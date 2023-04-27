<?php

declare(strict_types=1);

namespace MIT\Queue\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const XML_PATH_EMAIL_SENDER     = 'massemailpromotioncustomers/email/identity';
    const XML_PATH_EMAIL_TEMPLATE   = 'massemailpromotioncustomers/email/template';
    const XML_PATH_EMAIL_PRODUCT_SKUS   = 'massemailpromotioncustomers/email/product_skus';


    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve Sender
     *
     * @param int $store
     * @return mixed
     */
    public function getSender($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve Email Template
     *
     * @param int $store
     * @return mixed
     */
    public function getEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve Product Skus
     * 
     * @param int $store
     * @return string
     */
    public function getProductSkus($store = null) {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_PRODUCT_SKUS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
