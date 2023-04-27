<?php

namespace MIT\Product\Helper;

use Magento\Framework\App\Helper\Context;

class ProductHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;

    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfigInterface;
    }

    public function getWeightUnit()
    {
        return $this->_scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isReviewEnabled()
    {
        return $this->_scopeConfig->getValue(
            'catalog/review/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isReviewEnabledForGuest()
    {
        return $this->_scopeConfig->getValue(
            'catalog/review/allow_guest',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}

