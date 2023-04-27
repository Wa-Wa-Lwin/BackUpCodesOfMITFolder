<?php

namespace MIT\Product\Model\Api;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Reports\Model\Event;
use MIT\Product\Api\CustomComparedProductInterface;

class CustomComparedProduct implements CustomComparedProductInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Magento\Reports\Model\Product\Index\ComparedFactory
     */
    protected $_productCompFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManagerInterface;

    /**
     * @var \Magento\Reports\Model\ReportStatus
     */
    private $reportStatus;


    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Reports\Model\Product\Index\ComparedFactory $productCompFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magento\Reports\Model\ReportStatus $reportStatus
    ) {
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->objectFactory = $objectFactory;
        $this->_productCompFactory = $productCompFactory;
        $this->objectManagerInterface = $objectManagerInterface;
        $this->reportStatus = $reportStatus;
    }

    /**
     * retrieve recently viewed product by customer
     * @param string $customerId
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function getComparedProductList($customerId)
    {
        if (!$this->reportStatus->isReportEnabled(Event::EVENT_PRODUCT_COMPARE)) {
            return;
        }
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        $collection->getSelect()->join('report_compared_product_index as viewed_item', 'e.entity_id = viewed_item.product_id', 'index_id');    // Add this join statement

        $comparedProductIds = [];
        $collection->getSelect()
            ->where('viewed_item.customer_id = ?', $customerId)
            ->order('viewed_item.added_at DESC')
            ->limit(5);

        foreach ($collection as $product) {
            $comparedProductIds[] = $product->getId();
        }

        $filteredId = $this->_filterBuilder
            ->setConditionType('in')
            ->setField('entity_id')
            ->setValue($comparedProductIds)
            ->create();

        $filterGroupList = [];
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();

        $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList);

        $products = $this->productRepository->getList($this->_searchCriteriaBuilder->create());

        return $products;
    }

    /**
     * set customer recently viewed product
     * @param string productId
     * @param string customerId
     * @return \Magento\Framework\DataObject
     */
    public function setComparedProduct($productId, $customerId)
    {
        if (!$this->reportStatus->isReportEnabled(Event::EVENT_PRODUCT_COMPARE)) {
            return;
        }
        $viewData = ['product_id' => $productId];
        $viewData['customer_id'] = $customerId;
        $viewData['store_id']   = $this->_storeManager->getStore()->getId();
        $this->_productCompFactory->create()->setData($viewData)->save()->calculate();

        $obj = $this->objectFactory->create();
        $obj->setItem($viewData);
        return $obj->getData();
    }

    /**
     * delete compare product by id
     * @param string $id
     * @param string $customerId
     * @return bool
     */
    public function deleteComparedProductById($id, $customerId)
    {
        if (!$this->reportStatus->isReportEnabled(Event::EVENT_PRODUCT_COMPARE)) {
            return false;
        }
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        $collection->getSelect()->join('report_compared_product_index as viewed_item', 'e.entity_id = viewed_item.product_id', 'index_id');    // Add this join statement

        $comparedIds = [];
        $collection->getSelect()
            ->where('viewed_item.customer_id = ?', $customerId)
            ->where('viewed_item.product_id = ? ', $id)
            ->order('viewed_item.added_at DESC')
            ->limit(5);

        foreach ($collection as $product) {
            $comparedIds[] = $product->getIndexId();
        }

        if (count($comparedIds) > 0) {
            $model = $this->objectManagerInterface->create(\Magento\Reports\Model\Product\Index\Compared::class);
            foreach ($comparedIds as $id) {
                $model->load($id);
                $model->delete();
            }
            return true;
        } else {
            return false;
        }
    }
}
