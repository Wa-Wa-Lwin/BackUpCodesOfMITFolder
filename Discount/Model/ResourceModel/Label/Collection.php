<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Model\ResourceModel\Label;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'label_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \MIT\Discount\Model\Label::class,
            \MIT\Discount\Model\ResourceModel\Label::class
        );
    }
}

