<?php

namespace MIT\Cart\Model\Quote;

use Magento\Quote\Model\Quote\Item;
use MIT\Cart\Api\Data\CustomCartItemInterface;

class CustomItem extends Item implements CustomCartItemInterface
{

    /**
     * @inheritdoc
     */
    public function setImgPath($imgPath)
    {
        return $this->setData(self::KEY_PRODUCT_IMG_PATH, $imgPath);
    }

    /**
     * @inheritdoc
     */
    public function getImgPath()
    {
        return $this->getData(self::KEY_PRODUCT_IMG_PATH);
    }

    /**
     * @inheritdoc
     */
    public function setSubTotal($subtotal)
    {
        return $this->setData(self::KEY_SUBTOTAL, $subtotal);
    }

    /**
     * @inheritdoc
     */
    public function getSubTotal()
    {
        return $this->getData(self::KEY_SUBTOTAL);
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($attributes)
    {
        return $this->setData(self::KEY_ATTRIBUTES, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return $this->getData(self::KEY_ATTRIBUTES);
    }

    /**
     * @inheritdoc
     */
    public function setParentId($id)
    {
        return $this->setData(self::KEY_PARENT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getParentId()
    {
        return $this->getData(self::KEY_PARENT_ID);
    }
}

