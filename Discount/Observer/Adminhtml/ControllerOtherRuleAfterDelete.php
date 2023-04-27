<?php

namespace MIT\Discount\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MIT\Discount\Helper\PromotionPageHelper;

class ControllerOtherRuleAfterDelete implements ObserverInterface {

    /**
     * @var PromotionPageHelper
     */
    private $promotionPageHelper;

    public function __construct(
        PromotionPageHelper $promotionPageHelper
    )
    {
        $this->promotionPageHelper = $promotionPageHelper;
    }

    public function execute(Observer $observer)
    {
        $pageIds = $observer->getData('ids');
        $this->promotionPageHelper->deletePage($pageIds);
        return $this;
    }
}