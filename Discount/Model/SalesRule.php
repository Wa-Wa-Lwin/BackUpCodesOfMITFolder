<?php

namespace MIT\Discount\Model;

use Magento\SalesRule\Model\Data\Rule;
use MIT\Discount\Api\Data\SalesRuleInterface;

class SalesRule extends Rule implements SalesRuleInterface {

     /**
     * @inheritdoc
     */
    public function setDiscountImageId($discountImgId)
    {
        return $this->setData(self::DISCOUNT_IMG_ID, $discountImgId);
    }

    /**
     * @inheritdoc
     */
    public function getDiscountImageId()
    {
        return $this->_get(self::DISCOUNT_IMG_ID);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountLabel($discountLabel)
    {
        return $this->setData(self::DISCOUNT_LABEL, $discountLabel);
    }

    /**
     * @inheritdoc
     */
    public function getDiscountLabel()
    {
        return $this->_get(self::DISCOUNT_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setWidth($width)
    {
        return $this->setData(self::IMG_WIDTH, $width);
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        return $this->_get(self::IMG_WIDTH);
    }

    /**
     * @inheritDoc
     */
    public function setHeight($height)
    {
        return $this->setData(self::IMG_HEIGHT, $height);
    }

    /**
     * @inheritDoc
     */
    public function getHeight()
    {
        return $this->_get(self::IMG_HEIGHT);
    }
}

