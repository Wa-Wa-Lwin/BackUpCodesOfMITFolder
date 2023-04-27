<?php

namespace MIT\Customer\Controller\Account;

use Magento\Customer\Controller\Account\LoginPost as AccountLoginPost;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;

class LoginPost extends AccountLoginPost
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        parent::__construct(
            $context,
            $customerSession,
            $customerAccountManagement,
            $customerHelperData,
            $formKeyValidator,
            $accountRedirect
        );
    }


    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated 100.0.10
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $login['username'])) {
                        if (preg_match('/^09([0-9]{7,15})$/i', $login['username'])) {
                            $login['username'] = substr_replace($login['username'], '+959', 0, 2);
                        } else if (preg_match('/^959([0-9]{7,15})$/i', $login['username'])) {
                            $login['username'] = '+' . $login['username'];
                        }
                    }
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }
                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                        // URL is checked to be internal in $this->_redirect->success()
                        $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                        return $resultRedirect;
                    }
                } catch (EmailNotConfirmedException $e) {
                    $this->messageManager->addComplexErrorMessage(
                        'confirmAccountErrorMessage',
                        ['url' => $this->customerUrl->getEmailConfirmationUrl($login['username'])]
                    );
                    $this->session->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                            . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password

                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/login.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info($e->getMessage());
                    $this->messageManager->addErrorMessage(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addErrorMessage($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('A login and a password are required.'));
            }
        }

        return $this->accountRedirect->getRedirect();
    }
}
