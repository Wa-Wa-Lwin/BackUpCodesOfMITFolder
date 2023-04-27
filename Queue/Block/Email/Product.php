<?php

namespace MIT\Queue\Block\Email;

use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use MIT\Queue\Model\Config;

class Product extends AbstractProduct implements BlockInterface
{
    const DEFAULT_CACHE_TAG = 'MIT_Queue';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var CatalogConfig
     */
    protected $catalogConfig;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrencyInterface;

    /* 
    * Listing constructor.
    *
    * @param Context $context
    * @param Data $helper
    * @param DateUnit $dateUnit
    * @param MembershipFactory $membershipFactory
    * @param MembershipResource $membershipResource
    * @param array $data
    */
    public function __construct(
        Context $context,
        Config $config,
        ProductCollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        CatalogConfig $catalogConfig,
        PriceCurrencyInterface $priceCurrencyInterface,
        array $data = []
    ) {
        $this->config = $config;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->catalogConfig = $catalogConfig;
        $this->priceCurrencyInterface = $priceCurrencyInterface;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $this->setProductCollection($this->getProductList());
        return parent::_beforeToHtml();
    }

    /**
     * format price
     * @param float $amount
     * @return string
     */
    public function formatPrice($amount) {
        return $this->priceCurrencyInterface->convertAndFormat($amount, true, 0);
    }

    /**
     * get product list
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection | null
     */
    public function getProductList()
    {
        $productListString = $this->config->getProductSkus();
        if ($productListString) {
            $productSkus = explode(',', $productListString);
            $productSkus = array_map('trim', $productSkus);
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect(['name','sku']);
            $collection->addFieldToFilter('sku', ['in' => $productSkus]);
            $collection->distinct(true);

            $collection->addMinimalPrice()
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addUrlRewrite()
            ->setVisibility($this->catalogProductVisibility->getVisibleInSiteIds());

            if (is_array($productSkus) > 0) {
                $condition = "'" . implode("','", $productSkus) . "'";
                $collection->getSelect()->order('FIELD(e.sku,' . $condition . ')');
            }
            return $collection;
        }
        return null;
    }

    /**
     * get product price data
     * @param \Magento\Catalog\Model\Product $product
     * @return @array
     */
    public function getProductPriceData($product) {
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

        if ($regularPrice == $specialPrice) {
            $specialPrice = 0;
        }
        return array('regular' => $regularPrice, 'special' => $specialPrice);
    }

}
