<?php

namespace MIT\Customer\Plugin\Block\Adminhtml\Order;

class View
{
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        $methodTitle = $view->getOrder()->getPayment()->getMethod();
        if (!$view->getOrder()->isCanceled() && $view->getOrder()->getState() == 'new' && $view->getOrder()->getStatus() == 'pending_payment' && $methodTitle == 'kbzpayment') {
            $message = 'Are you sure you want to send a pending mail or sms to customer?';


            $view->addButton(
                'order_pending',
                [
                    'label' => __('Send Pending'),
                    'class' => 'send-pending',
                    'onclick' => "confirmSetLocation('{$message}', '{$view->getUrl('sales/*/email', array('status' => 'pending'))}')"
                ]
            );

            $message = 'Are you sure you want to check the transaction for current order?';
            
            $view->addButton(
                'check_transaction',
                [
                    'label' => __('Check Transaction'),
                    'class' => 'check-transaction',
                    'onclick' => "confirmSetLocation('{$message}', '{$view->getUrl('sales/*/email', array('status' => 'check_transaction'))}')"
                ]
            );
        }
    }
}

