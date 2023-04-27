<?php

namespace MIT\Discount\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MIT\Discount\Helper\PromotionPageHelper;
use Other\Rule\Model\ImageCollection;

class ControllerOtherRuleAfterSave implements ObserverInterface {

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
        /** @var ImageCollection $otherRule */
        $otherRule = $observer->getData('model');
        if ($otherRule->getImagecollectionId() > 0) {
            if ($otherRule->getIsActive()) {
                $this->promotionPageHelper->generatePromotionPage($otherRule->getImagecollectionId(), $otherRule->getImage1(),
                $otherRule->getImage2(), $otherRule->getImage3(), $otherRule->getImage4(), $otherRule->getName(), explode(',',$otherRule->getCatalogRuleId()), explode(',', $otherRule->getSaleRuleId()),
                $otherRule->getType());
            } else {
                $this->promotionPageHelper->deletePage([$otherRule->getImagecollectionId()]);
            }
            return $this;
        }
    }
}