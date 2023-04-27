<?php

namespace MIT\StoreInfoConfiguration\Api;

interface StoreInfoConfigurationApiInterface
{
    /**
     * GET for Post api
     * @param string $store_info_phone_number
     * @param string $store_info_mail
     * @param string $store_info_address
     * @param string $version_number
     * @return array
     */
    //
  
    public function getStoreInfo();
}
 
