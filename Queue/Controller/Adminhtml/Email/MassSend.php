<?php

declare(strict_types=1);

namespace MIT\Queue\Controller\Adminhtml\Email;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SalesCollectionFactory;
use MIT\Queue\Model\Config;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class MassSend extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var salesCollectionFactory
     */
    protected $salesCollectionFactory;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PublisherInterface
     */
    protected $publisherInterface;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param Config $config
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param SalesCollectionFactory $salesCollectionFactory
     * @param PublisherInterface $publisherInterface,
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Config $config,
        CustomerCollectionFactory $customerCollectionFactory,
        SalesCollectionFactory $salesCollectionFactory,
        PublisherInterface $publisherInterface,
        JsonHelper $jsonHelper
    ) {
        $this->filter = $filter;
        $this->config = $config;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->salesCollectionFactory = $salesCollectionFactory;
        $this->publisherInterface = $publisherInterface;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $data = [];

        $collection = $this->filter->getCollection($this->customerCollectionFactory->create());
        $storeId = 0;
        foreach ($collection as $item) {
            if (preg_match("/^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i", $item->getEmail())) {
                $data['customer'][] = array(
                    'name' => $item->getName(),
                    'email' => $item->getEmail()
                );
                $storeId = $item->getData('store_id');
            }
        }

        $data['storeId'] = $storeId;
        $this->publisherInterface->publish('notifycustomer.massmail', $this->jsonHelper->jsonEncode($data));
        $this->messageManager->addSuccessMessage(__('Mail sending is added to queue. Please check notification for mail sending status.'));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
