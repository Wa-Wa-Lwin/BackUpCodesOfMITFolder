<?php

namespace MIT\Customer\Block\Adminhtml\Order\Create\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\Account as FormAccount;
use Magento\Store\Model\ScopeInterface;

class Account extends FormAccount
{
	
    private const XML_PATH_EMAIL_REQUIRED_CREATE_ORDER = 'customer/create_account/email_required_create_order';

    protected function _addAdditionalFormElementData(AbstractElement $element)
    {
        switch ($element->getId()) {
            case 'email':
                $element->setRequired($this->isEmailRequiredToCreateOrder());
                $element->setClass('validate-phone-number-custom admin__control-text');
                break;
        }
        return $this;
    }

    private function isEmailRequiredToCreateOrder()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_REQUIRED_CREATE_ORDER,
            ScopeInterface::SCOPE_STORE
        );
    }
}
