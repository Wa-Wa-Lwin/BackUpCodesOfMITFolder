<?php

namespace MIT\MobileConfiguration\Api;

interface MobileConfigurationApiInterface
{
    /**
     * GET for Post api
     * @param string $version_number
     * @return array
     */
    public function checkMobileVersion($version_number);

    /**
     * GET for Post api 
     * @return array
     */ 
    public function getStoreInfo();
} 
