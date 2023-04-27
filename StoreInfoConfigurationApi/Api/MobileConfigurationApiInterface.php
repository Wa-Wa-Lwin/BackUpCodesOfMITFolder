<?php

namespace MIT\StoreInfoConfigurationApi\Api;

interface StoreInfoConfigurationApiInterface
{
    /**
     * GET for Post api
     * @param string $store_info_phone_number
     * @param string $store_info_mail
     * @param string $store_info_address
     * @return array
     */
    public function getStoreInfo($store_info_phone_number, $store_info_mail, $store_info_address);
} 
