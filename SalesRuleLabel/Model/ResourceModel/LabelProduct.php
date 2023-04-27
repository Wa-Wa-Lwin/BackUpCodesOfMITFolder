<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\SalesRuleLabel\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LabelProduct extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('mit_salesrule_label_product', 'rule_product_id');
    }
}