<?php

namespace MIT\Swatches\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Model\Swatch;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use MIT\Swatches\Helper\ConfigurableProductHelper as CustomConfigurableHelper;

class ProductAttributesHelper extends AbstractHelper
{
    /**
     * Config path if swatch tooltips are enabled
     */
    private const XML_PATH_SHOW_SWATCH_TOOLTIP = 'catalog/frontend/show_swatch_tooltip';

    /**
     * Config path which contains number of swatches per product
     */
    private const XML_PATH_SWATCHES_PER_PRODUCT = 'catalog/frontend/swatches_per_product';

    /**
     * Action name for ajax request
     */
    const MEDIA_CALLBACK_ACTION = 'swatches/ajax/media';

    /**
     * Name of swatch image for json config
     */
    const SWATCH_IMAGE_NAME = 'swatchImage';

    /**
     * Name of swatch thumbnail for json config
     */
    const SWATCH_THUMBNAIL_NAME = 'swatchThumb';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data
     */
    private $helper;

    /**
     * @var ConfigurableAttributeData
     */
    protected $configurableAttributeData;

    /**
     * @var Format
     */
    private $localeFormat;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices
     */
    private $variationPrices;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    private $swatchHelper;

    /**
     * @var \Magento\Swatches\Helper\Media
     */
    private $swatchMediaHelper;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var ConfigurableProductHelper
     */
    private $configurableProductHelper;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var CustomConfigurableHelper
     */
    private $customConfigurableHelper;

    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        StoreManagerInterface $storeManagerInterface,
        \Magento\ConfigurableProduct\Helper\Data $helper,
        ConfigurableAttributeData $configurableAttributeData,
        PriceCurrencyInterface $priceCurrency,
        EncoderInterface $jsonEncoder,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Helper\Media $swatchMediaHelper,
        UrlInterface $urlInterface,
        ConfigurableProductHelper $configurableProductHelper,
        Configurable $configurable,
        CustomConfigurableHelper $customConfigurableHelper,
        Format $localeFormat = null,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices $variationPrices = null,
        UrlBuilder $imageUrlBuilder = null
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->configurableAttributeData = $configurableAttributeData;
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;
        $this->jsonEncoder = $jsonEncoder;
        $this->swatchHelper = $swatchHelper;
        $this->swatchMediaHelper = $swatchMediaHelper;
        $this->urlInterface = $urlInterface;
        $this->localeFormat = $localeFormat ?: ObjectManager::getInstance()->get(Format::class);
        $this->product = null;
        $this->variationPrices = $variationPrices ?: ObjectManager::getInstance()->get(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices::class
        );
        $this->imageUrlBuilder = $imageUrlBuilder ?? ObjectManager::getInstance()->get(UrlBuilder::class);
        $this->configurableProductHelper = $configurableProductHelper;
        $this->configurable = $configurable;
        $this->customConfigurableHelper = $customConfigurableHelper;
    }

    /**
     * Get number of swatches from config to show on product listing.
     *
     * Other swatches can be shown after click button 'Show more'
     *
     * @return string
     */
    public function getNumberSwatchesPerProduct()
    {
        return $this->scopeConfigInterface->getValue(
            self::XML_PATH_SWATCHES_PER_PRODUCT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config if swatch tooltips should be rendered.
     *
     * @return string
     */
    public function getShowSwatchTooltip()
    {
        return $this->scopeConfigInterface->getValue(
            self::XML_PATH_SHOW_SWATCH_TOOLTIP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * set product
     * @param \Magento\Catalog\Model\Product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Retrieve current store
     *
     * @return \Magento\Store\Model\Store
     */
    public function getCurrentStore()
    {
        return $this->storeManagerInterface->getStore();
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();
        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

        $this->configurableProductHelper->getOutofStockProductIdsByParent($currentProduct);

        $attributecustom = $attributesData['attributes'];
        $skuList = $this->getChildSkuList();
        $index = isset($options['index']) ? $options['index'] : [];
        $outOfStockAttr = $this->customConfigurableHelper->getOutofStockProductIdsByParent($currentProduct);
        if ($outOfStockAttr > 0) {
            foreach($outOfStockAttr as $attrData) {
                foreach($attrData as $key=>$val) {
                    $isAttrCodeExist = false;
                    if (count($attributecustom[$key]['options']) > 0) {
                        foreach($attributecustom[$key]['options'] as $attr) {
                            if ($attr['id'] == $val['id']) {
                                $isAttrCodeExist = true;
                                break;
                            }
                        }
                        if (!$isAttrCodeExist) {
                            $attributecustom[$key]['options'][] = $val;
                        }
                    } else {
                        $attributecustom[$key]['options'][] = $val;
                    }
                }
            }
        }

        $config = [
            'attributes' => $attributecustom,
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => $this->variationPrices->getFormattedPrices($this->getProduct()->getPriceInfo()),
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => [], //$this->getOptionImages(),
            'index' => $index,
            'sku' => $skuList,
            'channel' => 'website',
            'salesChannelCode' => 'base'
        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, $this->_getAdditionalConfig());
        return $this->jsonEncoder->encode($config);
    }

    /**
     * get used ajax
     * @return bool
     */
    public function getUsedAjax()
    {
        return true;
    }

    /**
     * get child skus
     * @return array
     */
    public function getChildSkuList()
    {
        $skuList = [];
        $childProducts = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct());
        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($childProducts as $product) {
            if ((int) $product->getStatus() === Status::STATUS_ENABLED) {
                $skuList[$product->getId()] = $product->getSku();
            }
        }
        return $skuList;
    }

    /**
     * Returns additional values for js config, con be overridden by descendants
     *
     * @return array
     */
    protected function _getAdditionalConfig()
    {
        return [];
    }

    /**
     * Composes configuration for js price format
     *
     * @return string
     * @since 100.2.3
     */
    public function getPriceFormatJson()
    {
        return $this->jsonEncoder->encode($this->localeFormat->getPriceFormat());
    }

    /**
     * Composes configuration for js price
     *
     * @return string
     * @since 100.2.3
     */
    public function getPricesJson()
    {
        return $this->jsonEncoder->encode(
            $this->variationPrices->getFormattedPrices($this->getProduct()->getPriceInfo())
        );
    }

    public function getMediaCallback()
    {
        return $this->urlInterface->getUrl(self::MEDIA_CALLBACK_ACTION, ['_secure' => true]);
    }

    /**
     * Get product images for configurable variations
     *
     * @return array
     * @since 100.1.10
     */
    protected function getOptionImages()
    {
        $images = [];
        foreach ($this->getAllowProducts() as $product) {
            $productImages = $this->helper->getGalleryImages($product) ?: [];
            foreach ($productImages as $image) {
                $images[$product->getId()][] =
                    [
                        'thumb' => $image->getData('small_image_url'),
                        'img' => $image->getData('medium_image_url'),
                        'full' => $image->getData('large_image_url'),
                        'caption' => $image->getLabel(),
                        'position' => $image->getPosition(),
                        'isMain' => $image->getFile() == $product->getImage(),
                        'type' => str_replace('external-', '', $image->getMediaType()),
                        'videoUrl' => $image->getVideoUrl(),
                    ];
            }
        }

        return $images;
    }

    /* Get Swatch config data
     *
     * @return string
     */
    public function getJsonSwatchConfig()
    {
        $attributesData = $this->getSwatchAttributesData();
        foreach ($attributesData as $key => $attribute) {
            if ($attribute['used_in_product_listing'] != "1") {
                unset($attributesData[$key]);
            }
        }
        $allOptionIds = $this->getConfigurableOptionsIds($attributesData);
        $swatchesData = $this->swatchHelper->getSwatchesByOptionsId($allOptionIds);

        $config = [];
        foreach ($attributesData as $attributeId => $attributeDataArray) {
            if (isset($attributeDataArray['options'])) {
                $config[$attributeId] = $this->addSwatchDataForAttribute(
                    $attributeDataArray['options'],
                    $swatchesData,
                    $attributeDataArray
                );
            }
            if (isset($attributeDataArray['additional_data'])) {
                $config[$attributeId]['additional_data'] = $attributeDataArray['additional_data'];
            }
        }
        return $this->jsonEncoder->encode($config);
    }

    /**
     * Get Swatch image size config data.
     *
     * @return string
     * @since 100.2.5
     */
    public function getJsonSwatchSizeConfig()
    {
        $imageConfig = $this->swatchMediaHelper->getImageConfig();
        $sizeConfig = [];

        $sizeConfig[self::SWATCH_IMAGE_NAME]['width'] = $imageConfig[Swatch::SWATCH_IMAGE_NAME]['width'];
        $sizeConfig[self::SWATCH_IMAGE_NAME]['height'] = $imageConfig[Swatch::SWATCH_IMAGE_NAME]['height'];
        $sizeConfig[self::SWATCH_THUMBNAIL_NAME]['height'] = $imageConfig[Swatch::SWATCH_THUMBNAIL_NAME]['height'];
        $sizeConfig[self::SWATCH_THUMBNAIL_NAME]['width'] = $imageConfig[Swatch::SWATCH_THUMBNAIL_NAME]['width'];

        return $this->jsonEncoder->encode($sizeConfig);
    }

    /**
     * Add Swatch Data for attribute
     *
     * @param array $options
     * @param array $swatchesCollectionArray
     * @param array $attributeDataArray
     * @return array
     */
    protected function addSwatchDataForAttribute(
        array $options,
        array $swatchesCollectionArray,
        array $attributeDataArray
    ) {
        $result = [];
        foreach ($options as $optionId => $label) {
            if (isset($swatchesCollectionArray[$optionId])) {
                $result[$optionId] = $this->extractNecessarySwatchData($swatchesCollectionArray[$optionId]);
                $result[$optionId] = $this->addAdditionalMediaData($result[$optionId], $optionId, $attributeDataArray);
                $result[$optionId]['label'] = $label;
            }
        }

        return $result;
    }

    /**
     * Retrieve Swatch data for config
     *
     * @param array $swatchDataArray
     * @return array
     */
    protected function extractNecessarySwatchData(array $swatchDataArray)
    {
        $result['type'] = $swatchDataArray['type'];

        if ($result['type'] == Swatch::SWATCH_TYPE_VISUAL_IMAGE && !empty($swatchDataArray['value'])) {
            $result['value'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_IMAGE_NAME,
                $swatchDataArray['value']
            );
            $result['thumb'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_THUMBNAIL_NAME,
                $swatchDataArray['value']
            );
        } else {
            $result['value'] = $swatchDataArray['value'];
        }

        return $result;
    }

    /**
     * Add media from variation
     *
     * @param array $swatch
     * @param integer $optionId
     * @param array $attributeDataArray
     * @return array
     */
    protected function addAdditionalMediaData(array $swatch, $optionId, array $attributeDataArray)
    {
        if (
            isset($attributeDataArray['use_product_image_for_swatch'])
            && $attributeDataArray['use_product_image_for_swatch']
        ) {
            $variationMedia = $this->getVariationMedia($attributeDataArray['attribute_code'], $optionId);
            if (!empty($variationMedia)) {
                $swatch['type'] = Swatch::SWATCH_TYPE_VISUAL_IMAGE;
                $swatch = array_merge($swatch, $variationMedia);
            }
        }
        return $swatch;
    }

    /**
     * Generate Product Media array
     *
     * @param string $attributeCode
     * @param integer $optionId
     * @return array
     */
    protected function getVariationMedia($attributeCode, $optionId)
    {
        $variationProduct = $this->swatchHelper->loadFirstVariationWithSwatchImage(
            $this->getProduct(),
            [$attributeCode => $optionId]
        );

        if (!$variationProduct) {
            $variationProduct = $this->swatchHelper->loadFirstVariationWithImage(
                $this->getProduct(),
                [$attributeCode => $optionId]
            );
        }

        $variationMediaArray = [];
        if ($variationProduct) {
            $variationMediaArray = [
                'value' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_IMAGE_NAME),
                'thumb' => $this->getSwatchProductImage($variationProduct, Swatch::SWATCH_THUMBNAIL_NAME),
            ];
        }

        return $variationMediaArray;
    }

    /**
     * Get swatch product image.
     *
     * @param \Magento\Catalog\Model\Product $childProduct
     * @param string $imageType
     * @return string
     */
    protected function getSwatchProductImage(\Magento\Catalog\Model\Product $childProduct, $imageType)
    {
        if ($this->isProductHasImage($childProduct, Swatch::SWATCH_IMAGE_NAME)) {
            $swatchImageId = $imageType;
            $imageAttributes = ['type' => Swatch::SWATCH_IMAGE_NAME];
        } elseif ($this->isProductHasImage($childProduct, 'image')) {
            $swatchImageId = $imageType == Swatch::SWATCH_IMAGE_NAME ? 'swatch_image_base' : 'swatch_thumb_base';
            $imageAttributes = ['type' => 'image'];
        }

        if (!empty($swatchImageId) && !empty($imageAttributes['type'])) {
            return $this->imageUrlBuilder->getUrl($childProduct->getData($imageAttributes['type']), $swatchImageId);
        }
    }

    /**
     * Check if product have image.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageType
     * @return bool
     */
    protected function isProductHasImage(\Magento\Catalog\Model\Product $product, $imageType)
    {
        return $product->getData($imageType) !== null && $product->getData($imageType) != SwatchData::EMPTY_IMAGE_VALUE;
    }

    /**
     * Get swatch attributes data.
     *
     * @return array
     */
    protected function getSwatchAttributesData()
    {
        return $this->swatchHelper->getSwatchAttributesAsArray($this->getProduct());
    }

    /**
     * Get configurable options ids.
     *
     * @param array $attributeData
     * @return array
     * @since 100.0.3
     */
    protected function getConfigurableOptionsIds(array $attributeData)
    {
        $ids = [];
        foreach ($this->getAllowProducts() as $product) {
            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute */
            foreach ($this->helper->getAllowAttributes($this->getProduct()) as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                if (isset($attributeData[$productAttributeId])) {
                    $ids[$product->getData($productAttribute->getAttributeCode())] = 1;
                }
            }
        }
        return array_keys($ids);
    }

    /**
     * Collect price options
     *
     * @return array
     */
    protected function getOptionPrices()
    {
        $prices = [];
        foreach ($this->getAllowProducts() as $product) {
            $priceInfo = $product->getPriceInfo();

            $prices[$product->getId()] = [
                'baseOldPrice' => [
                    'amount' => $this->localeFormat->getNumber(
                        $priceInfo->getPrice('regular_price')->getAmount()->getBaseAmount()
                    ),
                ],
                'oldPrice' => [
                    'amount' => $this->localeFormat->getNumber(
                        $priceInfo->getPrice('regular_price')->getAmount()->getValue()
                    ),
                ],
                'basePrice' => [
                    'amount' => $this->localeFormat->getNumber(
                        $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount()
                    ),
                ],
                'finalPrice' => [
                    'amount' => $this->localeFormat->getNumber(
                        $priceInfo->getPrice('final_price')->getAmount()->getValue()
                    ),
                ],
                'tierPrices' => $this->getTierPricesByProduct($product),
                'msrpPrice' => [
                    'amount' => $this->localeFormat->getNumber(
                        $this->priceCurrency->convertAndRound($product->getMsrp())
                    ),
                ],
            ];
        }

        return $prices;
    }

    /**
     * Returns product's tier prices list
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getTierPricesByProduct(ProductInterface $product): array
    {
        $tierPrices = [];
        $tierPriceModel = $product->getPriceInfo()->getPrice('tier_price');
        foreach ($tierPriceModel->getTierPriceList() as $tierPrice) {
            $tierPriceData = [
                'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                'price' => $this->localeFormat->getNumber($tierPrice['price']->getValue()),
                'percentage' => $this->localeFormat->getNumber(
                    $tierPriceModel->getSavePercent($tierPrice['price'])
                ),
            ];

            if (isset($tierPrice['excl_tax_price'])) {
                $excludingTax = $tierPrice['excl_tax_price'];
                $tierPriceData['excl_tax_price'] = $this->localeFormat->getNumber($excludingTax->getBaseAmount());
            }
            $tierPrices[] = $tierPriceData;
        }

        return $tierPrices;
    }

    /**
     * Get Allowed Products
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getAllowProducts()
    {
        $products = [];
        $allProducts = $this->getChildProductsByParent($this->getProduct());
        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($allProducts as $product) {
            if ((int) $product->getStatus() === Status::STATUS_ENABLED) {
                $products[] = $product;
            }
        }       
        return $products;
    }

    /**
     * get child product list by parent id for both instock and out of stock
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getChildProductsByParent($product) {

        $collection = $this->configurable->getUsedProductCollection($product);
        $collection->setFlag('has_stock_status_filter', false);
        $collection
            ->addAttributeToSelect(['sku', 'entity_id'])
            ->addFilterByRequiredOptions()
            ->setStoreId($product->getStoreId());

        $collection->addMediaGalleryData();
        $collection->addTierPriceData();

        return $collection->getItems();
    }
}
