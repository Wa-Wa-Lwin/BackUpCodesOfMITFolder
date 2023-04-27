<?php

namespace MIT\Discount\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MIT\Discount\Helper\OtherRuleHelper;

class ControllerCatalogRuleAfterSave implements ObserverInterface {

    /**
     * @var OtherRuleHelper
     */
    private $otherRuleHelper;

    public function __construct(
        OtherRuleHelper $otherRuleHelper
    )
    {
        $this->otherRuleHelper = $otherRuleHelper;
    }

    public function execute(Observer $observer)
    {
        $id = $observer->getData('id');
        if ($id) {
            $this->otherRuleHelper->regeneratePromotionPage($id, OtherRuleHelper::CATALOG_RULE_TYPE);
        }
        return $this;
    }
}