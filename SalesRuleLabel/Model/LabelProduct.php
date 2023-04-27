<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\SalesRuleLabel\Model;

use Magento\Framework\Model\AbstractModel;

class LabelProduct extends AbstractModel
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\MIT\SalesRuleLabel\Model\ResourceModel\LabelProduct::class);
    }
}