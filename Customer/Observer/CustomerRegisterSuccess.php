<?php

namespace MIT\Customer\Observer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\Session;

class CustomerRegisterSuccess implements ObserverInterface {

    /**
     * @var AccountManagementInterface
     */
    private $accountManagementInterface;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        AccountManagementInterface $accountManagementInterface,
        Session $session
    )
    {
        $this->accountManagementInterface = $accountManagementInterface;
        $this->session = $session;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getEvent()->getCustomer();
        $confirmationStatus = $this->accountManagementInterface->getConfirmationStatus($customer->getId());
        if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
            $this->session->setCustomerConfirmedPopup($customer->getEmail());
        }
    }
}