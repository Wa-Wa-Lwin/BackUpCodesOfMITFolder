<?php

namespace MIT\Payatstore\Observer;

class SaveCustomFieldsInOrder implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        if ($order->getShippingMethod() == 'payatstore_payatstore') {
            $order->setData('pay_at_store_id', $quote->getPayAtStoreId());
        }

        return $this;
    }
}
