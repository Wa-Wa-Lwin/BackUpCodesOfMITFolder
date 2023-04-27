<?php

namespace MIT\Product\Model;

use DateTime;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\FilterProductCustomAttribute;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Review\Model\Rating\Option\VoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use MIT\Product\Api\Data\CustomProductManagementInterface;
use MIT\Product\Helper\ProductHelper;

class CustomProduct extends Product implements CustomProductManagementInterface
{

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var VoteFactory
     */
    protected $voteFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var float
     */
    protected $normalPrice;

    /**
     * @var float
     */
    protected $realPrice;

    /**
     * @var string
     */
    protected $discount;

    /**
     * @var string
     */
    protected $weightUnit;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    protected $configProductList;

    protected $minPrice;

    protected $maxPrice;

    protected $maxRegularPrice;

    protected $maxRealPrice;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataService
     * @param Product\Url $url
     * @param Product\Link $productLink
     * @param Product\Configuration\Item\OptionFactory $itemOptionFactory
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory
     * @param Product\OptionFactory $catalogProductOptionFactory
     * @param Product\Visibility $catalogProductVisibility
     * @param Product\Attribute\Source\Status $catalogProductStatus
     * @param Product\Media\Config $catalogProductMediaConfig
     * @param Product\Type $catalogProductType
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Catalog\Model\ResourceModel\Product $resource
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $resourceCollection
     * @param \Magento\Framework\Data\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Eav\Processor $productEavIndexerProcessor
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Product\Image\CacheFactory $imageCacheFactory
     * @param \Magento\Catalog\Model\ProductLink\CollectionProvider $entityCollectionProvider
     * @param Product\LinkTypeProvider $linkTypeProvider
     * @param \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory
     * @param \Magento\Catalog\Api\Data\ProductLinkExtensionFactory $productLinkExtensionFactory
     * @param EntryConverterPool $mediaGalleryEntryConverterPool
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param array $data
     * @param \Magento\Eav\Model\Config|null $config
     * @param FilterProductCustomAttribute|null $filterCustomAttribute
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataService,
        Product\Url $url,
        Product\Link $productLink,
        \Magento\Catalog\Model\Product\Configuration\Item\OptionFactory $itemOptionFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\Catalog\Model\Product\OptionFactory $catalogProductOptionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        Status $catalogProductStatus,
        \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
        Product\Type $catalogProductType,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Model\ResourceModel\Product $resource,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $resourceCollection,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Eav\Processor $productEavIndexerProcessor,
        CategoryRepositoryInterface $categoryRepository,
        Product\Image\CacheFactory $imageCacheFactory,
        \Magento\Catalog\Model\ProductLink\CollectionProvider $entityCollectionProvider,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory,
        \Magento\Catalog\Api\Data\ProductLinkExtensionFactory $productLinkExtensionFactory,
        EntryConverterPool $mediaGalleryEntryConverterPool,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory,
        ProductHelper $productHelper,
        float $normalPrice = 0.0,
        float $realPrice = 0.0,
        string $discount = '',
        string $weightUnit = '',
        float $minPrice = 0.0,
        float $maxPrice = 0.0,
        float $maxRegularPrice = 0.0,
        float $maxRealPrice = 0.0,
        array $data = [],
        array $productManagementInterface = [],
        \Magento\Eav\Model\Config $config = null,
        FilterProductCustomAttribute $filterCustomAttribute = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $metadataService,
            $url,
            $productLink,
            $itemOptionFactory,
            $stockItemFactory,
            $catalogProductOptionFactory,
            $catalogProductVisibility,
            $catalogProductStatus,
            $catalogProductMediaConfig,
            $catalogProductType,
            $moduleManager,
            $catalogProduct,
            $resource,
            $resourceCollection,
            $collectionFactory,
            $filesystem,
            $indexerRegistry,
            $productFlatIndexerProcessor,
            $productPriceIndexerProcessor,
            $productEavIndexerProcessor,
            $categoryRepository,
            $imageCacheFactory,
            $entityCollectionProvider,
            $linkTypeProvider,
            $productLinkFactory,
            $productLinkExtensionFactory,
            $mediaGalleryEntryConverterPool,
            $dataObjectHelper,
            $joinProcessor,
            $data = [],
            $config,
            $filterCustomAttribute
        );

        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->voteFactory = $voteFactory;
        $this->storeManager = $storeManager;
        $this->normalPrice = $normalPrice;
        $this->realPrice = $realPrice;
        $this->discount = $discount;
        $this->weightUnit = $weightUnit;
        $this->productHelper = $productHelper;
        $this->configProductList = $productManagementInterface;
        $this->minPrice = $minPrice;
        $this->maxPrice = $maxPrice;
        $this->maxRegularPrice = $maxRegularPrice;
        $this->maxRealPrice = $maxRealPrice;

        $this->metadataService = $metadataService;
        $this->_itemOptionFactory = $itemOptionFactory;
        $this->_stockItemFactory = $stockItemFactory;
        $this->optionFactory = $catalogProductOptionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogProductStatus = $catalogProductStatus;
        $this->_catalogProductMediaConfig = $catalogProductMediaConfig;
        $this->_catalogProductType = $catalogProductType;
        $this->moduleManager = $moduleManager;
        $this->_catalogProduct = $catalogProduct;
        $this->_collectionFactory = $collectionFactory;
        $this->_urlModel = $url;
        $this->_linkInstance = $productLink;
        $this->_filesystem = $filesystem;
        $this->indexerRegistry = $indexerRegistry;
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->_productEavIndexerProcessor = $productEavIndexerProcessor;
        $this->categoryRepository = $categoryRepository;
        $this->imageCacheFactory = $imageCacheFactory;
        $this->entityCollectionProvider = $entityCollectionProvider;
        $this->linkTypeProvider = $linkTypeProvider;
        $this->productLinkFactory = $productLinkFactory;
        $this->productLinkExtensionFactory = $productLinkExtensionFactory;
        $this->mediaGalleryEntryConverterPool = $mediaGalleryEntryConverterPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->joinProcessor = $joinProcessor;
        $this->eavConfig = $config ?? ObjectManager::getInstance()->get(\Magento\Eav\Model\Config::class);
        $this->filterCustomAttribute = $filterCustomAttribute ?? ObjectManager::getInstance()->get(FilterProductCustomAttribute::class);
    }

    /**
     * @inheritdoc
     */
    public function setRating(?array $ratingList = null)
    {
    }

    /**
     * @inheritdoc
     */
    public function getRating()
    {
        return $this->getProductRatingListByProductId($this->getId());
    }

    /**
     * generate rating of product
     */
    private function getProductRatingListByProductId($productId)
    {
        $collection = $this->reviewCollectionFactory->create();
        $collection
            ->addFieldToSelect('*')
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $productId)
            ->setDateOrder()
            ->addReviewsTotalCount()
            ->addRateVotes();
        $collection->getSelect();
        $product = [];
        foreach ($collection as $item) {
            $data = $item->getData();
            $votesCollection = $this->voteFactory->create()->getResourceCollection()->setReviewFilter(
                $data['review_id']
            )->setStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->addRatingInfo(
                $this->_storeManager->getStore()->getId()
            )->load();
            $data['rating_votes'] = $votesCollection->getData();
            $data['created_at'] = $this->formatDate($data['created_at']);
            $product[] = $data;
        }
        return $product;
    }

    /**
     * @inheritdoc
     */
    public function setRegularPrice(float $regularPrice)
    {
        $this->normalPrice = $regularPrice;
    }

    /**
     * @inheritdoc
     */
    public function getRegularPrice()
    {
        return $this->normalPrice;
    }

    /**
     * @inheritdoc
     */
    public function setRealPrice($price)
    {
        $this->realPrice = $price;
    }

    /**
     * @inheritdoc
     */
    public function getRealPrice()
    {
        return $this->realPrice;
    }

    /**
     * @inheritdoc
     */
    public function setMinPrice(float $bundleMinPrice)
    {
        $this->minPrice = $bundleMinPrice;
    }

    /**
     * @inheritdoc
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @inheritdoc
     */
    public function setMaxPrice(float $bundleMaxPrice)
    {
        $this->maxPrice = $bundleMaxPrice;
    }

    /**
     * @inheritdoc
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }

    /**
     * @inheritdoc
     */
    public function setMaxRegularPrice(float $regularPrice)
    {
        $this->maxRegularPrice = $regularPrice;
    }

    /**
     * @inheritdoc
     */
    public function getMaxRegularPrice()
    {
        return $this->maxRegularPrice;
    }

    /**
     * @inheritdoc
     */
    public function setMaxRealPrice(float $finalPrice)
    {
        $this->maxRealPrice = $finalPrice;
    }

    /**
     * @inheritdoc
     */
    public function getMaxRealPrice()
    {
        return $this->maxRealPrice;
    }

    /**
     * @inheritdoc
     */
    public function setDiscountPercentage(string $discount)
    {
        $this->discount = $discount;
    }

    /**
     * @inheritdoc
     */
    public function getDiscountPercentage()
    {
        return $this->discount;
    }

    /**
     * @inheritdoc
     */
    public function setWeightUnit(string $weightUnit)
    {
        $this->weightUnit = $weightUnit;
    }

    /**
     * @inheritdoc
     */
    public function getWeightUnit()
    {
        return $this->weightUnit;
    }

    /**
     * @inheritdoc
     */
    public function setConfigurableProductList(?array $configProductList = [])
    {
        $this->configProductList = $configProductList;
    }

    /**
     * @inheritdoc
     */
    public function getConfigurableProductList()
    {
        return $this->configProductList;
    }

    /**
     * @inheritdoc
     */
    public function setGuestCanReview($isReview)
    {
    }

    /**
     * @inheritdoc
     */
    public function getGuestCanReview()
    {
        return $this->productHelper->isReviewEnabledForGuest();
    }

    /**
     * @inheritdoc
     */
    public function setReviewEnabled($reviewEnabled)
    {
    }

    /**
     * @inheritdoc
     */
    public function getReviewEnabled()
    {
        return $this->productHelper->isReviewEnabled();
    }

    /**
     * set price and calculate discount percentage
     */
    public function getCustomRegularPrice()
    {
        $regularPrice = 0;
        $specialPrice = 0;
        if ($this->getTypeId() == 'configurable') {
            $basePrice = $this->getPriceInfo()->getPrice('regular_price');

            $regularPrice = $basePrice->getMinRegularAmount()->getValue();
            $specialPrice = $this->getFinalPrice();
        } else if ($this->getTypeId() == 'bundle') {
            $regularPrice = $this->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
            $specialPrice = $this->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
            $this->setMaxRegularPrice($this->getPriceInfo()->getPrice('regular_price')->getMaximalPrice()->getValue());
            $this->setMaxRealPrice(round($this->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue()));
            $this->setMinPrice($specialPrice);
            $this->setMaxPrice($this->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue());
        } else if ($this->getTypeId() == 'grouped') {
            $usedProds = $this->getTypeInstance(true)->getAssociatedProducts($this);
            foreach ($usedProds as $child) {
                if ($child->getId() != $this->getId()) {
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
            $regularPrice = $this->getPriceInfo()->getPrice('regular_price')->getValue();
            $specialPrice = $this->getPriceInfo()->getPrice('final_price')->getValue();
        }
        $this->setRegularPrice($regularPrice);
        $this->setRealPrice(round($specialPrice));
        $save_percent = 0;
        if ($specialPrice > 0 && $regularPrice > 0) {
            $save_percent = 100 - round(($specialPrice / $regularPrice) * 100);
        }
        $this->setDiscountPercentage(sprintf('%s%% off', $save_percent));
        $this->setWeightUnit($this->productHelper->getWeightUnit());
    }

    /**
     * format date to d/m/Y format
     * @param string $date
     */
    private function formatDate($date)
    {
        if ($this->verifyDate($date)) {
            $date = new DateTime($date);
            return $date->format('d/m/Y');
        }
        return $date;
    }

    /**
     * verify date
     * @param string $date
     * @param bool $strict
     * @return bool
     */
    private function verifyDate($date, $strict = true)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        if ($strict) {
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warning_count'])) {
                return false;
            }
        }
        return $dateTime !== false;
    }

    /**
     * @inheritdoc
     */
    public function setProductShareUrl($url)
    {
        return $this->setData(self::KEY_PRODUCT_URL, $url);
    }

    /**
     * @inheritdoc
     */
    public function getProductShareUrl()
    {
        return $this->getData(self::KEY_PRODUCT_URL);
    }

    /**
     * @inheritdoc
     */
    public function setIsWishList($isWishList)
    {
        return $this->setData(self::KEY_IS_WISH_LIST, $isWishList);
    }

    /**
     * @inheritdoc
     */
    public function getIsWishList()
    {
        return $this->getData(self::KEY_IS_WISH_LIST);
    }

    /**
     * @inheritdoc
     */
    public function setGroupProductList(array $groupProductList = null)
    {
        return $this->setData(self::KEY_GROUP_PRODUCT_LIST, $groupProductList);
    }

    /**
     * @inheritdoc
     */
    public function getGroupProductList()
    {
        return $this->getData(self::KEY_GROUP_PRODUCT_LIST);
    }

    /**
     * @inheritdoc
     */
    public function setAverageRating($averagRating)
    {
        return $this->setData(self::KEY_AVERAGE_RATING, $averagRating);
    }

    /**
     * @inheritdoc
     */
    public function getAverageRating()
    {
        return $this->getData(self::KEY_AVERAGE_RATING);
    }

    /**
     * @inheritdoc
     */
    public function setWishlistQty($qty)
    {
        return $this->setData(self::KEY_WISHLIST_QTY, $qty);
    }

    /**
     * @inheritdoc
     */
    public function getWishlistQty()
    {
        return $this->getData(self::KEY_WISHLIST_QTY) != null ? $this->getData(self::KEY_WISHLIST_QTY) : 0;
    }

    /**
     * @inheritdoc
     */
    public function setWishlistItemId($wishlistItemId)
    {
        return $this->setData(self::KEY_WISHLIST_ITEM_ID, $wishlistItemId);
    }

    /**
     * @inheritdoc
     */
    public function getWishlistItemId()
    {
        return $this->getData(self::KEY_WISHLIST_ITEM_ID) != null ? $this->getData(self::KEY_WISHLIST_ITEM_ID) : 0;
    }

    /**
     * @inheritdoc
     */
    public function setSelectedProductId($selectedProductId)
    {
        return $this->setData(self::KEY_SELECTED_PRODUCT_ID, $selectedProductId);
    }

    /**
     * @inheritdoc
     */
    public function getSelectedProductId()
    {
        return $this->getData(self::KEY_SELECTED_PRODUCT_ID) != null ? $this->getData(self::KEY_SELECTED_PRODUCT_ID) : 0;
    }

    /**
     * @inheritdoc
     */
    public function setStockQty($qty)
    {
        return $this->setData(self::STOCK_QTY, $qty);
    }

    /**
     * @inheritdoc
     */
    public function getStockQty()
    {
        return $this->getData(self::STOCK_QTY);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountLabel($discountLabel)
    {
        return $this->setData(self::KEY_DISCOUNT_LABEL, $discountLabel);
    }

    /**
     * @inheritdoc
     */
    public function getDiscountLabel()
    {
        return $this->getData(self::KEY_DISCOUNT_LABEL);
    }
}
