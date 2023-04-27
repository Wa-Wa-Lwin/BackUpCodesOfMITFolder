<?php

namespace MIT\Checkout\Model\Api;

use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Checkout\Model\PaymentDetailsFactory;
use Magento\Checkout\Model\PaymentInformationManagement as ModelPaymentInformationManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use MIT\Checkout\Api\Data\CheckoutResultInterfaceFactory;
use MIT\Checkout\Api\PaymentInformationInterface;
use Payment\Kbz\Helper\Data as Helper;

class PaymentInformationManagement extends ModelPaymentInformationManagement implements PaymentInformationInterface
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
     * @var PaymentMethodListInterface
     */
    private $paymentMethodListInterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param PaymentDetailsFactory $paymentDetailsFactory
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     * @param CheckoutResultInterfaceFactory $checkoutResultInterfaceFactory
     * @param OrderRepository $orderRepository
     * @param PaymentMethodListInterface $paymentMethodListInterface
     * @param StoreManagerInterface $storeManagerInterface
     * @param OrderFactory $orderFactory
     * @param Helper $helper
     * @param PaymentProcessingRateLimiterInterface|null $paymentRateLimiter
     * @param PaymentSavingRateLimiterInterface|null $saveRateLimiter
     * @param CartRepositoryInterface|null $cartRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        CheckoutResultInterfaceFactory $checkoutResultInterfaceFactory,
        OrderRepository $orderRepository,
        PaymentMethodListInterface $paymentMethodListInterface,
        StoreManagerInterface $storeManagerInterface,
        OrderFactory $orderFactory,
        Helper $helper,
        ?PaymentProcessingRateLimiterInterface $paymentRateLimiter = null,
        ?PaymentSavingRateLimiterInterface $saveRateLimiter = null,
        ?CartRepositoryInterface $cartRepository = null
    ) {

        parent::__construct(
            $billingAddressManagement,
            $paymentMethodManagement,
            $cartManagement,
            $paymentDetailsFactory,
            $cartTotalsRepository,
            $paymentRateLimiter,
            $saveRateLimiter,
            $cartRepository
        );
        $this->checkoutResultInterfaceFactory = $checkoutResultInterfaceFactory;
        $this->orderRepository = $orderRepository;
        $this->paymentMethodListInterface = $paymentMethodListInterface;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function savePaymentInfoAndPlaceOrder($cartId, PaymentInterface $paymentMethod, ?AddressInterface $billingAddress = null)
    {
        $orderId = parent::savePaymentInformationAndPlaceOrder($cartId, $paymentMethod, $billingAddress);
        $order = $this->orderRepository->get($orderId);
        $result = $this->checkoutResultInterfaceFactory->create()->setEntityId($orderId)->setIncrementId($order->getIncrementId())->setEmail($order->getCustomerEmail());
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodList($customerId, $orderIncrementId)
    {
        $order = $this->getOrderByIncrementIdAndCustomerId($customerId, $orderIncrementId);
        if (null !== $order->getIncrementId() && $order->getPayment()->getMethod() == 'kbzpayment' &&
        $order->getStatus() == 'pending_payment' && $order->getState() == 'new') {
            return $this->getActivePaymentMethodList();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function repayOrder($customerId, $orderIncrementId, $paymentMethod)
    {
        $result = $this->checkoutResultInterfaceFactory->create();
        $order = $this->getOrderByIncrementIdAndCustomerId($customerId, $orderIncrementId);
        if (null !== ($order->getIncrementId()) && isset($paymentMethod)) {
            $paymentMethodList = $this->getActivePaymentMethodList();
            foreach ($paymentMethodList as $payment) {
                if ($payment->getCode() == $paymentMethod) {
                    $order = $this->orderFactory->create();
                    $order->loadByIncrementId($orderIncrementId);
                    if ((array) $order) {
                        if ($order->getPayment()->getMethod() != 'kbzpayment') {
                            return $result;
                        }
                        if ($paymentMethod == 'kbzpayment') {
                            $order->setStatus('pending_payment');
                        } else {
                            $order->setStatus('pending');
                            $methodName = $this->helper->getConfig("payment/cashondelivery/title");
                            $order->getPayment()->setAdditionalInformation('method_title', $methodName);
                        }
                        $order->getPayment()->setMethod($paymentMethod);
                        $order->setState('new');
                        $this->orderRepository->save($order);
                        $result->setEntityId($order->getEntityId())->setIncrementId($order->getIncrementId())->setEmail($order->getCustomerEmail());
                    }
                }
            }
        }
        return $result;
    }

    /**
     * get active payment method list
     * @return \Magento\Payment\Api\Data\PaymentMethodInterface[]
     */
    private function getActivePaymentMethodList()
    {
        $activePaymentMethodList = $this->paymentMethodListInterface->getActiveList($this->storeManagerInterface->getStore()->getId());
        foreach ($activePaymentMethodList as $elementKey => $element) {
            $paymentTitle = $element->getTitle();
            if (in_array($paymentTitle, ['No Payment Information Required', 'PayPal Billing Agreement'])) {
                unset($activePaymentMethodList[$elementKey]);
            }
        }
        return $activePaymentMethodList;
    }

    /**
     * get order by increment id and customer id
     * @param int $customerId
     * @param string $orderIncrementId
     * @return \Magento\Sales\Model\Order
     */
    private function getOrderByIncrementIdAndCustomerId($customerId, $orderIncrementId)
    {
        try {
            $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
            if ($order->getCustomerId() == $customerId) {
                return $order;
            } else {
                return $this->orderFactory->create();
            }
        } catch(NoSuchEntityException $e) {
            return $this->orderFactory->create();
        }
    }
}
