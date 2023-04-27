<?php

namespace MIT\Integration\Block;


class DatePicker extends \Magento\Config\Block\System\Config\Form\Field
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setDateFormat(\Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT);
        $element->setTimeFormat('HH:mm:ss'); //set date and time as per your need
        $element->setShowsTime(false);
        return parent::render($element);
    }
}
