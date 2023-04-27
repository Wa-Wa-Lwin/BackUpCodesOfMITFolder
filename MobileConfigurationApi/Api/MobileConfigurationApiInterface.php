<?php

namespace MIT\MobileConfigurationApi\Api;

interface MobileConfigurationApiInterface
{
    /**
     * GET for Post api
     * @param string $version_number
     * @return array
     */
    public function checkMobileVersion($version_number);
} 
