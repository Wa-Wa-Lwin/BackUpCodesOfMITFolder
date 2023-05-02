<?php

namespace MIT\Configuration\Api;

interface ConfigurationApiInterface
{
    /**
     * Check Mobile Version API 
     * @param string $version_number
     * @return array
     */
    public function checkMobileVersion($version_number);

    /**
     * GET Store Info API
     * @return array
     */
    public function getStoreInfo();
}
