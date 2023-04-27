<?php

namespace MIT\Discount\Helper;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class DynamicAttributeShowHelper extends AbstractHelper {
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    public function __construct(
        ProductRepository $productRepository,
        PriceCurrencyInterface $priceCurrency
    )
    {
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
    }


        /**
     * $excludeAttr is optional array of attribute codes to exclude them from additional data array
     *
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdditionalData($productId)
    {
        $data = [];
        $product = $this->productRepository->getById($productId);
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($this->isVisibleOnFrontend($attribute, [])) {
                $value = $attribute->getFrontend()->getValue($product);

                if ($value instanceof Phrase) {
                    $value = (string)$value;
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                if (is_string($value) && strlen(trim($value))) {
                    $data[$attribute->getAttributeCode()] = [
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code' => $attribute->getAttributeCode(),
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * Determine if we should display the attribute on the front-end
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param array $excludeAttr
     * @return bool
     * @since 103.0.0
     */
    protected function isVisibleOnFrontend(
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        array $excludeAttr
    ) {
        return ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr));
    }
}