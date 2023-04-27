<?php

namespace MIT\Delivery\Model;

use Magento\Framework\DataObject\IdentityInterface;
use MIT\Delivery\Api\Data\CustomDeliveryInterface;
use MIT\Delivery\Model\ResourceModel\CustomDelivery as CustomDeliveryResourceModel;

class CustomDelivery extends \Magento\Framework\Model\AbstractModel implements CustomDeliveryInterface, IdentityInterface
{

    const ENTITY = 'mit_delivery_customdelivery';

    const CACHE_TAG = 'mit_delivery';

    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init(CustomDeliveryResourceModel::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    // public function getCountry()
    // {
    //     return $this->getData(self::COUNTRY);
    // }

    // public function setCountry($country)
    // {
    //     return $this->setData(self::COUNTRY, $country);
    // }

    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    public function setWeight($weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }

    public function setItems($items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    public function getTotal()
    {
        return $this->getData(self::TOTAL);
    }

    public function setTotal($total)
    {
        return $this->setData(self::TOTAL, $total);
    }

    public function getShipping()
    {
        return $this->getData(self::SHIPPING);
    }

    public function setShipping($shipping)
    {
        return $this->setData(self::SHIPPING, $shipping);
    }

    public function getDeliveryType()
    {
        return $this->getData(self::DELIVERY_TYPE);
    }

    public function setDeliveryType($deliveryType)
    {
        return $this->setData(self::DELIVERY_TYPE, $deliveryType);
    }

}
