<?php

namespace MIT\Product\Api;

interface CustomRefundRequestInterface
{

    /**
     * get refund request configuration
     * @return \Magento\Framework\DataObject
     */
    public function getConfig();

    /**
     * submit refund request by customer
     * @param int @customerId
     * @return \Magento\Framework\DataObject
     */
    public function submitRefundRequest($customerId);
}
