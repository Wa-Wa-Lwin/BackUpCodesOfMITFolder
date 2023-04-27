<?php

namespace MIT\Customer\Model\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Security\Model\SecurityManager;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use MIT\Customer\Api\CustomAccountManagementInterface;
use MIT\Customer\Helper\AttributeChecker;
use MIT\Customer\Helper\CustomerHelper;
use MIT\Customer\Helper\SMSSender;
use MIT\Customer\Model\AccountConfigFactory;
use MIT\Customer\Model\CustomerEmailNotification;
use MIT\Product\Api\Data\StatusShowInterfaceFactory;
use Zend_Db_Expr;

class CustomAccountManagement extends AccountManagement implements CustomAccountManagementInterface
{
    const MAX_OTP_WRONG_COUNT = 3;
    const MAX_CONFIRM_EMAIL_SEND_COUNT = 3;

    /**Maximum count for reset password wrong and reset password resend otp count */
    const MAX_RESET_PASSWORD_WRONG = 3;
    const MAX_RESET_PASSWORD_RESEND_OTP_COUNT = 3;
    /**OPT NUMBER */
    const RANDOM_OTP_CODE_LENGTH = 6;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * @var AccountManagement
     */
    private $accountManagement;

    /**
     * @var \Magento\Customer\Model\AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $customerViewHelper;

    /**
     * @var \MIT\Customer\Helper\AttributeChecker
     */
    private $attributeChecker;

    /**
     * @var \Magento\Framework\Mail\Template\SenderResolverInterface
     */
    private $senderResolverInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var SMSSender
     */
    private $smsSender;

    /**
     * @var AccountConfigFactory
     */
    private $accountConfigFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StatusShowInterfaceFactory
     */
    protected $statusShowInterfaceFactory;

    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @var int
     */
    protected $passwordRequestEvent;

    /**
     * @var CustomerEmailNotification
     */
    protected $customerEmailNotification;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;

