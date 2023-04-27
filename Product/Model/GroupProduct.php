<?php
namespace MIT\Product\Model;
use MIT\Product\Api\Data\GroupProductInterface;
use Magento\Framework\Model\AbstractExtensibleModel;


class GroupProduct  extends AbstractExtensibleModel implements GroupProductInterface{
    
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
    public function getName()
    {
        return $this->getData(self::NAME);
    }
    
    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME,$name);
    }

    /**
     * @inheritdoc
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritdoc
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function getRegularPrice()
    {
        return $this->getData(self::REGULAR_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setRegularPrice(string $price)
    {
        return $this->setData(self::REGULAR_PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function getRealPrice()
    {
        return $this->getData(self::REAL_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setRealPrice(string $price)
    {
        return $this->setData(self::REAL_PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->getData(self::QUANTITY);
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($qty)
    {
        return $this->setData(self::QUANTITY, $qty);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultQuantity()
    {
        return $this->getData(self::DEFAULY_QUANTITY);
    }

    /**
     * @inheritdoc
     */
    public function setDefaultQuantity($default_qty)
    {
        return $this->setData(self::DEFAULY_QUANTITY, $default_qty);
    }

 
}