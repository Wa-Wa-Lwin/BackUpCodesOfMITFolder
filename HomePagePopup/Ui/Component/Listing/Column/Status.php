<?php

namespace MIT\HomePagePopup\Ui\Component\Listing\Column;

class Status implements \Magento\Framework\Option\ArrayInterface
{
/**
 * Options getter
 *
 * @return array
 */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Active')], ['value' => 0, 'label' => __('Inactive')]];
    }
}
