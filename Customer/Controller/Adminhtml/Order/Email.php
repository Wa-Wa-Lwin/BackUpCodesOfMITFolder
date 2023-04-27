<?php

namespace MIT\Customer\Controller\Adminhtml\Order;

use Magento\Sales\Controller\Adminhtml\Order\Email as OrderEmail;
use Magento\Backend\App\Action;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use MIT\Customer\Model\Order\Email\Sender\OrderPendingSender;
use Payment\PaySuccess\Helper\TransactionHelper;
use Psr\Log\LoggerInterface;

class Email extends OrderEmail
{
    const PENDING_MAIL_TEMPLATE = 'sales_email_pending_template';
    const PENDING_MAIL_GUEST_TEMPLATE = 'sales_email_pending_guest_template';
    protected $senderBuilderFactory;
    protected $templateContainer;
    protected $identityContainer;
    protected $orderFactory;
    protected $orderPendingSender;
    public $objectManager;
    

    /**
     * @var TransactionHelper
     */
    private $transactionHelper;


    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        OrderPendingSender $orderPendingSender,
        TransactionHelper $transactionHelper
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
        $this->_request = $context->getRequest();
        $this->orderFactory = $orderFactory;
        $this->orderPendingSender = $orderPendingSender;
        $this->transactionHelper = $transactionHelper;
    }

    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {

                $status = $this->_request->getParam('status');
                if ($status == 'pending') {
                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/pendingorder.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info('type :: ' . $status);
                    // $this->prepareTemplate($order);

                    $orderData = $this->orderFactory->create();
                    $orderData->loadByIncrementId($order->getIncrementId());
                    $this->orderPendingSender->send($order);
                    $this->messageManager->addSuccessMessage(__('You sent the order pending email.'));
                } else if ($status == 'check_transaction') {
                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/gotit.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info('testing');
                    $this->transactionHelper->checkOnlinePaymentStatus($order->getEntityId());
                    $this->messageManager->addSuccessMessage(__('You checked the transaction status.'));
                }else {
                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/pendingorder.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info('type :: ' . $status);
                    $this->orderManagement->notify($order->getEntityId());
                    $this->messageManager->addSuccessMessage(__('You sent the order email.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('We can\'t send the email order right now.'));
                $this->logger->critical($e);
            }
            return $this->resultRedirectFactory->create()->setPath(
                'sales/order/view',
                [
                    'order_id' => $order->getEntityId()
                ]
            );
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }
}
