<?php

namespace MIT\OrderCancel\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * custom cron actions
 */
class CronFile
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagementInterface;

    public function __construct(
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfigInterface,
        OrderManagementInterface $orderManagementInterface
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->orderManagementInterface = $orderManagementInterface;
    }


    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cancelorder.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(json_encode($this->scopeConfigInterface->getValue('order/configurable_cron/auto_cancel_order_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)));

        if ($this->scopeConfigInterface->getValue('order/configurable_cron/auto_cancel_order_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $days_old = $this->scopeConfigInterface->getValue('order/configurable_cron/days_old', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $collection = $this->collectionFactory->create();
            $collection->getSelect()->joinInner('sales_order_payment', 'main_table.entity_id = sales_order_payment.parent_id', 'method');
            $collection->getSelect()
                ->where(' DATE_ADD(main_table.created_at, INTERVAL ? DAY) <= now()', $days_old)
                ->where('main_table.state = ? ', 'new')
                ->where('main_table.status = ? ', 'pending_payment')
                ->where('sales_order_payment.method = ? ', 'kbzpayment');

            //$logger->info($collection->getSelectSql(true));

            foreach ($collection as $item) {
                $logger->info($item->getId());
                $this->orderManagementInterface->cancel($item->getId());
            }
        }
    }
}
