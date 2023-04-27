<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Label extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('mit_discount_label', 'label_id');
    }
}

