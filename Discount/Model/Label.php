<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\Discount\Model;

use MIT\Discount\Api\Data\LabelInterface;
use Magento\Framework\Model\AbstractModel;

class Label extends AbstractModel implements LabelInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\MIT\Discount\Model\ResourceModel\Label::class);
    }

    /**
     * @inheritDoc
     */
    public function getLabelId()
    {
        return $this->getData(self::LABEL_ID);
    }

    /**
     * @inheritDoc
     */
    public function setLabelId($labelId)
    {
        return $this->setData(self::LABEL_ID, $labelId);
    }

    /**
     * @inheritDoc
     */
    public function setRuleId($ruleId)
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    /**
     * @inheritDoc
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORTER_ORDER, $sortOrder);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORTER_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerGroupId()
    {
        return $this->getData(self::CUSTOMER_GROUP_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData(self::CUSTOMER_GROUP_ID, $customerGroupId);
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountLabel()
    {
        return $this->getData(self::DISCOUNT_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountLabel($discountLabel)
    {
        return $this->setData(self::DISCOUNT_LABEL, $discountLabel);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountImgId()
    {
        return $this->getData(self::DISCOUNT_IMG_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountImgId($discountImgId)
    {
        return $this->setData(self::DISCOUNT_IMG_ID, $discountImgId);
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
    public function setFromTime($fromDate)
    {
        return $this->setData(self::FROM_TIME, $fromDate);
    }

    /**
     * @inheritDoc
     */
    public function getFromTime()
    {
        return $this->getData(self::FROM_TIME);
    }

    /**
     * @inheritDoc
     */
    public function setToTime($toDate)
    {
        return $this->setData(self::TO_TIME, $toDate);
    }

    /**
     * @inheritDoc
     */
    public function getToTime()
    {
        return $this->getData(self::TO_TIME);
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
