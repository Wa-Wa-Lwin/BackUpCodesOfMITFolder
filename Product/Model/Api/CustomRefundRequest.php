<?php

namespace MIT\Product\Model\Api;

use Bss\RefundRequest\Helper\Email;
use Bss\RefundRequest\Model\RequestFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\OrderFactory;
use MIT\Product\Api\CustomRefundRequestInterface;

class CustomRefundRequest implements CustomRefundRequestInterface
{
    /**
     * @var \Bss\RefundRequest\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Bss\RefundRequest\Model\ResourceModel\Label\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $request;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var \Bss\RefundRequest\Model\ResourceModel\Request\CollectionFactory
     */
    protected $refundCollectionFactory;

    /**
     * @var Email
     */
    protected $emailSender;

    public function __construct(
        \Bss\RefundRequest\Helper\Data $helper,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Bss\RefundRequest\Model\ResourceModel\Label\CollectionFactory $collectionFactory,
        \Magento\Framework\Webapi\Rest\Request $request,
        OrderFactory $orderFactory,
        RequestFactory $requestFactory,
        \Bss\RefundRequest\Model\ResourceModel\Request\CollectionFactory $refundCollectionFactory,
        Email $emailSender
    ) {
        $this->helper = $helper;
        $this->objectFactory = $objectFactory;
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->orderFactory = $orderFactory;
        $this->requestFactory = $requestFactory;
        $this->refundCollectionFactory = $refundCollectionFactory;
        $this->emailSender = $emailSender;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $enabled = $this->helper->getConfigEnableModule();
        $orderStatus = $this->helper->getOrderRefund();
        $title = $this->helper->getPopupModuleTitle();
        $description = $this->helper->getDescription();
        $enableDropDown = $this->helper->getConfigEnableDropdown();
        $dropdownTitle = $this->helper->getDropdownTitle();
        $enableYesNo = $this->helper->getConfigEnableOption();
        $titleYesNo = $this->helper->getOptionTitle();
        $reasonTitle = $this->helper->getDetailTitle();
        $obj = $this->objectFactory->create();
        $obj->setItem(array(
            'enable' => $enabled, 'order' => $orderStatus, 'title' => $title,
            'description' => $description, 'enabledDropdown' => $enableDropDown, 'dropDownTitle' => $dropdownTitle,
            'dropDownDataList' => $this->getLabel()->getData(), 'enabledYesNo' => $enableYesNo, 'titleYesNo' => $titleYesNo,
            'YesNoDataList' => [array('code' => 1, 'value' => 'Yes'), array('code' => 0, 'value' => 'No')], 'reasonTitle' => $reasonTitle
        ));
        return $obj->getData();
    }

    /**
     * @inheritdoc
     */
    public function submitRefundRequest($customerId)
    {
        $data = $this->request->getBodyParams();
        $obj = $this->objectFactory->create();

        if ($this->helper->getConfigEnableModule() == '1') {
            if (isset($data['orderId'])) {
                $option = '';
                $radio = '';
                if ($this->helper->getConfigEnableDropdown() && isset($data['optionVal'])) {
                    $option = $data['optionVal'];
                }
                if ($this->helper->getConfigEnableOption() && isset($data['radioVal'])) {
                    $radio = $data['radioVal'];
                }
                $reason = isset($data['reason']) ? $data['reason'] : '';
                try {
                    $order = $this->orderFactory->create()->loadByIncrementId($data['orderId']);
                    if ($order->getIncrementId() && $customerId == $order->getCustomerId()) {
                        if (in_array($order->getStatus(), explode(',', $this->helper->getOrderRefund()))) {
                            if (!$this->isAlreadySubmittedRefundRequest($order->getIncrementId())) {
                                $model = $this->requestFactory->create();
                                $model->setOption($option);
                                $model->setRadio($radio);
                                $model->setOrderId($order->getIncrementId());
                                $model->setReasonComment($reason);
                                $model->setCustomerName($order->getCustomerName());
                                $model->setCustomerEmail($order->getCustomerEmail());
                                $model->save();
                                try {
                                    $this->sendEmail($order);
                                    $obj->setItem(array('success' => true, 'message' => 'Your refund request number #' . $order->getIncrementId() . ' has been submited.'));
                                    return $obj->getData();
                                } catch (\Exception $e) {
                                    $this->messageManager->addErrorMessage($e->getMessage());
                                    $obj->setItem(array('success' => false, 'message' => $e->getMessage()));
                                    return $obj->getData();
                                }
                            } else {
                                $obj->setItem(array('success' => false, 'message' => 'Order was already submitted for refund.'));
                                return $obj->getData();
                            }
                        } else {
                            $obj->setItem(array('success' => false, 'message' => 'Can\'t request refund in current order status.'));
                            return $obj->getData();
                        }
                    } else {
                        $obj->setItem(array('success' => false, 'message' => 'Invalid order.'));
                        return $obj->getData();
                    }
                } catch (NoSuchEntityException $e) {
                    $obj->setItem(array('success' => false, 'message' => 'Order not found.'));
                    return $obj->getData();
                }
            } else {
                $obj->setItem(array('success' => false, 'message' => 'Invalid order.'));
                return $obj->getData();
            }
        } else {
            $obj->setItem(array('success' => false, 'message' => 'Can\'t request refund right now.'));
            return $obj->getData();
        }
    }

    /**
     * check order was already requested for refund or not
     * @param string @orderId
     * @return bool
     */
    public function isAlreadySubmittedRefundRequest($orderId)
    {
        $collection = $this->refundCollectionFactory->create()->addFieldToFilter('increment_id', $orderId);
        if ($collection->getData()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \Bss\RefundRequest\Model\ResourceModel\Label\Collection
     */
    public function getLabel()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', 0);
        return $collection;
    }

    /**
     * @param $orderData
     */
    protected function sendEmail($orderData)
    {
        $emailTemplate = $this->helper->getEmailTemplate();
        $adminEmail    = $this->helper->getAdminEmail();
        $adminEmails   = explode(",", $adminEmail);
        $countEmail    = count($adminEmails);
        if ($countEmail > 1) {
            foreach ($adminEmails as $value) {
                $value             = str_replace(' ', '', $value);
                $emailTemplateData = [
                    'adminEmail'   => $value,
                    'incrementId'  => $orderData->getIncrementId(),
                    'customerName' => $orderData->getCustomerName(),
                    'createdAt'    => $orderData->getCreatedAt(),
                ];
                $this->emailSender->sendEmail($value, $emailTemplate, $emailTemplateData);
            }
        } else {
            $emailTemplateData = [
                'adminEmail'   => $adminEmail,
                'incrementId'  => $orderData->getIncrementId(),
                'customerName' => $orderData->getCustomerName(),
                'createdAt'    => $orderData->getCreatedAt(),
            ];
            $this->emailSender->sendEmail($adminEmail, $emailTemplate, $emailTemplateData);
        }
    }
}
