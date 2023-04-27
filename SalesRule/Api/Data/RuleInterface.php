<?php

namespace MIT\SalesRule\Api\Data;

use Magento\SalesRule\Api\Data\RuleInterface as DataRuleInterface;

interface RuleInterface extends DataRuleInterface
{

    /**
     * set is weekly promotion
     * @param bool $isWeeklyPromotion
     * @return $this
     */
    public function setIsWeeklyPromotion($isWeeklyPromotion);

    /**
     * get is weekly promotion
     * @return bool
     */
    public function getIsWeeklyPromotion();
}
