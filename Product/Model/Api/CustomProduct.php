<?php

namespace MIT\Product\Model\Api;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Entity\Attribute\Exception as AttributeException;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\TemporaryState\CouldNotSaveException as TemporaryCouldNotSaveException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\Store;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use MIT\Discount\Helper\DiscountHelper;
use MIT\Product\Api\CustomProductInterface;
use MIT\Product\Api\Data\CustomProductSearchResultsInterfaceFactory;
use MIT\Product\Helper\BundleProductHelper;
use MIT\Product\Helper\ProductHelper;
use MIT\Product\Helper\ShoppingOptionsFilter;
use MIT\Product\Model\CustomProductFactory;
use MIT\Product\Model\DiscountLabelFactory;

class CustomProduct extends ProductRepository implements CustomProductInterface
{
    /**
     * @var CustomProductFactory
     */
    protected $customProductFactory;

    /**
     * @var \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Product[]
     */
    protected $instances = [];

    /**
     * @var Product[]
     */
    protected $instancesById = [];

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceModel;

    /**
     * @var Product\Initialization\Helper\ProductLinks
     */
    protected $linkInitializer;

    /**
     * @var Product\LinkTypeProvider
     */
    protected $linkTypeProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $metadataService;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @deprecated 103.0.2
     *
     * @var ImageContentInterfaceFactory
     */
    protected $contentFactory;

    /**
     * @deprecated 103.0.2
     *
     * @var ImageProcessorInterface
     */
    protected $imageProcessor;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @deprecated 103.0.2
     *
     * @var \Magento\Catalog\Model\Product\Gallery\Processor
     */
    protected $mediaGalleryProcessor;

    /**
     * @var MediaGalleryProcessor
     */
    private $mediaProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var int
     */
    private $cacheLimit = 0;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var ReadExtensions
     */
    private $readExtensions;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $linkManagement;

    /**
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @var \MIT\Product\Api\Data\customProductSearchResultsInterface
     */
    protected $customProductSearchResultsInterface;

    /**
     * @var ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurable;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    private $wishListFactory;

    /**
     * @var \Magento\Integration\Model\Oauth\TokenFactory
     */
    private $tokenFactory;

    /**
     * @var \Magento\Integration\Helper\Oauth\Data
     */
    private $oauthHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var BundleProductHelper
     */
    private $bundleProductHelper;

    /**
     * @var \MIT\Product\Model\GroupProductFactory
     */
    private $groupProductFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistryInterface;

    /**
     * @var ShoppingOptionsFilter
     */
    private $shoppingOptionsFilter;

    /**
     * @var DiscountHelper
     */
    private $discountHelper;

    /**
     * @var DiscountLabelFactory
     */
    private $discountLabelFactory;

