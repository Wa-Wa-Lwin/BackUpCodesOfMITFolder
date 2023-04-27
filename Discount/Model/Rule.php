<?php

namespace MIT\Discount\Model;

use Magento\CatalogRule\Model\Rule as ModelRule;
use MIT\Discount\Api\Data\RuleInterface;

class Rule extends ModelRule implements RuleInterface
{

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
        return $this->getData(self::DISCOUNT_IMG_ID);
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
        return $this->getData(self::DISCOUNT_LABEL);
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
        return $this->getData(self::IMG_WIDTH);
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
        return $this->getData(self::IMG_HEIGHT);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountLabelColor()
    {
        return $this->getData(self::DISCOUNT_LABEL_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountLabelColor($discountColor)
    {
        return $this->setData(self::DISCOUNT_LABEL_COLOR, $discountColor);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountLabelStyle()
    {
        return $this->getData(self::DISCOUNT_LABEL_STYLE);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountLabelStyle($discountStyle)
    {
        return $this->setData(self::DISCOUNT_LABEL_STYLE, $discountStyle);
    }
}
