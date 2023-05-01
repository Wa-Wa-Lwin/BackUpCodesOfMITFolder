<?php

namespace MIT\Configuration\Api;

interface ConfigurationApiInterface
{
    /**
     * POST for Post api
     * @param string $version_number
     * @return array
     */
    public function checkMobileVersion($version_number);

    /**
     * GET for Get api 
     * @return array
     */ 
    public function getStoreInfo();
} 
