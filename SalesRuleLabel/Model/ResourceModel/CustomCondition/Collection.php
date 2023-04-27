<?php

namespace MIT\SalesRuleLabel\Model\ResourceModel\CustomCondition;

use MIT\SalesRuleLabel\Model\CustomCondition as CustomConditionModel;
use MIT\SalesRuleLabel\Model\ResourceModel\CustomCondition as CustomConditionResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            CustomConditionModel::class,
            CustomConditionResourceModel::class
        );
    }
}