<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MIT\Product\Helper\Product\Options;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader as OptionsLoader;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

/**
 * Class Loader
 */
class Loader extends OptionsLoader
{
    /**
     * @var OptionValueInterfaceFactory
     */
    private $optionValueFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * ReadHandler constructor
     *
     * @param OptionValueInterfaceFactory $optionValueFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        OptionValueInterfaceFactory $optionValueFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        parent::__construct($optionValueFactory, $extensionAttributesJoinProcessor);
        $this->optionValueFactory = $optionValueFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * @param ProductInterface $product
     * @return OptionInterface[]
     */
    public function load(ProductInterface $product)
    {
        $options = [];
        /** @var Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $attributeCollection = $typeInstance->getConfigurableAttributeCollection($product);
        $this->extensionAttributesJoinProcessor->process($attributeCollection);
        foreach ($attributeCollection as $attribute) {
            $values = [];
            $attributeOptions = $attribute->getOptions();
            if (is_array($attributeOptions)) {
                foreach ($attributeOptions as $option) {
                    /** @var \Magento\ConfigurableProduct\Api\Data\OptionValueInterface $value */
                    $value = $this->optionValueFactory->create();
                    $value->setValueIndex($option['value_index']);
                    $customVal = array('value_index' => $option['value_index'], 'label' => $option['label']);
                    $values[] = $customVal;
                    //$values[] = $value;
                }
            }
            $attribute->setValues($values);
            $options[] = $attribute;
        }

        return $options;
    }
}