    /**
     * @var CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $_productRepository;

    public function __construct(
        ProductFactory $productFactory,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linkInitializer,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Catalog\Model\Product\Option\Converter $optionConverter,
        \Magento\Framework\Filesystem $fileSystem,
        ImageContentValidatorInterface $contentValidator,
        ImageContentInterfaceFactory $contentFactory,
        MimeTypeExtensionMap $mimeTypeExtensionMap,
        ImageProcessorInterface $imageProcessor,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CustomProductFactory $customProductFactory,
        CustomProductSearchResultsInterfaceFactory $customProductSearchResultsInterface,
        ReviewFactory $reviewFactory,
        FilterGroupBuilder $filterGroupBuilder,
        ProductHelper $productHelper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory,
        \Magento\Integration\Helper\Oauth\Data $oauthHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        CollectionFactory $itemCollectionFactory,
        ProductRepositoryInterface $_productRepository,
        BundleProductHelper $bundleProductHelper,
        \MIT\Product\Model\GroupProductFactory $groupProductFactory,
        CategoryFactory $categoryFactory,
        SortOrderBuilder $sortOrderBuilder,
        CatalogConfig $catalogConfig,
        StockRegistryInterface $stockRegistryInterface,
        ShoppingOptionsFilter $shoppingOptionsFilter,
        DiscountHelper $discountHelper,
        DiscountLabelFactory $discountLabelFactory,
        CollectionProcessorInterface $collectionProcessor = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        $cacheLimit = 1000,
        ReadExtensions $readExtensions = null,
        CategoryLinkManagementInterface $linkManagement = null,
        ?ScopeOverriddenValue $scopeOverriddenValue = null
    ) {
        parent::__construct(
            $productFactory,
            $initializationHelper,
            $searchResultsFactory,
            $collectionFactory,
            $searchCriteriaBuilder,
            $attributeRepository,
            $resourceModel,
            $linkInitializer,
            $linkTypeProvider,
            $storeManager,
            $filterBuilder,
            $metadataServiceInterface,
            $extensibleDataObjectConverter,
            $optionConverter,
            $fileSystem,
            $contentValidator,
            $contentFactory,
            $mimeTypeExtensionMap,
            $imageProcessor,
            $extensionAttributesJoinProcessor,
            $collectionProcessor,
            $serializer,
            $cacheLimit,
            $readExtensions,
            $linkManagement,
            $scopeOverriddenValue
        );
        $this->productHelper = $productHelper;
        $this->configurable = $configurable;
        $this->wishListFactory = $wishlistFactory;
        $this->tokenFactory = $tokenFactory;
        $this->oauthHelper = $oauthHelper;
        $this->date = $date;
        $this->dateTime = $dateTime;
        $this->categoryFactory = $categoryFactory;
        $this->shoppingOptionsFilter = $shoppingOptionsFilter;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->_productRepository = $_productRepository;

        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->customProductFactory = $customProductFactory;
        $this->customProductSearchResultsInterface = $customProductSearchResultsInterface;
        $this->_reviewFactory = $reviewFactory;
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->initializationHelper = $initializationHelper;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->linkInitializer = $linkInitializer;
        $this->linkTypeProvider = $linkTypeProvider;
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->filterBuilder = $filterBuilder;
        $this->metadataService = $metadataServiceInterface;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->fileSystem = $fileSystem;
        $this->contentFactory = $contentFactory;
        $this->imageProcessor = $imageProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->cacheLimit = (int) $cacheLimit;
        $this->readExtensions = $readExtensions ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ReadExtensions::class);
        $this->linkManagement = $linkManagement ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(CategoryLinkManagementInterface::class);
        $this->scopeOverriddenValue = $scopeOverriddenValue ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ScopeOverriddenValue::class);
        $this->bundleProductHelper = $bundleProductHelper;
        $this->groupProductFactory = $groupProductFactory;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->catalogConfig = $catalogConfig;
        $this->stockRegistryInterface = $stockRegistryInterface;
        $this->discountHelper = $discountHelper;
        $this->discountLabelFactory = $discountLabelFactory;
    }

    /**
     * get product by id including review/rating
     * @param int $id
     * @return \MIT\Product\Api\Data\CustomProductManagementInterface
     */
    public function getProductDetailBySku($id)
    {
        $factory = $this->customProductFactory->create();
        $productStock = $this->stockRegistryInterface->getStockItem($id);
        $productQty = $productStock->getQty();
        $factory->setStockQty($productQty);
        $product = $factory->load($id);
        if (($product->getSku())) {
            $currentStore = $this->storeManager->getStore();
            $baseUrl = $currentStore->getBaseUrl();
            $product->getCustomRegularPrice();
            if ($product->getCustomAttributes()) {
                foreach ($product->getCustomAttributes() as $customAttribute) {
                    if (in_array($customAttribute->getAttributeCode(), ['image', 'thumbnail', 'small_image', 'swatch_image'])) {
                        $customAttribute->setValue($baseUrl . 'media/catalog/product' . $customAttribute->getValue());
                    }
                }
            }
            if ($product->getMediaGalleryEntries()) {
                $galleryList = [];
                foreach ($product->getMediaGalleryEntries() as $gallery) {
                    $data = $gallery;
                    if ($data->getFile()) {
                        $data->setFile($baseUrl . 'media/catalog/product' . $data->getFile());
                    }
                    $galleryList[] = $data;
                }
                $product->setMediaGalleryEntries([]);
                $product->setMediaGalleryEntries($galleryList);
            }

            if ($product->getCustomAttributes()) {
                foreach ($product->getCustomAttributes() as $customAttribute) {
                    if ($customAttribute->getAttributeCode() == 'description') {
                        $customAttribute->setValue($this->removeReviewWidget($customAttribute->getValue()));
                    }
                }
            }

            if ($product->getExtensionAttributes()->getConfigurableProductLinks()) {
                $relatedIds = implode(',', $product->getExtensionAttributes()->getConfigurableProductLinks());
                if ($relatedIds) {
                    $filteredId = $this->filterBuilder
                        ->setConditionType('in')
                        ->setField('entity_id')
                        ->setValue($relatedIds)
                        ->create();

                    $filterGroupList = [];
                    $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();

                    $this->searchCriteriaBuilder->setFilterGroups($filterGroupList);
                    $result = $this->getCustomProductDetailsList($this->searchCriteriaBuilder->create());
                    $product->setConfigurableProductList($result->getItems());
                    //get child product qty from configurable product
                    $childProductArr = [];
                    if ($result->getItems() > 0) {
                        foreach ($result->getItems() as $childProduct) {
                            $stock = $this->stockRegistryInterface->getStockItem($childProduct->getId());
                            // if ($stock->getQty() > 0 && $stock->getIsInStock()) {
                            $childProduct->setStockQty($stock->getQty());
                            $this->retrieveAndSetDiscountLabel($childProduct);
                            $childProductArr[] = $childProduct;
                            // }
                        }
                    }
                    $product->setConfigurableProductList($childProductArr);
                }
            }

            $customerId = $this->getCustomerIdByToken();
            $wishListProduct = [];
            if ($customerId > 0) {
                $wishListProduct = $this->getWishListProductIdsByCustomer($customerId);
            }
            $this->getProductUrl($product, $baseUrl);
            $product->setIsWishList(false);
            foreach ($wishListProduct as $item) {
                $wishlistItemId = $item['wishlist_item_id'];
                $wishlistQty = $item['wishlist_qty'];
                $selectedProductId = $item['selected_product_id'];
                if ($item['product_id'] == $id) {
                    $product->setIsWishList(true);
                    $product->setWishlistItemId($wishlistItemId);
                    $product->setWishlistQty($wishlistQty);
                    $product->setSelectedProductId($selectedProductId);
                }
            }
            if ($product->getTypeId() == 'bundle') {
                $this->bundleProductHelper->generateBundleProductItemNameAndPrice($product->getExtensionAttributes()->getBundleProductOptions());
            }

            if ($product->getTypeId() == 'grouped') {
                $_children = $product->getTypeInstance(true)->getAssociatedProducts($product);

                $simpleproduct = array();
                foreach ($_children as $child) {
                    if ($child->getId() != $product->getId()) {
                        //to get stock quantity
                        $stock = $this->stockRegistryInterface->getStockItem($child->getId());

                        $groupProductFactory = $this->groupProductFactory->create();
                        $groupProductFactory->setId($child->getId());
                        $groupProductFactory->setName($child->getName());
                        $groupProductFactory->setSku($child->getSKU());
                        $groupProductFactory->setRegularPrice($child->getPrice() + 0);
                        //using buldleProductHelper to get real price
                        $groupProductFactory->setRealPrice($this->bundleProductHelper->getBundleProductItemPrice($child));

                        $groupProductFactory->setDefaultQuantity($child->getQty());
                        $groupProductFactory->setQuantity($stock->getQty());
                        $simpleproduct[] = $groupProductFactory;
                    }

                }
                $product->setGroupProductList($simpleproduct);
            }
            $this->retrieveAndSetDiscountLabel($product);
            return $product;
        } else {
            return;
        }
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $customSort = false, $sortQuery = '')
    {
        $filteredId = $this->filterBuilder
            ->setConditionType('eq')
            ->setField('is_in_stock')
            ->setValue(1)
            ->create();

        $filteredStock = $this->filterBuilder
            ->setConditionType('eq')
            ->setField('stock_status')
            ->setValue(1)
            ->create();

        $filterStatus = $this->filterBuilder
            ->setConditionType('eq')
            ->setField('status')
            ->setValue(1)
            ->create();

        $filterGroupList = [];
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredStock)->create();
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filterStatus)->create();
        $search = $searchCriteria->getFilterGroups();
        array_push($search, $filterGroupList[0], $filterGroupList[1], $filterGroupList[2]);
        $searchCriteria->setFilterGroups($search);

        $productList = $this->getCustomProductDetailsList($searchCriteria, $customSort, $sortQuery);

        return $productList;
    }

    /**
     * get product details list
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function getCustomProductDetailsList(SearchCriteriaInterface $searchCriteria, $customSort = false, $sortQuery = '')
    {
        $layerNavigationResultArr = $this->shoppingOptionsFilter->getModifiedSearchCriteria($searchCriteria);
        $searchCriteria = $layerNavigationResultArr['search'];

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getConditionType() == 'eq' && $filter->getField() == 'category_id') {
                    $category = $this->categoryFactory->create()->load($filter->getValue());
                    $categoryIdList = $category->getAllChildren(false);
                    $filter->setConditionType('in');
                    $filter->setValue($categoryIdList);
                }
            }
        }

        if (null == ($searchCriteria->getSortOrders())) {
            $sortOrderList = [];

            $key = $this->catalogConfig->getProductListDefaultSortBy($this->storeManager->getStore()->getId());
            if ($key == 'price_asc') {
                $sortOrderList[] = $this->sortOrderBuilder->setField('price')->setDirection('ASC')->create();
            } else if ($key == 'price_desc') {
                $sortOrderList[] = $this->sortOrderBuilder->setField('price')->setDirection('DESC')->create();
            } else {
                $sortOrderList[] = $this->sortOrderBuilder->setField($key)->setDirection('DESC')->create();
            }
            $searchCriteria->setSortOrders($sortOrderList);
        }

        if ($customSort) {
            $searchCriteria->setSortOrders([]);
        }

        $sorterArr = $searchCriteria->getSortOrders();
        $sorterArr[] = $this->sortOrderBuilder->setField('position')->setDirection('ASC')->create();
        $searchCriteria->setSortOrders($sorterArr);

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        $collection->addAttributeToSelect('*');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        $collection->joinField('is_in_stock', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id');
        $collection->joinField('stock_status', 'cataloginventory_stock_status', 'stock_status', 'product_id=entity_id');
        $this->joinPositionField($collection, $searchCriteria);

        $this->collectionProcessor->process($searchCriteria, $collection);

        if ($customSort) {
            $collection->getSelect()->order($sortQuery);
        }

        $collection->load();

        $collection->addCategoryIds();
        $collection->addMinimalPrice();
        $collection->addFinalPrice();
        $this->addExtensionAttributes($collection);
        $searchResult = $this->customProductSearchResultsInterface->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $currentStore = $this->storeManager->getStore();
        $baseUrl = $currentStore->getBaseUrl();

        $customerId = $this->getCustomerIdByToken();
        $wishListProduct = [];
        if ($customerId > 0) {
            $wishListProduct = $this->getWishListProductIdsByCustomer($customerId);
        }

        foreach ($collection->getItems() as $item) {
            $this->retrieveAndSetDiscountLabel($item);
            $stock = $this->stockRegistryInterface->getStockItem($item->getId());
            $item->setStockQty($stock->getQty());
            $this->getCustomRegularPrice($item);
            if ($item->getCustomAttributes()) {
                foreach ($item->getCustomAttributes() as $customAttribute) {
                    if (in_array($customAttribute->getAttributeCode(), ['image', 'thumbnail', 'small_image', 'swatch_image'])) {
                        $customAttribute->setValue($baseUrl . 'media/catalog/product' . $customAttribute->getValue());
                    }
                }
            }
            if ($item->getMediaGalleryEntries()) {
                $galleryList = [];
                foreach ($item->getMediaGalleryEntries() as $gallery) {
                    $data = $gallery;
                    if ($data->getFile()) {
                        $data->setFile($baseUrl . 'media/catalog/product' . $data->getFile());
                    }
                    $galleryList[] = $data;
                }
                $item->setMediaGalleryEntries([]);
                $item->setMediaGalleryEntries($galleryList);
            }

            $this->cacheProduct(
                $this->getCacheKey(
                    [
                        false,
                        $item->getStoreId(),
                    ]
                ),
                $item
            );
            $this->getProductUrl($item, $baseUrl);
            $item->setIsWishList(false);
            foreach ($wishListProduct as $product) {
                $wishlistItemId = $product['wishlist_item_id'];
                $wishlistQty = $product['wishlist_qty'];
                $selectedProductId = $product['selected_product_id'];
                if ($product['product_id'] == $item->getId()) {
                    $item->setIsWishList(true);
                    $item->setWishlistItemId($wishlistItemId);
                    $item->setWishlistQty($wishlistQty);
                    $item->setSelectedProductId($selectedProductId);
                }
            }
        }
        $searchResult->setItems($collection->getItems());
        if ($layerNavigationResultArr['total'] > 0 && $layerNavigationResultArr['curPage'] > 0) {
            $searchResult->setTotalCount($layerNavigationResultArr['total']);
            $searchResult->getSearchCriteria()->setCurrentPage($layerNavigationResultArr['curPage']);
        } else {
            $searchResult->setTotalCount($collection->getSize());
        }
        $searchResult->getSearchCriteria()->setFilterGroups([]);
        return $searchResult;
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }

    /**
     * Add product to internal cache and truncate cache if it has more than cacheLimit elements.
     *
     * @param string $cacheKey
     * @param ProductInterface $product
     * @return void
     */
    private function cacheProduct($cacheKey, ProductInterface $product)
    {
        $this->instancesById[$product->getId()][$cacheKey] = $product;
        $this->saveProductInLocalCache($product, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset, null, true);
        }
    }

    /**
     * Saves product in the local cache by sku.
     *
     * @param Product $product
     * @param string $cacheKey
     * @return void
     */
    private function saveProductInLocalCache(Product $product, string $cacheKey): void
    {
        $preparedSku = $this->prepareSku($product->getSku());
        $this->instances[$preparedSku][$cacheKey] = $product;
    }

    /**
     * Converts SKU to lower case and trims.
     *
     * @param string $sku
     * @return string
     */
    private function prepareSku(string $sku): string
    {
        return mb_strtolower(trim($sku));
    }

    /**
     * Add extension attributes to loaded items.
     *
     * @param Collection $collection
     * @return Collection
     */
    private function addExtensionAttributes(Collection $collection): Collection
    {
        foreach ($collection->getItems() as $item) {
            $this->readExtensions->execute($item);
        }
        return $collection;
    }

    /**
     * Join category position field to make sorting by position possible.
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @return void
     */
    private function joinPositionField(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria
    ): void {
        $categoryIds = [[]];
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'category_id') {
                    $filterValue = $filter->getValue();
                    $categoryIds[] = is_array($filterValue) ? $filterValue : explode(',', $filterValue);
                }
            }
        }
        $categoryIds = array_unique(array_merge(...$categoryIds));
        if (count($categoryIds) === 1) {
            $collection->joinField(
                'position',
                'catalog_category_product',
                'position',
                'product_id=entity_id',
                ['category_id' => current($categoryIds)],
                'left'
            );
        }
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                // phpstan:ignore "Class Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor not found."
                \Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor::class
            );
        }
        return $this->collectionProcessor;
    }

    /**
     * get product rating summary
     * @param $product
     * @return $ratingSummary
     */
    public function getRatingSummary($product)
    {
        $this->_reviewFactory->create()->getEntitySummary($product, $this->storeManager->getStore()->getId());
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();

        return $ratingSummary;
    }

    /**
     * set product price and discount percentage
     * @param $product
     */
    public function getCustomRegularPrice($product)
    {
        $product->setWeightUnit($this->productHelper->getWeightUnit());
        $regularPrice = 0;
        $specialPrice = 0;
        if ($product->getTypeId() == 'configurable') {
            $basePrice = $product->getPriceInfo()->getPrice('regular_price');

            $regularPrice = $basePrice->getMinRegularAmount()->getValue();
            $specialPrice = $product->getFinalPrice();
        } else if ($product->getTypeId() == 'bundle') {
            $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
            $specialPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
            $product->setMaxRegularPrice($product->getPriceInfo()->getPrice('regular_price')->getMaximalPrice()->getValue());
            $product->setMaxRealPrice(round($product->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue()));
            $product->setMinPrice($specialPrice);
            $product->setMaxPrice($product->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue());
        } else if ($product->getTypeId() == 'grouped') {
            $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);
            foreach ($usedProds as $child) {
                if ($child->getId() != $product->getId()) {
                    $tmpFinal = $child->getFinalPrice();
                    if ($specialPrice > 0) {
                        if ($specialPrice > $tmpFinal) {
                            $specialPrice = $tmpFinal;
                        }
                    } else {
                        $specialPrice = $tmpFinal;
                    }
                }
            }
            $regularPrice = $specialPrice;
        } else {
            $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();
            $specialPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();
        }
        $product->setRegularPrice($regularPrice);
        $product->setRealPrice(round($specialPrice));
        $save_percent = 0;
        if ($specialPrice > 0 && $regularPrice > 0) {
            $save_percent = 100 - round(($specialPrice / $regularPrice) * 100);
        }
        $product->setRating($this->getRatingSummary($product));
        $product->setDiscountPercentage(sprintf('%s%% off', $save_percent));
    }

    /**
     * This method is used to remove review widget for mobile api
     * @param $descrption product description
     * @return updated description
     */
    private function removeReviewWidget($description)
    {
        $firstIdx = strpos($description, '{{');
        if ($firstIdx) {
            $lastIdx = strpos($description, '}}');
            return substr($description, 0, $firstIdx) . substr($description, $lastIdx + 2);
        }
        return $description;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(ProductInterface $product, $saveOptions = false)
    {

        $data = new ProductInterface();
        $data->setSku('');
        $data->setName('');
        $data->setPrice(0.0);

        $data->setVisibility(4);
        $data->setStatus(1);

        $assignToCategories = false;
        $tierPrices = $product->getData('tier_price');
        $productDataToChange = $product->getData();

        try {
            $existingProduct = $product->getId() ?
            $this->getById($product->getId()) :
            $this->get($product->getSku());

            $product->setData(
                $this->resourceModel->getLinkField(),
                $existingProduct->getData($this->resourceModel->getLinkField())
            );
            if (!$product->hasData(Product::STATUS)) {
                $product->setStatus($existingProduct->getStatus());
            }

            /** @var ProductExtension $extensionAttributes */
            $extensionAttributes = $product->getExtensionAttributes();
            if (empty($extensionAttributes->__toArray())) {
                $product->setExtensionAttributes($existingProduct->getExtensionAttributes());
                $assignToCategories = true;
            }
        } catch (NoSuchEntityException $e) {
            $existingProduct = null;
        }

        $productDataArray = $this->extensibleDataObjectConverter
            ->toNestedArray($product, [], ProductInterface::class);
        $productDataArray = array_replace($productDataArray, $product->getData());
        $ignoreLinksFlag = $product->getData('ignore_links_flag');
        $productLinks = null;
        if (!$ignoreLinksFlag && $ignoreLinksFlag !== null) {
            $productLinks = $product->getProductLinks();
        }
        if (!isset($productDataArray['store_id'])) {
            $productDataArray['store_id'] = (int) $this->storeManager->getStore()->getId();
        }
        $product = $this->initializeProductData($productDataArray, empty($existingProduct));
        $this->processLinks($product, $productLinks);
        if (isset($productDataArray['media_gallery'])) {
            $this->processMediaGallery($product, $productDataArray['media_gallery']['images']);
        }

        if (!$product->getOptionsReadonly()) {
            $product->setCanSaveCustomOptions(true);
        }

        $validationResult = $this->resourceModel->validate($product);
        if (true !== $validationResult) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Invalid product data: %1', implode(',', $validationResult))
            );
        }

        if ($tierPrices !== null) {
            $product->setData('tier_price', $tierPrices);
        }

        try {
            $stores = $product->getStoreIds();
            $websites = $product->getWebsiteIds();
        } catch (NoSuchEntityException $exception) {
            $stores = null;
            $websites = null;
        }

        if (!empty($existingProduct) && is_array($stores) && is_array($websites)) {
            $hasDataChanged = false;
            $productAttributes = $product->getAttributes();
            if (
                $productAttributes !== null
                && $product->getStoreId() !== Store::DEFAULT_STORE_ID
                && (count($stores) > 1 || count($websites) === 1)
            ) {
                foreach ($productAttributes as $attribute) {
                    $attributeCode = $attribute->getAttributeCode();
                    $value = $product->getData($attributeCode);
                    if (
                        $existingProduct->getData($attributeCode) === $value
                        && $attribute->getScope() !== EavAttributeInterface::SCOPE_GLOBAL_TEXT
                        && !is_array($value)
                        && $attribute->getData('frontend_input') !== 'media_image'
                        && !$attribute->isStatic()
                        && !array_key_exists($attributeCode, $productDataToChange)
                        && $value !== null
                        && !$this->scopeOverriddenValue->containsValue(
                            ProductInterface::class,
                            $product,
                            $attributeCode,
                            $product->getStoreId()
                        )
                    ) {
                        $product->setData($attributeCode);
                        $hasDataChanged = true;
                    }
                }
                if ($hasDataChanged) {
                    $product->setData('_edit_mode', true);
                }
            }
        }

        $this->saveProduct($product);
        if ($assignToCategories === true && $product->getCategoryIds()) {
            $this->linkManagement->assignProductToCategories(
                $product->getSku(),
                $product->getCategoryIds()
            );
        }
        $this->removeProductFromLocalCacheBySku($product->getSku());
        $this->removeProductFromLocalCacheById($product->getId());

        return $this->get($product->getSku(), false, $product->getStoreId());
    }

    /**
     * Process product links, creating new links, updating and deleting existing links
     *
     * @param ProductInterface $product
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface[] $newLinks
     * @return $this
     * @throws NoSuchEntityException
     */
    private function processLinks(ProductInterface $product, $newLinks)
    {
        if ($newLinks === null) {
            // If product links were not specified, don't do anything
            return $this;
        }

        // Clear all existing product links and then set the ones we want
        $linkTypes = $this->linkTypeProvider->getLinkTypes();
        foreach (array_keys($linkTypes) as $typeName) {
            $this->linkInitializer->initializeLinks($product, [$typeName => []]);
        }

        // Set each linktype info
        if (!empty($newLinks)) {
            $productLinks = [];
            foreach ($newLinks as $link) {
                $productLinks[$link->getLinkType()][] = $link;
            }

            foreach ($productLinks as $type => $linksByType) {
                $assignedSkuList = [];
                /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $link */
                foreach ($linksByType as $link) {
                    $assignedSkuList[] = $link->getLinkedProductSku();
                }
                $linkedProductIds = $this->resourceModel->getProductsIdsBySkus($assignedSkuList);

                $linksToInitialize = [];
                foreach ($linksByType as $link) {
                    $linkDataArray = $this->extensibleDataObjectConverter
                        ->toNestedArray($link, [], \Magento\Catalog\Api\Data\ProductLinkInterface::class);
                    $linkedSku = $link->getLinkedProductSku();
                    if (!isset($linkedProductIds[$linkedSku])) {
                        throw new NoSuchEntityException(
                            __('The Product with the "%1" SKU doesn\'t exist.', $linkedSku)
                        );
                    }
                    $linkDataArray['product_id'] = $linkedProductIds[$linkedSku];
                    $linksToInitialize[$linkedProductIds[$linkedSku]] = $linkDataArray;
                }

                $this->linkInitializer->initializeLinks($product, [$type => $linksToInitialize]);
            }
        }

        $product->setProductLinks($newLinks);
        return $this;
    }

    /**
     * Save product resource model.
     *
     * @param ProductInterface|Product $product
     * @throws TemporaryCouldNotSaveException
     * @throws InputException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function saveProduct($product): void
    {
        try {
            $this->removeProductFromLocalCacheBySku($product->getSku());
            $this->removeProductFromLocalCacheById($product->getId());
            $this->resourceModel->save($product);
        } catch (ConnectionException $exception) {
            throw new TemporaryCouldNotSaveException(
                __('Database connection error'),
                $exception,
                $exception->getCode()
            );
        } catch (DeadlockException $exception) {
            throw new TemporaryCouldNotSaveException(
                __('Database deadlock found when trying to get lock'),
                $exception,
                $exception->getCode()
            );
        } catch (LockWaitException $exception) {
            throw new TemporaryCouldNotSaveException(
                __('Database lock wait timeout exceeded'),
                $exception,
                $exception->getCode()
            );
        } catch (AttributeException $exception) {
            throw InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $product->getData($exception->getAttributeCode()),
                $exception
            );
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('The product was unable to be saved. Please try again.'),
                $e
            );
        }
    }

    /**
     * Removes product in the local cache by sku.
     *
     * @param string $sku
     * @return void
     */
    private function removeProductFromLocalCacheBySku(string $sku): void
    {
        $preparedSku = $this->prepareSku($sku);
        unset($this->instances[$preparedSku]);
    }

    /**
     * Removes product in the local cache by id.
     *
     * @param string|null $id
     * @return void
     */
    private function removeProductFromLocalCacheById(?string $id): void
    {
        unset($this->instancesById[$id]);
    }

    /**
     * get product url
     * @param \MIT\Product\Model\CustomProduct $product
     * @param string $baseUrl
     */
    private function getProductUrl($product, $baseUrl)
    {
        if ($product->getVisibility() == 4) {
            $url = $this->setCustomProductShareUrl($product);
            if ($url) {
                $product->setProductShareUrl($baseUrl . $url);
            }
        } else {
            $parentIdsConfig = $this->configurable->getParentIdsByChild($product->getId());
            if (is_array($parentIdsConfig) && count($parentIdsConfig) > 0) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $parentProduct = $objectManager->get('Magento\Catalog\Model\Product')->load($parentIdsConfig[0]);
                $url = $this->setCustomProductShareUrl($parentProduct);
                if ($url) {
                    $product->setProductShareUrl($baseUrl . $url);
                }
            }
        }
    }

    /**
     * set product share url
     * @param \MIT\Product\Model\CustomProduct $product
     * @return string
     */
    private function setCustomProductShareUrl($product)
    {
        if ($product->getCustomAttributes()) {
            foreach ($product->getCustomAttributes() as $customAttribute) {
                if ($customAttribute->getAttributeCode() === 'url_key') {
                    return $customAttribute->getValue() . '.html';
                }
            }
        }
        return;
    }

    /**
     * set wishlist or not
     * @param int $customerId
     * @return array
     */
    private function getWishListProductIdsByCustomer($customerId)
    {
        $product = [];
        $storeId = $this->storeManager->getStore()->getId();

        $collection = $this->itemCollectionFactory->create();
        $collection->addFieldToSelect('qty');
        $collection->getSelect()->joinInner('wishlist', 'wishlist.wishlist_id = main_table.wishlist_id', 'customer_id');
        $collection->getSelect()->joinInner('wishlist_item_option', 'wishlist_item_option.wishlist_item_id = main_table.wishlist_item_id', ['value', 'code', 'product_id', 'wishlist_item_id']);
        $collection->getSelect()->where('wishlist.customer_id = ? ', $customerId)
            ->where('wishlist_item_option.code = ? ', 'info_buyRequest')
            ->where('main_table.store_id = ? ', $storeId);

        foreach ($collection as $item) {
            $value = json_decode($item['value'], true);
            $productId = $item['product_id'];
            $wishlistItemId = $item['wishlist_item_id'];
            $qty = $item['qty'];

            if (array_key_exists('super_attribute', $value)) {
                $attribute = $value['super_attribute'];

                $_configProduct = $this->_productRepository->getById($productId);
                $childProduct = $this->configurable->getProductByAttributes($attribute, $_configProduct);
                $productId = $childProduct->getId();
            }
            $data = [
                "wishlist_item_id" => $wishlistItemId,
                "product_id" => $item['product_id'],
                "wishlist_qty" => $qty,
                "selected_product_id" => $productId,
            ];
            $product[] = $data;
        }

        return $product;

    }

    /**
     * get customerId from token
     * @return int
     */
    private function getCustomerIdByToken()
    {
        if (isset($_SERVER['HTTP_TOKEN'])) {
            $result = $this->tokenFactory->create()->loadByToken($_SERVER['HTTP_TOKEN']);
            if (!$this->checkTokenExpired($result->getCreatedAt())) {
                return $result->getCustomerId();
            }
        }
        return 0;
    }

    /**
     * check token expired or not
     * @param string $tokenCreatedAt
     * @return bool
     */
    private function checkTokenExpired($tokenCreatedAt)
    {
        return $tokenCreatedAt <= $this->dateTime->formatDate($this->date->gmtTimestamp() - $this->oauthHelper->getCustomerTokenLifetime() * 60 * 60);
    }

    /**
     * retrieve and set product discount label
     * @param \MIT\Product\Api\Data\CustomProductManagementInterface $product
     * @return $this
     */
    private function retrieveAndSetDiscountLabel($product)
    {
        $result = $this->discountHelper->getLabelInfo($product->getId());
        if (count($result) > 0) {
            $discount = $this->discountLabelFactory->create();
            $discount->setLabel($result['label']);
            $discount->setImgPath($result['imgPath']);
            $discount->setMainClass($result['main_class']);
            $discount->setSubClass($result['sub_class']);
            $discount->setStyle($result['style']);
            $discount->setLabelStyle($result['label_styles']);
            $product->setDiscountLabel($discount);
        }
    }
}
