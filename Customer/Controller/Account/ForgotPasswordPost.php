<?php

namespace MIT\Customer\Controller\Account;

use Magento\Customer\Controller\Account\ForgotPasswordPost as AccountForgotPasswordPost;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;

class ForgotPasswordPost extends AccountForgotPasswordPost
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper
    ) {
        parent::__construct($context, $customerSession, $customerAccountManagement, $escaper);
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper = $escaper;
    }

    /**
     * Forgot customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {
            if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class) && !(preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $email))) {
                $this->session->setForgottenEmail($email);
                $this->messageManager->addErrorMessage(
                    __('The email address or phone number is incorrect. Verify the email address or phone number and try again.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            } else {
                if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $email)) {
                    if (preg_match('/^09([0-9]{7,15})$/i', $email)) {
                        $email = substr_replace($email, '+959', 0, 2);
                    } else if (preg_match('/^959([0-9]{7,15})$/i', $email)) {
                        $email = '+' . $email;
                    }
                }
            }

            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
            } catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath('*/*/forgotpassword');
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We\'re unable to send the password reset email.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }
            $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
            return $resultRedirect->setPath('*/*/');
        } else {
            $this->messageManager->addErrorMessage(__('Please enter your email or phone number.'));
            return $resultRedirect->setPath('*/*/forgotpassword');
        }
    }

    /**
     * Retrieve success message
     *
     * @param string $email
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($email)
    {
        $defaultType = 'email';
        if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $email)) {
            $defaultType = 'sms';
        }
        return __(
            'If there is an account associated with %1 you will receive an ' . $defaultType . ' with a link to reset your password.',
            $this->escaper->escapeHtml($email)
        );
    }
}
