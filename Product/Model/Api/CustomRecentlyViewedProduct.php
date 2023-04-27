<?php

namespace MIT\Product\Model\Api;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Reports\Model\Event;
use MIT\Product\Api\CustomRecentlyViewedProductInterface;

class CustomRecentlyViewedProduct implements CustomRecentlyViewedProductInterface
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
     * @var \Magento\Reports\Model\Product\Index\ViewedFactory
     */
    protected $_productIndxFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

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
        \Magento\Reports\Model\Product\Index\ViewedFactory $productIndxFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Framework\DataObjectFactory $objectFactory,
	\MIT\Product\Api\CustomProductInterface $customProduct,
        \Magento\Reports\Model\ReportStatus $reportStatus
    ) {
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->_productIndxFactory = $productIndxFactory;
        $this->_storeManager = $storeManager;
        $this->objectFactory = $objectFactory;
	$this->reportStatus = $reportStatus;
	$this->customProduct = $customProduct;
    }

    /**
     * retrieve recently viewed product by customer
     * @param string $customerId
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function getRecentlyViewedProductList($customerId)
    {
        if (!$this->reportStatus->isReportEnabled(Event::EVENT_PRODUCT_VIEW)) {
            return;
        }
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        $collection->getSelect()->join('report_viewed_product_index as viewed_item', 'e.entity_id = viewed_item.product_id', 'index_id');    // Add this join statement

        $recentlyViewedProductIds = [];
        $collection->getSelect()
            ->where('viewed_item.customer_id = ?', $customerId)
            ->order('viewed_item.added_at DESC')
            ->limit(5);

        foreach ($collection as $product) {
            $recentlyViewedProductIds[] = $product->getId();
        }

        $filteredId = $this->_filterBuilder
            ->setConditionType('in')
            ->setField('entity_id')
            ->setValue($recentlyViewedProductIds)
            ->create();

        $filterGroupList = [];
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();

        $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList);

        $products = $this->customProduct->getList($this->_searchCriteriaBuilder->create());

        return $products;
    }

    /**
     * set customer recently viewed product
     * @param $productId
     * @param $customerId
     * @return \Magento\Framework\DataObject
     */
    public function setRecentlyViewedProduct($productId, $customerId)
    {
        if (!$this->reportStatus->isReportEnabled(Event::EVENT_PRODUCT_VIEW)) {
            return;
        }
        $viewData['product_id'] = $productId;
        $viewData['store_id']   = $this->_storeManager->getStore()->getId();
        $viewData['customer_id'] = $customerId;

        $this->_productIndxFactory->create()->setData($viewData)->save()->calculate();
        $obj = $this->objectFactory->create();
        $obj->setItem($viewData);
        return $obj->getData();
    }
}
