<?php

namespace MIT\SalesRule\Model;

use Magento\SalesRule\Model\Data\Rule as DataRule;
use MIT\SalesRule\Api\Data\RuleInterface;

class Rule extends DataRule implements RuleInterface
{

    const IS_WEEKLY_PROMOTION = 'is_weekly_promotion';

    public function setIsWeeklyPromotion($isWeeklyPromotion)
    {
        return $this->setData(self::IS_WEEKLY_PROMOTION, $isWeeklyPromotion);
    }

    public function getIsWeeklyPromotion()
    {
        return $this->_get(self::IS_WEEKLY_PROMOTION);
    }
}
