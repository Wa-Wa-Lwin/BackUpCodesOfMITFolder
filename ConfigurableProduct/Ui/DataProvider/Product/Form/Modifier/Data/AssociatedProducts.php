<?php

namespace MIT\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data\AssociatedProducts as DataAssociatedProducts;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;

class AssociatedProducts extends DataAssociatedProducts
{

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ConfigurableType $configurableType
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param VariationMatrix $variationMatrix
     * @param CurrencyInterface $localeCurrency
     * @param JsonHelper $jsonHelper
     * @param ImageHelper $imageHelper
     * @param Escaper|null $escaper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ConfigurableType $configurableType,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        VariationMatrix $variationMatrix,
        CurrencyInterface $localeCurrency,
        JsonHelper $jsonHelper,
        ImageHelper $imageHelper,
        Escaper $escaper = null
    ) {
        parent::__construct(
            $locator,
            $urlBuilder,
            $configurableType,
            $productRepository,
            $stockRegistry,
            $variationMatrix,
            $localeCurrency,
            $jsonHelper,
            $imageHelper,
            $escaper
        );
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * Prepare variations
     *
     * @return void
     * @throws \Zend_Currency_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    protected function prepareVariations()
    {
        $variations = $this->getVariations();
        $productMatrix = [];
        $attributes = [];
        $productIds = [];
        if ($variations) {
            $usedProductAttributes = $this->getUsedAttributes();
            $productByUsedAttributes = $this->getAssociatedProducts();
            $currency = $this->localeCurrency->getCurrency($this->locator->getBaseCurrencyCode());
            $configurableAttributes = $this->getAttributes();
            foreach ($variations as $variation) {
                $attributeValues = [];
                foreach ($usedProductAttributes as $attribute) {
                    $attributeValues[$attribute->getAttributeCode()] = $variation[$attribute->getId()]['value'];
                }
                $key = implode('-', $attributeValues);
                if (isset($productByUsedAttributes[$key])) {
                    $product = $productByUsedAttributes[$key];
                    $price = $product->getPrice();
                    $variationOptions = [];
                    foreach ($usedProductAttributes as $attribute) {
                        if (!isset($attributes[$attribute->getAttributeId()])) {
                            $attributes[$attribute->getAttributeId()] = [
                                'code' => $attribute->getAttributeCode(),
                                'label' => $attribute->getStoreLabel(),
                                'id' => $attribute->getAttributeId(),
                                'position' => $configurableAttributes[$attribute->getAttributeId()]['position'],
                                'chosen' => [],
                            ];
                            $options = $attribute->usesSource() ? $attribute->getSource()->getAllOptions() : [];
                            foreach ($options as $option) {
                                if (!empty($option['value'])) {
                                    $attributes[$attribute->getAttributeId()]['options'][$option['value']] = [
                                        'attribute_code' => $attribute->getAttributeCode(),
                                        'attribute_label' => $attribute->getStoreLabel(0),
                                        'id' => $option['value'],
                                        'label' => $option['label'],
                                        'value' => $option['value'],
                                    ];
                                }
                            }
                        }
                        $optionId = $variation[$attribute->getId()]['value'];
                        $variationOption = [
                            'attribute_code' => $attribute->getAttributeCode(),
                            'attribute_label' => $attribute->getStoreLabel(0),
                            'id' => $optionId,
                            'label' => $variation[$attribute->getId()]['label'],
                            'value' => $optionId,
                        ];
                        $variationOptions[] = $variationOption;
                        $attributes[$attribute->getAttributeId()]['chosen'][$optionId] = $variationOption;
                    }

                    $productMatrix[] = [
                        'id' => $product->getId(),
                        'product_link' => '<a href="' . $this->urlBuilder->getUrl(
                            'catalog/product/edit',
                            ['id' => $product->getId()]
                        ) . '" target="_blank">' . $this->escaper->escapeHtml($product->getName()) . '</a>',
                        'sku' => $product->getSku(),
                        'name' => $this->escaper->escapeHtml($product->getName()),
                        'my_sku' => $this->escaper->escapeHtml($this->getCustomSku($product->getId(), $product->getSku())),
                        'qty' => $this->getProductStockQty($product),
                        'price' => $price,
                        'price_string' => $currency->toCurrency(sprintf("%f", $price)),
                        'price_currency' => $this->locator->getStore()->getBaseCurrency()->getCurrencySymbol(),
                        'configurable_attribute' => $this->getJsonConfigurableAttributes($variationOptions),
                        'weight' => $product->getWeight(),
                        'status' => $product->getStatus(),
                        'variationKey' => $this->getVariationKey($variationOptions),
                        'canEdit' => 0,
                        'newProduct' => 0,
                        'attributes' => $this->getTextAttributes($variationOptions),
                        'thumbnail_image' => $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl(),
                    ];
                    $productIds[] = $product->getId();
                }
            }
        }

        $this->productMatrix = $productMatrix;
        $this->productIds = $productIds;
        $this->productAttributes = array_values($attributes);
    }

    /**
     * get custom sku
     * @param int $productId
     * @return string
     */
    protected function getCustomSku($productId, $sku)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);
        return $product->getMySku();
    }
}
