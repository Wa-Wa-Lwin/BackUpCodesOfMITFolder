<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\SalesRuleLabel\Model\ResourceModel\LabelProduct;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'rule_product_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \MIT\SalesRuleLabel\Model\LabelProduct::class,
            \MIT\SalesRuleLabel\Model\ResourceModel\LabelProduct::class
        );
    }
}