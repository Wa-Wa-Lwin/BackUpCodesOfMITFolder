<?php

namespace MIT\Delivery\Model\ResourceModel;

class CustomDelivery extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('mit_delivery_customdelivery', 'id');
    }
}