    /**
     * @var SessionCleanerInterface
     */
    private $sessionCleaner;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Math\Random $random,
        AccountManagement $accountManagement,
        \Magento\Customer\Model\AddressRegistry $addressRegistry,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Customer\Helper\View $customerViewHelper,
        \MIT\Customer\Helper\AttributeChecker $attributeChecker,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        SMSSender $smsSender,
        AccountConfigFactory $accountConfigFactory,
        ResourceConnection $resourceConnection,
        CollectionFactory $collectionFactory,
        StatusShowInterfaceFactory $statusShowInterfaceFactory,
        \Magento\Security\Model\SecurityManager $securityManager,
        $passwordRequestEvent = PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST,
        CustomerEmailNotification $customerEmailNotification,
        CustomerHelper $customerHelper,
        CredentialsValidator $credentialsValidator = null,
        SessionCleanerInterface $sessionCleaner = null,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolverInterface = null,
        Emulation $emulation = null
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->random = $random;
        $this->accountManagement = $accountManagement;
        $this->addressRegistry = $addressRegistry;
        $this->customerRegistry = $customerRegistry;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->customerViewHelper = $customerViewHelper;
        $this->attributeChecker = $attributeChecker;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->transportBuilder = $transportBuilder;
        $this->smsSender = $smsSender;
        $this->accountConfigFactory = $accountConfigFactory;
        $this->resourceConnection = $resourceConnection;
        $this->collectionFactory = $collectionFactory;
        $this->statusShowInterfaceFactory = $statusShowInterfaceFactory;
        $this->securityManager = $securityManager;
        $this->passwordRequestEvent = $passwordRequestEvent;
        $this->customerEmailNotification = $customerEmailNotification;
        $this->customerHelper = $customerHelper;
        $objectManager = ObjectManager::getInstance();
        $this->credentialsValidator = $credentialsValidator ?: $objectManager->get(CredentialsValidator::class);
        $this->senderResolverInterface = $senderResolverInterface ?? ObjectManager::getInstance()->get(SenderResolverInterface::class);
        $this->emulation = $emulation ?? ObjectManager::getInstance()->get(Emulation::class);
        $this->sessionCleaner = $sessionCleaner ?? $objectManager->get(SessionCleanerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function initiatePasswordReset($email, $template, $websiteId = null)
    {
        if ($this->customerHelper->validateEmailAndPhone($email)) {
            if ($websiteId === null) {
                $websiteId = $this->storeManagerInterface->getStore()->getWebsiteId();
            }

            if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $email)) {
                if (preg_match('/^09([0-9]{7,15})$/i', $email)) {
                    $email = substr_replace($email, '+959', 0, 2);
                } else if (preg_match('/^959([0-9]{7,15})$/i', $email)) {
                    $email = '+' . $email;
                }
            }

            try {
                $this->securityManager->performSecurityCheck(
                    $this->passwordRequestEvent,
                    $email
                );
            } catch (SecurityViolationException $e) {
                return $this->showStatus(false, [$e->getMessage()], []);
            }

            try {
                $customer = $this->customerRepositoryInterface->get($email, $websiteId);
            } catch (NoSuchEntityException $e) {
                return $this->showStatus(false, [$e->getMessage()], []);
            } catch (LocalizedException $e) {
                return $this->showStatus(false, [$e->getMessage()], []);
            }

            foreach ($customer->getAddresses() as $customerAddress) {
                $address = $this->addressRegistry->retrieve($customerAddress->getId());
                $address->setShouldIgnoreValidation(true);
            }

            $customerId = $customer->getId();
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('customer_entity');
            // get day
            $query = $connection->select()->from($table, '	rp_token_created_at')->where('entity_id = ? ', $customerId);
            $rpTokenCreatedAt = $connection->fetchOne($query);
            $rpTokenCreatedDay = date('Y-m-d', strtotime($rpTokenCreatedAt));

            //get mail count
            $query1 = $connection->select()->from($table, 'reset_password_mail_count')->where('entity_id = ? ', $customerId);
            $mailCount = $connection->fetchOne($query1);
            $currentDate = date("Y-m-d");
            if ($rpTokenCreatedDay == $currentDate && self::MAX_RESET_PASSWORD_RESEND_OTP_COUNT > $mailCount && $customer->getConfirmation() == null) {
                return $this->getInitiatePasswordResetData($customer, $customerId, $table, $template, $connection);
            } elseif ($currentDate > $rpTokenCreatedDay && $customer->getConfirmation() == null) {
                return $this->getInitiatePasswordResetData($customer, $customerId, $table, $template, $connection, true);
            } else {
                return $this->showStatus(false, ["Sorry!, You can't reset your password right now!"], []);
            }
            return $this->showStatus(false, ["Sorry!,Something was errors"], []);
        } else {
            return $this->showStatus(false, ['Your email or phone number is invalid.'], []);
        }
    }

    /**
     * @inheritdoc
     */
    public function getAccountConfig()
    {
        $accountConfig = $this->accountConfigFactory->create();
        $accountConfig->setStreetLines($this->scopeConfigInterface->getValue('customer/address/street_lines', ScopeInterface::SCOPE_STORE));
        $accountConfig->setPrefixShow($this->scopeConfigInterface->getValue('customer/address/prefix_show', ScopeInterface::SCOPE_STORE));
        $accountConfig->setPrefixOptions($this->scopeConfigInterface->getValue('customer/address/prefix_options', ScopeInterface::SCOPE_STORE));
        $accountConfig->setMiddlenameShow($this->scopeConfigInterface->getValue('customer/address/middlename_show', ScopeInterface::SCOPE_STORE));
        $accountConfig->setSuffixShow($this->scopeConfigInterface->getValue('customer/address/suffix_show', ScopeInterface::SCOPE_STORE));
        $accountConfig->setSuffixOptions($this->scopeConfigInterface->getValue('customer/address/suffix_options', ScopeInterface::SCOPE_STORE));
        $accountConfig->setDobShow($this->scopeConfigInterface->getValue('customer/address/dob_show', ScopeInterface::SCOPE_STORE));
        $accountConfig->setTaxvatShow($this->scopeConfigInterface->getValue('customer/address/taxvat_show', ScopeInterface::SCOPE_STORE));
        $accountConfig->setGenderShow($this->scopeConfigInterface->getValue('customer/address/gender_show', ScopeInterface::SCOPE_STORE));
        $accountConfig->setTelephoneShow($this->scopeConfigInterface->getValue('customer/address/telephone_show', ScopeInterface::SCOPE_STORE));
        $accountConfig->setCompanyShow($this->scopeConfigInterface->getValue('customer/address/company_show', ScopeInterface::SCOPE_STORE));
        $accountConfig->setFaxShow($this->scopeConfigInterface->getValue('customer/address/fax_show', ScopeInterface::SCOPE_STORE));
        return $accountConfig;
    }

    /**
     * Send email with reset password token
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function passwordResetConfirmation(CustomerInterface $customer, $isTemplateId): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreIdChild($customer);
        }
        $customerEmailData = $this->getFullCustomerObjectChild($customer);
        $email = $customerEmailData->getEmail();
        $loginType = $this->attributeChecker->checkEmailOrPhone($email);

        if ($loginType == AttributeChecker::PHONE_TYPE) {
            if ($isTemplateId) {
                $token = $customerEmailData->getRpToken();
                $message = sprintf(
                    'There was recently a request to change the password for your account. Reset Password Token is :  %s',
                    strval($token)
                );
                $this->smsSender->sendSMS($email, $message);
            }
        } else if ($loginType == AttributeChecker::EMAIL_TYPE) {
            if ($isTemplateId) {
                /** @var array $from */
                $from = $this->senderResolverInterface->resolve(
                    $this->scopeConfigInterface->getValue('customer/password/forgot_email_identity', ScopeInterface::SCOPE_STORE, $storeId),
                    $storeId
                );

                $transport = $this->transportBuilder->setTemplateIdentifier('reset_password_email_template_otp_mobile')
                    ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
                    ->setTemplateVars(['customer' => $customerEmailData, 'store' => $this->storeManagerInterface->getStore($storeId)])
                    ->setFrom($from)
                    ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
                    ->getTransport();

                $this->emulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND);
                $transport->sendMessage();
                $this->emulation->stopEnvironmentEmulation();
            } else {

                $templateId = $this->scopeConfigInterface->getValue(CustomerEmailNotification::XML_PATH_FORGOT_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $storeId);
                $this->customerEmailNotification->customSendEmailTemplate(
                    $customer,
                    $templateId,
                    CustomerEmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ['customer' => $customerEmailData, 'store' => $this->storeManagerInterface->getStore($storeId)],
                    $storeId
                );
            }
        }
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
        $customerData = $this->dataObjectProcessor
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
            $storeIds = $this->storeManagerInterface->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Handle not supported template
     *
     * @param string $template
     * @throws InputException
     */
    private function handleUnknownTemplate($template)
    {
        throw new InputException(
            __(
                'Invalid value of "%value" provided for the %fieldName field. '
                    . 'Possible values: %template1 or %template2.',
                [
                    'value' => $template,
                    'fieldName' => 'template',
                    'template1' => AccountManagement::EMAIL_REMINDER,
                    'template2' => AccountManagement::EMAIL_RESET,
                ]
            )
        );
    }

    /**
     * activate customer account by otp code
     * @param string $email
     * @param string $otpCode
     * @return \MIT\Product\Api\Data\StatusShowInterface
     */
    public function activateAccountByOtpCode($email, $otpCode)
    {
        $status = false;
        $successMessage = [];
        $errorMessage = [];
        try {
            if ($this->customerHelper->validateEmailAndPhone($email)) {
                $email = $this->customerHelper->normalizePhoneNumber($email);
                $customer = $this->customerRepositoryInterface->get($email);
                $id = $customer->getId();

                $connection = $this->resourceConnection->getConnection();
                $tableName = $connection->getTableName('customer_entity');
                $currentDate = date("Y-m-d H:i:s");
                $updatedDate = $customer->getUpdatedAt();

                $sql = $connection->select()->from($tableName, ['otp_wrong_count', 'otp_expired_date'])->where('entity_id = ? ', $id);
                $data = $connection->fetchRow($sql);
                $wrongCount = $data['otp_wrong_count'];
                $expiredDate = $data['otp_expired_date'];

                if ($customer->getConfirmation()) {
                    if ($currentDate < $expiredDate && $currentDate > $updatedDate) {
                        if ($wrongCount < self::MAX_OTP_WRONG_COUNT) {
                            if ($customer->getConfirmation() == $otpCode) {
                                $customer->setConfirmation(null);
                                $connection->update(
                                    $tableName,
                                    ['otp_wrong_count' => 0, 'otp_expired_date' => null, 'confirm_mail_send_count' => 0],
                                    ['entity_id = ?' => $id]
                                );

                                $this->customerRepositoryInterface->save($customer);
                                $this->getEmailNotification()->newAccount(
                                    $customer,
                                    'confirmed',
                                    '',
                                    $this->storeManagerInterface->getStore()->getId()
                                );
                                $status = true;
                                $successMessage[] = 'Your account is successfully verified';
                            } else {
                                $wrongCount++;

                                $connection->update(
                                    $tableName,
                                    ['otp_wrong_count' => $wrongCount],
                                    ['entity_id = ?' => $id]
                                );
                                $errorMessage[] = 'Your OTP code is invalid. Verify the code and try again.';
                            }
                        } else {
                            $errorMessage[] = 'Your OTP wrong count reached limit. Please try again later.';
                        }
                    } else {
                        $errorMessage[] = 'Your OTP code is expired.';
                    }
                } else {
                    $errorMessage[] = 'The account is already active.';
                }
            } else {
                $errorMessage[] = "Your email or phone number is invalid.";
            }
        } catch (NoSuchEntityException $e) {
            $errorMessage[] = "Your email or phone number is invalid.";
            return $this->showStatus($status, $successMessage, $errorMessage);
        } catch (LocalizedException $e) {
            $errorMessage[] = "Your email or phone number is invalid.";
            return $this->showStatus($status, $successMessage, $errorMessage);
        }

        return $this->showStatus($status, $successMessage, $errorMessage);
    }

    /**
     * send confirmation email with otp
     * @param string $email
     * @param int $websiteId
     * @return \MIT\Product\Api\Data\StatusShowInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendConfirmEmailWithOtp($email, $websiteId = null)
    {
        $status = false;
        $successMessage = [];
        $errorMessage = [];
        try {
            if ($this->customerHelper->validateEmailAndPhone($email)) {
                $email = $this->customerHelper->normalizePhoneNumber($email);
                if ($websiteId === null) {
                    $websiteId = $this->storeManagerInterface->getStore()->getWebsiteId();
                }
                $customer = $this->customerRepositoryInterface->get($email, $websiteId);

                foreach ($customer->getAddresses() as $customerAddress) {
                    $address = $this->addressRegistry->retrieve($customerAddress->getId());
                    $address->setShouldIgnoreValidation(true);
                }

                $id = $customer->getId();
                $currentDate = date('Y-m-d H:i:s');
                $addedDate = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime($currentDate)));
                $connection = $this->resourceConnection->getConnection();
                $tableName = $connection->getTableName('customer_entity');
                $sql = $connection->select()->from($tableName, ['confirm_mail_send_count', 'confirmation'])->where('entity_id = ? ', $id);
                $data = $connection->fetchRow($sql);
                $mailSendCount = $data['confirm_mail_send_count'];
                $otp = $data['confirmation'];

                try {
                    if ($mailSendCount < self::MAX_CONFIRM_EMAIL_SEND_COUNT) {
                        if ($otp != null) {
                            $newOtp = $this->customerHelper->generateRandomOtpCode(self::RANDOM_OTP_CODE_LENGTH);
                            $mailSendCount++;
                            $connection->update(
                                $tableName,
                                [
                                    'confirmation' => $newOtp, 'otp_wrong_count' => 0, 'otp_expired_date' => $addedDate,
                                    'confirm_mail_send_count' => "$mailSendCount", 'updated_at' => $currentDate
                                ],
                                ['entity_id = ?' => $id]
                            );

                            $customer->setConfirmation($newOtp);
                            $this->AccountConfirmOtp($customer);
                            $status = true;
                            $successMessage[] = 'OTP is sent to your registered email address or phone number';
                        } else {
                            $errorMessage[] = 'Your account is already comfirmed.';
                        }
                    } else {
                        $errorMessage[] = 'Resend times reached limit!';
                    }
                } catch (MailException $e) {
                    // If we are not able to send a confirm email, this should be ignored
                    // $this->logger->critical($e);
                }
            } else {
                $errorMessage[] = "Your email or phone number is invalid.";
            }
        } catch (NoSuchEntityException $e) {
            $errorMessage[] = "Your email or phone number is invalid.";
            return $this->showStatus($status, $successMessage, $errorMessage);
        } catch (LocalizedException $e) {
            $errorMessage[] = "Your email or phone number is invalid.";
            return $this->showStatus($status, $successMessage, $errorMessage);
        }

        return $this->showStatus($status, $successMessage, $errorMessage);
    }

    /**
     * Send email template with otp
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function AccountConfirmOtp(CustomerInterface $customer): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreIdChild($customer);
        }
        $customerEmailData = $this->getFullCustomerObjectChild($customer);
        $email = $customerEmailData->getEmail();
        $loginType = $this->attributeChecker->checkEmailOrPhone($email);

        if ($loginType == AttributeChecker::PHONE_TYPE) {
            $confirmOtp = $customer->getConfirmation();
            $message = sprintf(
                'Your Confirmation OTP is :  %s',
                strval($confirmOtp)
            );
            $this->smsSender->sendSMS($email, $message);
        } else if ($loginType == AttributeChecker::EMAIL_TYPE) {
            $this->customerEmailNotification->customSendEmailTemplate(
                $customer,
                CustomerEmailNotification::ACCOUNT_CONFIRM_OTP_EMAIL_TEMPLATE_MOBILE,
                CustomerEmailNotification::XML_PATH_REGISTER_EMAIL_IDENTITY,
                ['customer' => $customerEmailData, 'store' => $this->storeManagerInterface->getStore($storeId)],
                $storeId,
            );
        }
    }

    /**
     * customize reset password for mobile
     *
     * @param string $email
     * @param int $websiteId
     * @return \MIT\Product\Api\Data\StatusShowInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resetPassword($email, $resetToken, $newPassword)
    {
        if (!$email) {
            $customer = $this->getByToken->execute($resetToken);
            $email = $customer->getEmail();
        } else {
            try {
                $customer = $this->customerRepositoryInterface->get($email);
            } catch (NoSuchEntityException $e) {
                return $this->showStatus(false, [$e->getMessage()], []);
            } catch (LocalizedException $e) {
                return $this->showStatus(false, [$e->getMessage()], []);
            }
        }

        // No need to validate customer and customer address while saving customer reset password token
        $this->disableAddressValidation($customer);
        $this->setIgnoreValidationFlag($customer);
        $customerId = $customer->getId();
        //Validate Token and new password strength
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('customer_entity');
        $query = $connection->select()->from($table, 'reset_password_wrong_count')->where('entity_id = ? ', $customerId);
        $result1 = $connection->fetchOne($query);

        if (self::MAX_RESET_PASSWORD_WRONG <= $result1) {
            return $this->showStatus(false, ['Your password opt code wrong count is over limit. Reset and try again.'], []);
        } else {
            $validateData = $this->validateResetPasswordToken($customer->getId(), $resetToken);
            if (!$validateData->getStatus()) {
                return $validateData;
            }
        }
        $this->credentialsValidator->checkPasswordDifferentFromEmail(
            $email,
            $newPassword
        );
        $this->accountManagement->checkPasswordStrength($newPassword);
        //Update secure data
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $customerSecure->setPasswordHash($this->accountManagement->createPasswordHash($newPassword));
        $this->sessionCleaner->clearFor((int) $customer->getId());
        $this->customerRepositoryInterface->save($customer);

        return $this->showStatus(true, [], ['Success']);
    }

    /**
     * Disable Customer Address Validation
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }

    /**
     * Set ignore_validation_flag for reset password flow to skip unnecessary address and customer validation
     *
     * @param Customer $customer
     * @return void
     */
    private function setIgnoreValidationFlag($customer)
    {
        $customer->setData('ignore_validation_flag', true);
    }

    /**
     * Validate the Reset Password Token for a customer.
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     *
     * @return \MIT\Product\Api\Data\StatusShowInterface
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function validateResetPasswordToken($customerId, $resetPasswordLinkToken)
    {
        if ($customerId !== null && $customerId <= 0) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $customerId, 'fieldName' => 'customerId']
                )
            );
        }

        if ($customerId === null) {
            //Looking for the customer.
            $customerId = $this->getByToken
                ->execute($resetPasswordLinkToken)
                ->getId();
        }
        if (!is_string($resetPasswordLinkToken) || empty($resetPasswordLinkToken)) {
            $params = ['fieldName' => 'resetPasswordLinkToken'];
            throw new InputException(__('"%fieldName" is required. Enter and try again.', $params));
        }
        $customerSecureData = $this->customerRegistry->retrieveSecureData($customerId);
        $rpToken = $customerSecureData->getRpToken();
        $rpTokenCreatedAt = $customerSecureData->getRpTokenCreatedAt();

        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('customer_entity');
        $query = $connection->select()->from($table, 'reset_password_wrong_count')->where('entity_id = ? ', $customerId);
        $result1 = $connection->fetchOne($query);

        $status = true;
        $successMessage = ['Success Message'];
        $errorMessage = [];

        if (!Security::compareStrings($rpToken, $resetPasswordLinkToken)) {

            $connection->update(
                $table,
                ['reset_password_wrong_count' => new Zend_Db_Expr('reset_password_wrong_count+1')],
                ['entity_id = ?' => $customerId]
            );

            $status = false;
            $errorMessage[] = 'The password token is mismatched. Reset and try again.';
            $successMessage = [];
        } elseif ($this->accountManagement->isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
            $status = false;
            $errorMessage[] = 'The password token is mismatched. Reset and try again.';
            $successMessage = [];
        }
        return $this->showStatus($status, $errorMessage, $successMessage);
    }

    /**
     * @inheritdoc
     */
    public function resetPasswordSendOtp($email, $websiteId = null)
    {
        if ($this->customerHelper->validateEmailAndPhone($email)) {
            if ($websiteId === null) {
                $websiteId = $this->storeManagerInterface->getStore()->getWebsiteId();
            }

            if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $email)) {
                if (preg_match('/^09([0-9]{7,15})$/i', $email)) {
                    $email = substr_replace($email, '+959', 0, 2);
                } else if (preg_match('/^959([0-9]{7,15})$/i', $email)) {
                    $email = '+' . $email;
                }
            }
            try {
                $this->securityManager->performSecurityCheck(
                    $this->passwordRequestEvent,
                    $email
                );
            } catch (SecurityViolationException $e) {
                return $this->showStatus(false, [$e->getMessage()], []);
            }
            try {
                $customer = $this->customerRepositoryInterface->get($email, $websiteId);
            } catch (NoSuchEntityException $e) {
                return $this->showStatus(false, [$e->getMessage()], []);
            } catch (LocalizedException $e) {
                return $this->showStatus(false, [$e->getMessage()], []);
            }
            $customerId = $customer->getId();
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('customer_entity');
            $query = $connection->select()->from($table, 'reset_password_mail_count')->where('entity_id = ? ', $customerId);

            $result = $connection->fetchOne($query);
            foreach ($customer->getAddresses() as $customerAddress) {
                $address = $this->addressRegistry->retrieve($customerAddress->getId());
                $address->setShouldIgnoreValidation(true);
            }

            try {
                $query = $connection->select()->from($table, '	rp_token_created_at')->where('entity_id = ? ', $customerId);
                $rpTokenCreatedAt = $connection->fetchOne($query);
                $rpTokenCreatedDay = date('Y-m-d', strtotime($rpTokenCreatedAt));
                $currentDate = date("Y-m-d");
                $currentDay = date('Y-m-d', strtotime($currentDate));
                if (self::MAX_RESET_PASSWORD_RESEND_OTP_COUNT > $result && $customer->getConfirmation() == null && $currentDay == $rpTokenCreatedDay) {

                    $updateQuery = $connection->update(
                        $table,
                        ['reset_password_mail_count' => ++$result, 'rp_token_created_at' => '$currentDate'],
                        ['entity_id = ?' => $customerId]
                    );
                    $newPasswordToken = $this->customerHelper->generateRandomOtpCode(self::RANDOM_OTP_CODE_LENGTH);
                    $this->accountManagement->changeResetPasswordLinkToken($customer, $newPasswordToken);
                    $this->passwordResetConfirmation($customer, true);
                    return $this->showStatus(true, [], ['Success']);
                } else if ($customer->getConfirmation() != null) {
                    return $this->showStatus(false, ["Sorry!, You can't reset your password right now!"], []);
                } else {
                    return $this->showStatus(false, ['Your password resend opt code  count is over limit. Reset and try again.'], []);
                }
            } catch (MailException $e) {
                return $this->showStatus(false, [$e->getMessage()], []);
            }
            return $this->showStatus(false, ["Sorry!,Something was errors"], []);
        } else {
            return $this->showStatus(false, ['Your email or phone number is invalid.'], []);
        }
    }

    /**
     * result status show
     * @param array $errorMessage
     * @param array $successMessage
     * @param bool $status
     * @return \MIT\Product\Api\Data\StatusShowInterface
     */
    public function showStatus($status, array $errorMessage, array $successMessage)
    {
        $resultFactory = $this->statusShowInterfaceFactory->create();
        $resultFactory->setStatus($status);
        $resultFactory->setSuccessMessage($successMessage);
        $resultFactory->setErrorMessage($errorMessage);
        return $resultFactory;
    }


    /**
     * @inheritdoc
     */
    private  function getInitiatePasswordResetData($customer, $customerId, $table, $template, $connection, $isNew = false)
    {
        $newPasswordToken = $this->customerHelper->generateRandomOtpCode(self::RANDOM_OTP_CODE_LENGTH);
        if ($isNew) {
            $connection->update(
                $table,
                ['reset_password_wrong_count' => 0, 'reset_password_mail_count' => 1],
                ['entity_id = ?' => $customerId]
            );
        } else {
            $connection->update(
                $table,
                ['reset_password_mail_count' => new Zend_Db_Expr('reset_password_mail_count+1')],
                ['entity_id = ?' => $customerId]
            );
        }
        $this->accountManagement->changeResetPasswordLinkToken($customer, $newPasswordToken);
        try {
            switch ($template) {
                case AccountManagement::EMAIL_REMINDER:
                    $this->getEmailNotification()->passwordReminder($customer);
                    break;
                case AccountManagement::EMAIL_RESET:
                    $this->passwordResetConfirmation($customer, true);
                    break;
                default:
                    $this->handleUnknownTemplate($template);
                    break;
            }
            return $this->showStatus(true, [], ['Success']);
        } catch (MailException $e) {
            return $this->showStatus(false, [$e->getMessage()], []);
        }
    }
}
