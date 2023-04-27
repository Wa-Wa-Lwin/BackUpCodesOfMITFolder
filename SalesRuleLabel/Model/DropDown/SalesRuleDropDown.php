<?php

namespace MIT\SalesRuleLabel\Model\DropDown;

use Magento\Framework\Data\OptionSourceInterface;
use MIT\SalesRuleLabel\Helper\Data;

class SalesRuleDropDown implements OptionSourceInterface
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function toOptionArray()
    {
        return $this->helper->getSalesRuleDropDownList();
    }
}
