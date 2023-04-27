<?php

namespace MIT\Swatches\Block\Product\Renderer\Listing;

use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as TypeConfigurable;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use MIT\Swatches\Helper\ConfigurableProductHelper;
use MIT\Swatches\Helper\ProductAttributesHelper;

class Configurable extends TypeConfigurable
{

    /**
     * @var ProductAttributesHelper
     */
    private $productAttributesHelper;

    /**
     * @var Format
     */
    private $localeFormat;

    /**
     * @var ConfigurableProductHelper
     */
    private $configurableProductHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\ConfigurableProduct\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        ProductAttributesHelper $productAttributesHelper,
        ConfigurableProductHelper $configurableProductHelper,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        SwatchAttributesProvider $swatchAttributesProvider = null,
        UrlBuilder $imageUrlBuilder = null,
        array $data = [],
        Format $localeFormat = null,
        Session $customerSession = null,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices $variationPrices = null
    ) {

        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data
        );
        $this->localeFormat = $localeFormat ?: ObjectManager::getInstance()->get(Format::class);
        $this->customerSession = $customerSession ?: ObjectManager::getInstance()->get(Session::class);
        $this->variationPrices = $variationPrices ?: ObjectManager::getInstance()->get(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices::class
        );
        $this->productAttributesHelper = $productAttributesHelper;
        $this->configurableProductHelper = $configurableProductHelper;
    }

    public function getJsonConfig()
    {
        $this->unsetData('allow_products');
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();
        $this->productAttributesHelper->setProduct($currentProduct);

        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

        $options = $this->helper->getOptions($currentProduct, $this->productAttributesHelper->getAllowProducts());

        $attributecustom = $attributesData['attributes'];
        $outOfStockAttr = $this->configurableProductHelper->getOutofStockProductIdsByParent($currentProduct);
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
            'images' => $this->getOptionImages(),
            'index' => isset($options['index']) ? $options['index'] : [],
        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, $this->_getAdditionalConfig());
        return $this->jsonEncoder->encode($config);
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
        $currentProduct = $this->getProduct();
        $this->productAttributesHelper->setProduct($currentProduct);
        $ids = [];
        foreach ($this->productAttributesHelper->getAllowProducts() as $product) {
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
}
