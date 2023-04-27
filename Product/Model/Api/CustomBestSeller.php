<?php

namespace MIT\Product\Model\Api;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use MIT\Product\Api\CustomBestSellerInterface;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use MIT\Product\Api\Data\CustomProductSearchResultsInterfaceFactory;

class CustomBestSeller implements CustomBestSellerInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var Grouped
     */
    protected $grouped;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var CustomProduct
     */
    protected $customProduct;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CustomProductSearchResultsInterfaceFactory
     */
    private $customProductSearchResultsInterface;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Visibility $catalogProductVisibility,
        ProductRepository $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        Grouped $grouped,
        Configurable $configurable,
        CustomProduct $customProduct,
        CollectionFactory $productCollectionFactory,
        CustomProductSearchResultsInterfaceFactory $customProductSearchResultsInterface
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->productRepository = $productRepository;
        $this->grouped = $grouped;
        $this->configurable = $configurable;
        $this->customProduct = $customProduct;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customProductSearchResultsInterface = $customProductSearchResultsInterface;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \MIT\Product\Api\Data\CustomProductSearchResultsInterface
     */
    public function getBestSellerProductList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {

        $collection = $this->productCollectionFactory->create();
        $collection->getSelect()->join('sales_bestsellers_aggregated_yearly as viewed_item', 'e.entity_id = viewed_item.product_id', 'qty_ordered');
        $collection->getSelect()->columns(['viewed_item.qty_ordered' => new \Zend_Db_Expr('SUM(viewed_item.qty_ordered)')]);
        $collection->addStoreFilter($this->_storeManager->getStore()->getId());
        $collection->getSelect()
            ->order('SUM(viewed_item.qty_ordered) desc')
            ->group('viewed_item.product_id');

        $productIds = $this->getProductParentIds($collection);
        if (empty($productIds)) {
            return null;
        }

        if (is_array($productIds) > 0) {
            $condition = '';
            foreach ($productIds as $id) {
                if (is_array($id)) {
                    foreach ($id as $parentId) {
                        $condition .= $parentId . ',';
                    }
                } else {
                    $condition .= $id . ',';
                }
            }
            if ($condition) {
                $condition = rtrim($condition, ",");
                $filteredId = $this->_filterBuilder
                    ->setConditionType('in')
                    ->setField('entity_id')
                    ->setValue($productIds)
                    ->create();

                $filterGroupList = [];
                $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();

                $searchCriteria->setFilterGroups($filterGroupList);

                return $this->customProduct->getList($searchCriteria, true, 'FIELD(e.entity_id,' . $condition . ')');
            }
        }

        return $this->customProductSearchResultsInterface->create();
    }

    /**
     * @param $collection best seller collection
     *
     * @return array $productIds
     */
    public function getProductParentIds($collection)
    {
        $productIds = [];

        foreach ($collection as $product) {
            if (isset($product->getData()['entity_id'])) {
                $productId = $product->getData()['entity_id'];
            } else {
                $productId = $product->getProductId();
            }

            $parentIdsGroup  = $this->grouped->getParentIdsByChild($productId);
            $parentIdsConfig = $this->configurable->getParentIdsByChild($productId);

            if (!empty($parentIdsGroup)) {
                $productIds[] = $parentIdsGroup[0];
            } elseif (!empty($parentIdsConfig)) {
                $productIds[] = $parentIdsConfig[0];
            } else {
                $productIds[] = $productId;
            }
        }
        return $productIds;
    }
}
