<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MIT\Customer\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use MIT\Customer\Helper\AttributeChecker;
use MIT\Customer\Helper\SMSSender;
use MIT\Customer\Helper\SMSTemplate;

/**
 * Sender Builder
 */
class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    /**
     * @var Template
     */
    protected $templateContainer;

    /**
     * @var IdentityInterface
     */
    protected $identityContainer;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    protected $_smsSender;
    protected $_checker;
    protected $_smsTemplate;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        SMSSender $smsSender,
        AttributeChecker $checker,
        SMSTemplate $smsTemplate
    ) {
        parent::__construct($templateContainer, $identityContainer, $transportBuilder);
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->transportBuilder = $transportBuilder;
        $this->_smsSender = $smsSender;
        $this->_checker = $checker;
        $this->_smsTemplate = $smsTemplate;
    }


    /**
     * Prepare and send email message
     *
     * @return void
     */
    public function send()
    {
        $type = $this->_checker->checkEmailOrPhone($this->identityContainer->getCustomerEmail());
        if ($type == AttributeChecker::PHONE_TYPE) {
            $customerName = $this->identityContainer->getCustomerName();

            $order = $this->templateContainer->getTemplateVars()['order'];
            $store = $this->templateContainer->getTemplateVars()['store'];
            $storeName = $store->getFrontendName();


            $orderIncrementId = $order->getIncrementId();
            $total = $order->getGrandTotal();
            $currencyCode = $order->getOrderCurrencyCode();
            $orderingDate = $order->getCreatedAt();

            $message = '';
            $templateId = strval($this->templateContainer->getTemplateId());
            if (str_contains($templateId, 'email_order') && !str_contains($templateId, 'email_order_comment')) {
                $message = $this->_smsTemplate->getMessage($templateId, $orderIncrementId, strval($total) . ' ' . strval($currencyCode), strval(date("Y-m-d", strtotime($orderingDate))));
            } else if (str_contains($templateId, 'email_shipment') && !str_contains($templateId, 'email_shipment_comment')) {
                $shipmentId = $this->templateContainer->getTemplateVars()['shipment']->getIncrementId();
                $message = $this->_smsTemplate->getMessage($templateId, $shipmentId, $orderIncrementId);
            } else if (str_contains($templateId, 'email_invoice') && !str_contains($templateId, 'email_invoice_comment')) {
                $invoiceId = $this->templateContainer->getTemplateVars()['invoice']->getIncrementId();
                $message = $this->_smsTemplate->getMessage($templateId, $invoiceId, $orderIncrementId);
            } else if (str_contains($templateId, 'email_creditmemo') && !str_contains($templateId, 'email_creditmemo_comment')) {
                $creditmemoId = $this->templateContainer->getTemplateVars()['creditmemo']->getIncrementId();
                $message = $this->_smsTemplate->getMessage($templateId, $creditmemoId, $orderIncrementId);
            } else if (str_contains($templateId, 'pending')) {
                $message = $this->_smsTemplate->getMessage($templateId, $orderIncrementId);
            } else {
                $comment = strval($this->templateContainer->getTemplateVars()['comment']) ? 'comment : ' . strval($this->templateContainer->getTemplateVars()['comment']) : '';
                $message = $this->_smsTemplate->getMessage($templateId, $orderIncrementId, $order->getFrontendStatusLabel(), $comment);
            }
            $this->_smsSender->sendSMS($this->identityContainer->getCustomerEmail(), $message);
        } else if ($type == AttributeChecker::EMAIL_TYPE) {
            $this->configureEmailTemplate();

            $this->transportBuilder->addTo(
                $this->identityContainer->getCustomerEmail(),
                $this->identityContainer->getCustomerName()
            );

            $copyTo = $this->identityContainer->getEmailCopyTo();

            if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
                foreach ($copyTo as $email) {
                    $this->transportBuilder->addBcc($email);
                }
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        }
    }
}
