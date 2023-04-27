<?php


namespace MIT\Customer\Plugin\Magento\Customer\Model;

use Magento\Framework\Exception\LocalizedException;
use MIT\Customer\Helper\CustomerHelper;

class AccountManagement
{

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    public function __construct(
        CustomerHelper $customerHelper
    )
    {
        $this->customerHelper = $customerHelper;
    }

    public function beforeCreateAccount(
        \Magento\Customer\Model\AccountManagement $subject,
        $customer,
        $password = null,
        $redirectUrl = ''
    ) {
        if (!$this->customerHelper->validateEmailAndPhone($customer->getEmail())) {
            throw new LocalizedException(
                __('Please enter valid phone number(09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or valid email address (Ex: johndoe@domain.com).')
            );
        }
        $mail = $this->normalizePhoneNumber($customer->getEmail());
        $customer->setEmail($mail);
        return [$customer, $password, $redirectUrl];
    }

    public function beforeIsEmailAvailable(\Magento\Customer\Model\AccountManagement $subject, $customerEmail, $websiteId = null)
    {
        $customerEmail = $this->normalizePhoneNumber($customerEmail);
        return [$customerEmail, $websiteId];
    }

    public function beforeInitiatePasswordReset(\Magento\Customer\Model\AccountManagement $subject, $email, $template, $websiteId = null)
    {
        $email = $this->normalizePhoneNumber($email);
        return [$email, $template, $websiteId];
    }

    public function beforeResetPassword(\Magento\Customer\Model\AccountManagement $subject, $email, $resetToken, $newPassword)
    {
        $email = $this->normalizePhoneNumber($email);
        return [$email, $resetToken, $newPassword];
    }

    private function normalizePhoneNumber($mail)
    {
        if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $mail)) {
            if (preg_match('/^09([0-9]{7,15})$/i', $mail)) {
                $mail = substr_replace($mail, '+959', 0, 2);
            } else if (preg_match('/^959([0-9]{7,15})$/i', $mail)) {
                $mail = '+' . $mail;
            }
        }
        return $mail;
    }
}
