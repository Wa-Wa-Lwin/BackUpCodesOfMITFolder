<?php

namespace MIT\SalesRuleLabel\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MIT\Discount\Helper\OtherRuleHelper;

class ControllerSaleruleLabelAfterSaveCustom implements ObserverInterface {

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
        $model = $observer->getData('model');
        if ($model->getRuleId() > 0) {
            $this->otherRuleHelper->regeneratePromotionPage($model->getSaleRuleId(), OtherRuleHelper::CART_PRICE_RULE_TYPE);
        }
        return $this;
    }
}