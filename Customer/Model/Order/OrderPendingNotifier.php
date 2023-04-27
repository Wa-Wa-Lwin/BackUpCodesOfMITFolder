<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MIT\Customer\Model\Order;

use Psr\Log\LoggerInterface as Logger;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;
use MIT\Customer\Model\Order\Email\Sender\OrderPendingSender;

/**
 * Class OrderNotifier
 * @package Magento\Sales\Model
 */
class OrderPendingNotifier extends \Magento\Sales\Model\AbstractNotifier
{
    /**
     * @var CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var OrderPendingSender
     */
    protected $sender;

    /**
     * @param CollectionFactory $historyCollectionFactory
     * @param Logger $logger
     * @param OrderPendingSender $sender
     */
    public function __construct(
        CollectionFactory $historyCollectionFactory,
        Logger $logger,
        OrderPendingSender $sender
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->logger = $logger;
        $this->sender = $sender;
    }
}
