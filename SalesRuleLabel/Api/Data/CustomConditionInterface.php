<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\SalesRuleLabel\Api\Data;

interface CustomConditionInterface
{

    const FROM_DATE = 'from_date';
    const SORT_ORDER = 'sort_order';
    const RULE_ID = 'rule_id';
    const HEIGHT = 'height';
    const TO_DATE = 'to_date';
    const RULE_NAME = 'rule_name';
    const WEBSITES = 'websites';
    const WIDTH = 'width';
    const RULE_STATUS = 'rule_status';
    const CUSTOMER_GROUPS = 'customer_groups';
    const DISCOUNT_IMAGE_ID = 'discount_image_id';
    const ACTIONS_SERIALIZED = 'actions_serialized';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const SALE_RULE_ID = 'sale_rule_id';
    const DISCOUNT_LABEL = 'discount_label';
    const DISCOUNT_LABEL_COLOR = "discount_label_color";
    const DISCOUNT_LABEL_STYLE = "discount_label_style";

    /**
     * Get rule_id
     * @return string|null
     */
    public function getRuleId();

    /**
     * Set rule_id
     * @param string $ruleId
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setRuleId($ruleId);

    /**
     * Get rule_name
     * @return string|null
     */
    public function getRuleName();

    /**
     * Set rule_name
     * @param string $ruleName
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setRuleName($ruleName);

    /**
     * Get websites
     * @return string|null
     */
    public function getWebsites();

    /**
     * Set websites
     * @param string $websites
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setWebsites($websites);

    /**
     * Get customer_groups
     * @return string|null
     */
    public function getCustomerGroups();

    /**
     * Set customer_groups
     * @param string $customerGroups
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setCustomerGroups($customerGroups);

    /**
     * Get rule_status
     * @return string|null
     */
    public function getRuleStatus();

    /**
     * Set rule_status
     * @param string $ruleStatus
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setRuleStatus($ruleStatus);

    /**
     * Get conditions_serialized
     * @return string|null
     */
    public function getConditionsSerialized();

    /**
     * Set conditions_serialized
     * @param string $conditionsSerialized
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * Get actions_serialized
     * @return string|null
     */
    public function getActionsSerialized();

    /**
     * Set actions_serialized
     * @param string $actionsSerialized
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setActionsSerialized($actionsSerialized);

    /**
     * Get sale_rule_id
     * @return string|null
     */
    public function getSaleRuleId();

    /**
     * Set sale_rule_id
     * @param string $saleRuleId
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setSaleRuleId($saleRuleId);

    /**
     * Get discount_image_id
     * @return string|null
     */
    public function getDiscountImageId();

    /**
     * Set discount_image_id
     * @param string $discountImageId
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setDiscountImageId($discountImageId);

    /**
     * Get discount_label
     * @return string|null
     */
    public function getDiscountLabel();

    /**
     * Set discount_label
     * @param string $discountLabel
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setDiscountLabel($discountLabel);

    /**
     * Get width
     * @return string|null
     */
    public function getWidth();

    /**
     * Set width
     * @param string $width
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setWidth($width);

    /**
     * Get height
     * @return string|null
     */
    public function getHeight();

    /**
     * Set height
     * @param string $height
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setHeight($height);

    /**
     * Get from_date
     * @return string|null
     */
    public function getFromDate();

    /**
     * Set from_date
     * @param string $fromDate
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setFromDate($fromDate);

    /**
     * Get to_date
     * @return string|null
     */
    public function getToDate();

    /**
     * Set to_date
     * @param string $toDate
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setToDate($toDate);

    /**
     * Get sort_order
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Set sort_order
     * @param string $sortOrder
     * @return \MIT\SalesRuleLabel\CustomCondition\Api\Data\CustomConditionInterface
     */
    public function setSortOrder($sortOrder);

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
