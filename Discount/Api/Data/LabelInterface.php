<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Api\Data;

interface LabelInterface
{
    const WEBSITE_ID = 'website_id';
    const LABEL_ID = 'label_id';
    const RULE_ID = 'rule_id';
    const SORTER_ORDER = 'sort_order';
    const PRODUCT_ID = 'product_id';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const DISCOUNT_IMG_ID = 'discount_img_id';
    const DISCOUNT_LABEL = 'discount_label';
    const IMG_WIDTH = 'width';
    const IMG_HEIGHT = 'height';
    const FROM_TIME = 'from_time';
    const TO_TIME = 'to_time';
    const DISCOUNT_LABEL_COLOR = "discount_label_color";
    const DISCOUNT_LABEL_STYLE = "discount_label_style";

    /**
     * Get label_id
     * @return string|null
     */
    public function getLabelId();

    /**
     * Set label_id
     * @param string $labelId
     * @return \MIT\Discount\Label\Api\Data\LabelInterface
     */
    public function setLabelId($labelId);

    /**
     * Set rule_id
     * @param string $ruleId
     * @return MIT\Discount\Label\Api\Data\LabelInterface
     */
    public function setRuleId($ruleId);

    /**
     * Get rule_id
     * @return string|null
     */
    public function getRuleId();

    /**
     * Set sort_order
     * @param string $sortOrder
     * @return MIT\Discount\Label\Api\Data\LabelInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Get sort_order
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param string $productId
     * @return \MIT\Discount\Label\Api\Data\LabelInterface
     */
    public function setProductId($productId);

    /**
     * Get customer_group_id
     * @return string|null
     */
    public function getCustomerGroupId();

    /**
     * Set customer_group_id
     * @param string $customerGroupId
     * @return \MIT\Discount\Label\Api\Data\LabelInterface
     */
    public function setCustomerGroupId($customerGroupId);

    /**
     * Get website_id
     * @return string|null
     */
    public function getWebsiteId();

    /**
     * Set website_id
     * @param string $websiteId
     * @return \MIT\Discount\Label\Api\Data\LabelInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * Get discount_label
     * @return string|null
     */
    public function getDiscountLabel();

    /**
     * Set discount_label
     * @param string $discountLabel
     * @return \MIT\Discount\Label\Api\Data\LabelInterface
     */
    public function setDiscountLabel($discountLabel);

    /**
     * Get discount_img_id
     * @return string|null
     */
    public function getDiscountImgId();

    /**
     * Set discount_img_id
     * @param string $discountImgId
     * @return \MIT\Discount\Label\Api\Data\LabelInterface
     */
    public function setDiscountImgId($discountImgId);

    /**
     * set img width
     * @param int $width
     * @return $this
     */
    public function setWidth($width);

    /**
     * get img width
     * @return int
     */
    public function getWidth();

    /**
     * set img height
     * @param int $height
     * @return $this
     */
    public function setHeight($height);

    /**
     * get img height
     * @return int
     */
    public function getHeight();

    /**
     * set from time
     * @param int $fromTime
     * @return $this
     */
    public function setFromTime($fromTime);

    /**
     * get from time
     * @return int
     */
    public function getFromTime();

    /**
     * set to time
     * @param int $fromDate
     * @return $this
     */
    public function setToTime($toTime);

    /**
     * get to time
     * @return int
     */
    public function getToTime();

    /**
     * get discount label color
     * @return string
     */
    public function getDiscountLabelColor();

    /**
     * set discount label color
     * @param string $discountColor
     * @return $this
     */
    public function setDiscountLabelColor($discountColor);

    /**
     * get discount label color
     * @return string
     */
    public function getDiscountLabelStyle();

    /**
     * set discount label color
     * @param string $discountStyle
     * @return $this
     */
    public function setDiscountLabelStyle($discountStyle);
}

