<?php

namespace MIT\SalesRuleLabel\Observer\Adminhtml;

use Magento\SalesRule\Model\Rule;
use MIT\SalesRuleLabel\Model\CustomConditionRepository;

class ControllerSaleruleAfterSaveCustom implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var CustomConditionRepository
     */
    private $customConditionRepository;

    public function __construct(
        CustomConditionRepository $CustomConditionRepository
    ) {
        $this->customConditionRepository = $CustomConditionRepository;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var Rule $salesRule */
        $salesRule = $observer->getData('model');
        $discountSalesRuleCollection = $this->customConditionRepository->getBySalesRuleId($salesRule->getId());
        foreach ($discountSalesRuleCollection as $discountSalesRule) {
            $discountSalesRule->setFromDate($salesRule->getFromDate());
            $discountSalesRule->setToDate($salesRule->getToDate());
            $discountSalesRule->setRuleStatus($salesRule->getIsActive());
            $discountSalesRule->setWebsites(implode(',', $salesRule->getWebsiteIds()));
            $discountSalesRule->setCustomerGroups(implode(',', $salesRule->getCustomerGroupIds()));
            $discountSalesRule->setSortOrder($salesRule->getSortOrder());
            $this->customConditionRepository->save($discountSalesRule);
        }
        return $this;
    }
}
