<?php

namespace MIT\Customer\Helper;

use Magento\Framework\App\Helper\Context;

class SMSTemplate extends \Magento\Framework\App\Helper\AbstractHelper
{

    const CHECK_STATUS_MESSAGE = 'You can check the status of your order by logging into your account. ';
    const THANK_YOU_MESSAGE = 'Thank you for your order. ';

    const TEMPLATE_ARR = array(
        'sales_email_order_template' =>
        self::THANK_YOU_MESSAGE . self::CHECK_STATUS_MESSAGE . 'Order No: %s, Total: %s, Date Ordered: %s',
        'sales_email_order_guest_template' =>
        self::THANK_YOU_MESSAGE . self::CHECK_STATUS_MESSAGE . 'Order No: %s, Total: %s, Date Ordered: %s',
        'sales_email_order_comment_template' =>
        'Your order #%s has been updated with a status of %s.' . self::CHECK_STATUS_MESSAGE . ' %s',
        'sales_email_order_comment_guest_template' =>
        'Your order #%s has been updated with a status of %s.' . self::CHECK_STATUS_MESSAGE . ' %s',
        'sales_email_invoice_template' =>
        self::THANK_YOU_MESSAGE . self::CHECK_STATUS_MESSAGE . 'Your invoice #%s for Order #%s',
        'sales_email_invoice_guest_template' =>
        self::THANK_YOU_MESSAGE . self::CHECK_STATUS_MESSAGE . 'Your invoice #%s for Order #%s',
        'sales_email_invoice_comment_template' =>
        'Your order #%s has been updated with a status of %s.' . self::CHECK_STATUS_MESSAGE . ' %s',
        'sales_email_invoice_comment_guest_template' =>
        'Your order #%s has been updated with a status of %s.' . self::CHECK_STATUS_MESSAGE . ' %s',
        'sales_email_creditmemo_template' =>
        self::THANK_YOU_MESSAGE . self::CHECK_STATUS_MESSAGE . 'Your Credit Memo #%s for Order #%s',
        'sales_email_creditmemo_guest_template' =>
        self::THANK_YOU_MESSAGE . self::CHECK_STATUS_MESSAGE . 'Your Credit Memo #%s for Order #%s',
        'sales_email_creditmemo_comment_template' =>
        'Your order #%s has been updated with a status of %s.' . self::CHECK_STATUS_MESSAGE . ' %s',
        'sales_email_creditmemo_comment_guest_template' =>
        'Your order #%s has been updated with a status of %s.' . self::CHECK_STATUS_MESSAGE . ' %s',
        'sales_email_shipment_template' =>
        self::THANK_YOU_MESSAGE . self::CHECK_STATUS_MESSAGE . 'Your Order has shipped. Your Shipment #%s for Order #%s',
        'sales_email_shipment_guest_template' =>
        self::THANK_YOU_MESSAGE . self::CHECK_STATUS_MESSAGE . 'Your Order has shipped. Your Shipment #%s for Order #%s',
        'sales_email_shipment_comment_template' =>
        'Your order #%s has been updated with a status of %s.' . self::CHECK_STATUS_MESSAGE . ' %s',
        'sales_email_shipment_comment_guest_template' =>
        'Your order #%s has been updated with a status of %s.' . self::CHECK_STATUS_MESSAGE . ' %s',
        'sales_email_pending_guest_template' =>
        self::THANK_YOU_MESSAGE . self::CHECK_STATUS_MESSAGE . 'Your Order #%s status is Pending',
        'sales_email_pending_template' =>
        self::THANK_YOU_MESSAGE . self::CHECK_STATUS_MESSAGE . 'Your Order #%s status is Pending'
    );

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function getMessage($templateId, $orderNo, $paramOne = '', $paramTwo = '', $paramThree = '')
    {
        return sprintf(self::TEMPLATE_ARR[$templateId], $orderNo, $paramOne, $paramTwo, $paramThree);
    }
}
