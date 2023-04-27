<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\Customer\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use MIT\Customer\Helper\AttributeChecker;
use MIT\Customer\Helper\CouponHelper;
use MIT\Customer\Helper\SMSSender;

/**
 * Customer email notification
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerEmailNotification extends \Magento\Customer\Model\EmailNotification
{
    /**#@+
     * Configuration paths for email templates and identities
     */
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'customer/password/reset_password_template';

    const XML_PATH_CHANGE_EMAIL_TEMPLATE = 'customer/account_information/change_email_template';

    const XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE =
    'customer/account_information/change_email_and_password_template';

    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/forgot_email_template';

    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'customer/password/remind_email_template';

    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';

    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'customer/create_account/email_no_password_template';

    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';

    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';

    const ACCOUNT_CONFIRM_OTP_EMAIL_TEMPLATE_MOBILE = 'account_confirm_otp_email_template_mobile';

    /**
     * self::NEW_ACCOUNT_EMAIL_REGISTERED               welcome email, when confirmation is disabled
     *                                                  and password is set
     * self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD   welcome email, when confirmation is disabled
     *                                                  and password is not set
     * self::NEW_ACCOUNT_EMAIL_CONFIRMED                welcome email, when confirmation is enabled
     *                                                  and password is set
     * self::NEW_ACCOUNT_EMAIL_CONFIRMATION             email with confirmation link
     */
    const TEMPLATE_TYPES = [
        self::NEW_ACCOUNT_EMAIL_REGISTERED => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_CONFIRMED => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_CONFIRMATION => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
    ];

    /**#@-*/

    /**#@-*/
    private $customerRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var SMSSender
     */
    protected $_smsSender;

    /**
     * @var AttributeChecker
     */
    private $_helper;

    /**
     * @var CouponHelper
     */
    private $couponHelper;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param CustomerViewHelper $customerViewHelper
     * @param DataObjectProcessor $dataProcessor
     * @param ScopeConfigInterface $scopeConfig
     * @param CouponHelper $couponHelper
     * @param AttributeChecker $checker
     * @param SMSSender $smsSender
     * @param SenderResolverInterface|null $senderResolver
     * @param Emulation|null $emulation
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        CustomerViewHelper $customerViewHelper,
        DataObjectProcessor $dataProcessor,
        ScopeConfigInterface $scopeConfig,
        CouponHelper $couponHelper,
        AttributeChecker $checker,
        SMSSender $smsSender,
        SenderResolverInterface $senderResolver = null,
        Emulation $emulation = null
    ) {
        parent::__construct($customerRegistry, $storeManager, $transportBuilder, $customerViewHelper, $dataProcessor, $scopeConfig, $senderResolver, $emulation);
        $this->customerRegistry = $customerRegistry;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->customerViewHelper = $customerViewHelper;
        $this->dataProcessor = $dataProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->senderResolver = $senderResolver ?? ObjectManager::getInstance()->get(SenderResolverInterface::class);
        $this->emulation = $emulation ?? ObjectManager::getInstance()->get(Emulation::class);
        $this->_helper = $checker;
        $this->_smsSender = $smsSender;
        $this->couponHelper = $couponHelper;
    }

    /**
     * Send notification to customer when email or/and password changed
     *
     * @param CustomerInterface $savedCustomer
     * @param string $origCustomerEmail
     * @param bool $isPasswordChanged
     * @return void
     */
    public function credentialsChanged(
        CustomerInterface $savedCustomer,
        $origCustomerEmail,
        $isPasswordChanged = false
    ): void {
        if ($origCustomerEmail != $savedCustomer->getEmail()) {
            if ($isPasswordChanged) {
                $this->emailAndPasswordChanged($savedCustomer, $origCustomerEmail);
                $this->emailAndPasswordChanged($savedCustomer, $savedCustomer->getEmail());
                return;
            }

            $this->emailChanged($savedCustomer, $origCustomerEmail);
            $this->emailChanged($savedCustomer, $savedCustomer->getEmail());
            return;
        }

        if ($isPasswordChanged) {
            $this->passwordReset($savedCustomer);
        }
    }

    /**
     * Send email to customer when his email and password is changed
     *
     * @param CustomerInterface $customer
     * @param string $email
     * @return void
     */
    private function emailAndPasswordChanged(CustomerInterface $customer, $email): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreIdChild($customer);
        }

        $customerEmailData = $this->getFullCustomerObjectChild($customer);

        $email = $customerEmailData->getEmail();
        $loginType = $this->_helper->checkEmailOrPhone($email);

        if ($loginType == AttributeChecker::PHONE_TYPE) {
            $message = 'We have received a request to change the email or phone and password associated with your account.';
            $this->_smsSender->sendSMS($email, $message);
        } else if ($loginType == AttributeChecker::EMAIL_TYPE) {
            $this->sendEmailTemplate(
                $customer,
                self::XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE,
                self::XML_PATH_FORGOT_EMAIL_IDENTITY,
                ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
                $storeId,
                $email
            );
        }
    }

    /**
     * Send email to customer when his email is changed
     *
     * @param CustomerInterface $customer
     * @param string $email
     * @return void
     */


    // return function () {
    //     $.validator.addMethod(
    //       'validate-phone-number-custom',
    //       function (value) {
    //         if (value === '' || value == null || value.length === 0) {
    //           return false;
    //         } else if (/^[0-9+]*$/.test(value)) {
    //           return /^(09|959|\+)([0-9]{7,15})$/i.test(value);
    //         } else {
    //           return /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value);//eslint-disable-line max-len
    //         }
    //       },
    //       $.mage.__('Please enter valid phone number(09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or valid email address (Ex: johndoe@domain.com).')
    //     )
    //   } 

    private function emailChanged(CustomerInterface $customer, $email): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreIdChild($customer);
        }
        $customerEmailData = $this->getFullCustomerObjectChild($customer);
        $email = $customerEmailData->getEmail();
        
        if (preg_match('/^([a-z0-9,!\#\$%&\'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&\'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i', $email))
        {
            $loginType = $this->_helper->checkEmailOrPhone($email);

            if ($loginType == AttributeChecker::PHONE_TYPE) {
                $message = 'We have received a request to change the email or phone associated with your account.';
                $this->_smsSender->sendSMS($email, $message);
            } else if ($loginType == AttributeChecker::EMAIL_TYPE) {
                $this->sendEmailTemplate(
                    $customer,
                    self::XML_PATH_CHANGE_EMAIL_TEMPLATE,
                    self::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
                    $storeId,
                    $email
                );
            }
        }         
        else 
        {    
            // $loginType = $this->_helper->checkEmailOrPhone($email);

            // if ($loginType == AttributeChecker::PHONE_TYPE) {
            //     $message = 'We have received a request to change the email or phone associated with your account.';
            //     $this->_smsSender->sendSMSEmail($email, $message); // I have changed here 
            // } else if ($loginType == AttributeChecker::EMAIL_TYPE) {
            //     $this->sendEmailTemplate(
            //         $customer,
            //         self::XML_PATH_CHANGE_EMAIL_TEMPLATE,
            //         self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            //         ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            //         $storeId,
            //         $email
            //     );
            // }
             
            $message = 'We have received a request to change the email or phone associated with your account.';
            $this->_smsSender->sendSMSEmail($email, $message);  // I have changed here     
        }
    }

    /**
     * Send email to customer when his password is reset
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function passwordReset(CustomerInterface $customer): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreIdChild($customer);
        }

        $customerEmailData = $this->getFullCustomerObjectChild($customer);

        $email = $customer->getEmail();
        $loginType = $this->_helper->checkEmailOrPhone($email);

        if ($loginType == AttributeChecker::PHONE_TYPE) {
            $message = 'We have received a request to change the password associated with your account.';
            $this->_smsSender->sendSMS($email, $message);
        } else if ($loginType == AttributeChecker::EMAIL_TYPE) {
            $this->sendEmailTemplate(
                $customer,
                self::XML_PATH_RESET_PASSWORD_TEMPLATE,
                self::XML_PATH_FORGOT_EMAIL_IDENTITY,
                ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
                $storeId
            );
        }
    }

    /**
     * Send corresponding email template
     *
     * @param CustomerInterface $customer
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string $email
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    private function sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ): void {
        $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);

        $this->customSendEmailTemplate(
            $customer,
            $templateId,
            $sender,
            $templateParams,
            $storeId,
            $email
        );
    }

    /**
     * Create an object with data merged from Customer and CustomerSecure
     *
     * @param CustomerInterface $customer
     * @return CustomerSecure
     */
    private function getFullCustomerObjectChild($customer): CustomerSecure
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, CustomerInterface::class);
        $mergedCustomerData->addData($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        return $mergedCustomerData;
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param CustomerInterface $customer
     * @param int|string|null $defaultStoreId
     * @return int
     */
    private function getWebsiteStoreIdChild($customer, $defaultStoreId = null): int
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Send email with new customer password
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function passwordReminder(CustomerInterface $customer): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreIdChild($customer);
        }

        $customerEmailData = $this->getFullCustomerObjectChild($customer);

        $email = $customerEmailData->getEmail();
        $loginType = $this->_helper->checkEmailOrPhone($email);

        if ($loginType == AttributeChecker::PHONE_TYPE) {
        } else if ($loginType == AttributeChecker::EMAIL_TYPE) {
            $this->sendEmailTemplate(
                $customer,
                self::XML_PATH_REMIND_EMAIL_TEMPLATE,
                self::XML_PATH_FORGOT_EMAIL_IDENTITY,
                ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
                $storeId
            );
        }
    }

    /**
     * Send email with reset password confirmation link
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function passwordResetConfirmation(CustomerInterface $customer): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreIdChild($customer);
        }

        $customerEmailData = $this->getFullCustomerObjectChild($customer);

        $email = $customerEmailData->getEmail();
        $loginType = $this->_helper->checkEmailOrPhone($email);

        if ($loginType == AttributeChecker::PHONE_TYPE) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/forgotpassword.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('===========================');
            $logger->info('email : ' . $email);
            $token = $customerEmailData->getRpToken();
            $logger->info($token);
            $storeUrl = $this->storeManager->getStore()->getBaseUrl();
            $message = sprintf(
                'There was recently a request to change the password for your account. Reset Password Link :  %scustomer/account/createPassword/?token=%s&_nosid=%s',
                $storeUrl,
                strval($token),
                strval(1)
            );
            $logger->info('message : ' . $message);
            $logger->info('==========================');
            $this->_smsSender->sendSMS($email, $message);
        } else if ($loginType == AttributeChecker::EMAIL_TYPE) {
            parent::passwordResetConfirmation($customer);
        }
    }

    /**
     * Send email with new account related information
     *
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int|null $storeId
     * @param string $sendemailStoreId
     * @return void
     * @throws LocalizedException
     */
    public function newAccount(
        CustomerInterface $customer,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = null,
        $sendemailStoreId = null
    ): void {
        $types = self::TEMPLATE_TYPES;

        if (!isset($types[$type])) {
            throw new LocalizedException(
                __('The transactional account email type is incorrect. Verify and try again.')
            );
        }

        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreIdChild($customer, $sendemailStoreId);
        }

        $store = $this->storeManager->getStore($customer->getStoreId());
        $otp = ctype_digit($customer->getConfirmation());

        $customerEmailData = $this->getFullCustomerObjectChild($customer);
        $email = $customerEmailData->getEmail();
        $loginType = $this->_helper->checkEmailOrPhone($email);
        $couponMessage = $this->couponHelper->getCouponMessage();
        if ($loginType == AttributeChecker::PHONE_TYPE) {
            // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/phoneconfirmation.log');
            // $logger = new \Zend_Log();
            // $logger->addWriter($writer);
            // $logger->info('===========================');
            // $logger->info($types[$type]);
            if ($types[$type] == self::XML_PATH_CONFIRM_EMAIL_TEMPLATE) {
                // $logger->info('phone : ' . $email);
                $customer = $customerEmailData;
                $store = $store;

                // $storeUrl = $this->storeManager->getStore()->getBaseUrl();
                // $message = sprintf(
                //     'You must confirm your %s phone number before you can sign in (link is only valid once): %scustomer/account/confirm/?id=%s&key=%s&back_url=%s&_nosid=%s',
                //     $email,
                //     $storeUrl,
                //     strval($customer->getId()),
                //     strval($customer->getConfirmation()),
                //     $backUrl,
                //     strval(1)
                // );
                $message = sprintf(
                    'Your Confirmation OTP is :  %s',
                    strval($customer->getConfirmation())
                );

                // $logger->info('message : ' . $message);

                $this->_smsSender->sendSMS($email, $message);
                if ($couponMessage) {
                    //$message .= ' ' . $couponMessage;
                    $this->_smsSender->sendSMS($email, $couponMessage);
                    // $logger->info('coupon message : ' . $couponMessage);
                }
            }
            // $logger->info('===========================');
        } else if ($loginType == AttributeChecker::EMAIL_TYPE) {
            if ($otp) {
                $this->customSendEmailTemplate(
                    $customer,
                    self::ACCOUNT_CONFIRM_OTP_EMAIL_TEMPLATE_MOBILE,
                    self::XML_PATH_REGISTER_EMAIL_IDENTITY,
                    ['customer' => $customerEmailData, 'back_url' => $backUrl, 'store' => $store, 'coupon' => $couponMessage],
                    $storeId,
                );
            } else {
                // parent::newAccount($customer, $type, $backUrl, $storeId, $sendemailStoreId);
                $this->sendEmailTemplate(
                    $customer,
                    $types[$type],
                    self::XML_PATH_REGISTER_EMAIL_IDENTITY,
                    ['customer' => $customerEmailData, 'back_url' => $backUrl, 'store' => $store, 'coupon' => $couponMessage],
                    $storeId
                );
            }
        }
    }

    /**
     * Custom Send corresponding email template
     *
     * @param CustomerInterface $customer
     * @param string $templateId email template id
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string $email
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    public function customSendEmailTemplate(
        $customer,
        $templateId,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {

        if ($email === null) {
            $email = $customer->getEmail();
        }

        /** @var array $from */
        $from = $this->senderResolver->resolve(
            $this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId),
            $storeId
        );

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($from)
            ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $this->emulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND);
        $transport->sendMessage();
        $this->emulation->stopEnvironmentEmulation();
    }
}
