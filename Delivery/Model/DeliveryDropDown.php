<?php

namespace MIT\Delivery\Model;

use Magento\Framework\Data\OptionSourceInterface;
use \Payment\Kbz\Helper\Data as Helper;

class DeliveryDropDown implements OptionSourceInterface
{

    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    public function toOptionArray()
    {
        return $this->getOptions();
    }

    protected function getOptions()
    {
        $res[] = [];
        $res[] = array('label' => $this->_helper->getConfig("carriers/customshipping/title"), 'value' => 0);
        $res[] = array('label' => $this->_helper->getConfig("carriers/customshippingone/title"), 'value' => 1);
        $res[] = array('label' => $this->_helper->getConfig("carriers/customshippingtwo/title"), 'value' => 2);
        $res[] = array('label' => $this->_helper->getConfig("carriers/customshippingthree/title"), 'value' => 3);
        $res[] = array('label' => $this->_helper->getConfig("carriers/customshippingfour/title"), 'value' => 4);
        return $res;
    }
}
