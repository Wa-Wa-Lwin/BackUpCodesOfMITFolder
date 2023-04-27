<?php

namespace MIT\MobileConfiguration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Data constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get notification version
     *
     * @return mixed
     */
    public function getNotificationVersion()
    {
        return $this->scopeConfig->getValue(
            'mobile_version_configuration/general/notification_version',
            ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * Get force update version
     *
     * @return mixed
     */
    public function getForceUpdateVersion()
    {
        return $this->scopeConfig->getValue(
            'mobile_version_configuration/general/force_update_version',
            ScopeInterface::SCOPE_STORE
        );
    } 

}
