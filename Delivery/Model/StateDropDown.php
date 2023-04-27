<?php

namespace MIT\Delivery\Model;

use Magento\Framework\Data\OptionSourceInterface;
use \Payment\Kbz\Helper\Data as Helper;

class StateDropDown implements OptionSourceInterface
{

    public function __construct(
        \Magento\Directory\Model\Country $country,
        Helper $helper
    ) {
        $this->country = $country;
        $this->_helper = $helper;
    }

    public function toOptionArray()
    {
        return $this->getOptions();
    }

    protected function getOptions()
    {
        // $countryCode = Mage::getStoreConfig('general/country/default');
        $countryCode = $this->_helper->getConfig("general/country/default");
        $regionCollection = $this->country->loadByCode($countryCode)->getRegions();
        $regions = $regionCollection->loadData()->toOptionArray(false);
        return $regions;
        // return $result;
    }
}
