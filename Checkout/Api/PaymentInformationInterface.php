<?php

namespace MIT\Checkout\Api;

use MIT\Checkout\Api\Data\CheckoutResultInterface;

interface PaymentInformationInterface
{
    /**
     * Set payment information and place order for a specified cart.
     *
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return CheckoutResultInterface
     */
    public function savePaymentInfoAndPlaceOrder(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    );

    /**
     * get available payment method list
     * @param int $customerId
     * @param string $orderId
     * @return \Magento\Payment\Api\Data\PaymentMethodInterface[]
     */
    public function getPaymentMethodList($customerId, $orderId);

    /**
     * update payment method for order
     * @param int $customerId
     * @param string $orderId
     * @param string $paymentMethod
     * @return \MIT\Checkout\Api\Data\CheckoutResultInterface
     */
    public function repayOrder($customerId, $orderId, $paymentMethod);
}
