<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Model\ResourceModel\LabelImage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'label_image_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \MIT\Discount\Model\LabelImage::class,
            \MIT\Discount\Model\ResourceModel\LabelImage::class
        );
    }
}