<?php

namespace MIT\Checkout\Model\Api;

use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Checkout\Model\GuestPaymentInformationManagement as ModelGuestPaymentInformationManagement;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Model\OrderRepository;
use MIT\Checkout\Api\Data\CheckoutResultInterfaceFactory;
use MIT\Checkout\Api\GuestPaymentInformationInterface;

class GuestPaymentInformationManagement extends ModelGuestPaymentInformationManagement implements GuestPaymentInformationInterface
{
    /**
     * @var CheckoutResultInterfaceFactory
     */
    private $checkoutResultInterfaceFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @param \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\GuestCartManagementInterface $cartManagement
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param CheckoutResultInterfaceFactory $checkoutResultInterfaceFactory
     * @param OrderRepository $orderRepository
     * @param PaymentProcessingRateLimiterInterface|null $paymentsRateLimiter
     * @param PaymentSavingRateLimiterInterface|null $savingRateLimiter
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $cartManagement,
        \Magento\Checkout\Api\PaymentInformationManagementInterface $paymentInformationManagement,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        CheckoutResultInterfaceFactory $checkoutResultInterfaceFactory,
        OrderRepository $orderRepository,
        ?PaymentProcessingRateLimiterInterface $paymentsRateLimiter = null,
        ?PaymentSavingRateLimiterInterface $savingRateLimiter = null
    ) {

        parent::__construct(
            $billingAddressManagement,
            $paymentMethodManagement,
            $cartManagement,
            $paymentInformationManagement,
            $quoteIdMaskFactory,
            $cartRepository,
            $paymentsRateLimiter,
            $savingRateLimiter
        );
        $this->checkoutResultInterfaceFactory = $checkoutResultInterfaceFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritDoc
     */
    public function savePaymentInfoAndPlaceOrder($cartId, $email, PaymentInterface $paymentMethod, ?AddressInterface $billingAddress = null)
    {
        $orderId = parent::savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMethod, $billingAddress);
        $order = $this->orderRepository->get($orderId);
        $result = $this->checkoutResultInterfaceFactory->create()->setEntityId($orderId)->setIncrementId($order->getIncrementId())->setEmail($order->getCustomerEmail());
        return $result;
    }
}
