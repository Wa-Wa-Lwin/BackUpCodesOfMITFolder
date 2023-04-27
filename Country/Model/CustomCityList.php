<?php
namespace MIT\Country\Model;
use MIT\Country\Api\Data\CustomCityListInterface;
use Magento\Framework\Model\AbstractExtensibleModel;


class CustomCityList  extends AbstractExtensibleModel implements CustomCityListInterface{
    
   /**
    * @inheritdoc
    */

    public function getId()
    {
        return $this->getData(self::ID);
    }
    /**
    * @inheritdoc
    */
    
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
    * @inheritdoc
    */

    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
    * @inheritdoc
    */

    public function setCity(string $city)
    {
        return $this->setData(self::CITY, $city);
    }

     /**
    * @inheritdoc
    */

    public function getPostCode()
    {
        return $this->getData(self::POSTCODE);
    }

     /**
    * @inheritdoc
    */

    public function setPostCode(string $postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

 
}