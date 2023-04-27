<?php

namespace MIT\Product\Helper;

use Magento\Catalog\Model\ProductRepository;

class BundleProductHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(
        ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * generated bundle items special price
     * @param array bundleProductOptions
     */
    public function generateBundleProductItemNameAndPrice($bundleProductOptions)
    {
        /** @var $option \Magento\Bundle\Model\Option */
        foreach ($bundleProductOptions as $option) {
            /** @var \Magento\Bundle\Api\Data\LinkInterface $selection */
            foreach ($option->getProductLinks() as $selection) {
                $product = $this->productRepository->get($selection->getSku());
                $selection->setProductName($product->getName());
                if (is_null($selection->getPriceType())) {
                    $selection->setPrice($this->getBundleProductItemPrice($product));
                }
            }
        }
    }

    /**
     * get bundle product item price
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    public function getBundleProductItemPrice($product)
    {

        $regularPrice = 0;
        $specialPrice = 0;
        if ($product->getTypeId() == 'configurable') {
            $basePrice = $product->getPriceInfo()->getPrice('regular_price');
            $regularPrice = $basePrice->getMinRegularAmount()->getValue();
            $specialPrice = $product->getFinalPrice();
        } else if ($product->getTypeId() == 'bundle') {
            $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
            $specialPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
        } else if ($product->getTypeId() == 'grouped') {
            $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($this);
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

        if ($specialPrice > 0) {
            return $specialPrice;
        }

        return $regularPrice;
    }
}

