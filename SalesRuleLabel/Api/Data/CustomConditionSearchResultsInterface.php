<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\SalesRuleLabel\Api\Data;

interface CustomConditionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get CustomCondition list.
     * @return \MIT\SalesRuleLabel\Api\Data\CustomConditionInterface[]
     */
    public function getItems();

    /**
     * Set rule_id list.
     * @param \MIT\SalesRuleLabel\Api\Data\CustomConditionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}