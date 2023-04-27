<?php

namespace MIT\ImportExport\Model\Source\Import\Behavior;

use Magento\ImportExport\Model\Source\Import\AbstractBehavior;
use Magento\ImportExport\Model\Import;

class Custom extends AbstractBehavior {

        /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_REPLACE => __('Update Price')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return 'custom_price_update';
    }
}