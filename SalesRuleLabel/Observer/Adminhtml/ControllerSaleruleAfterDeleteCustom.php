<?php

namespace MIT\SalesRuleLabel\Observer\Adminhtml;

use Magento\SalesRule\Model\Rule;
use MIT\SalesRuleLabel\Model\CustomConditionFactory;
use MIT\SalesRuleLabel\Model\CustomConditionRepository;

class ControllerSaleruleAfterDeleteCustom implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var CustomConditionRepository
     */
    private $customConditionRepository;

    /**
     * @var CustomConditionFactory
     */
    private $customConditionFactory;

    public function __construct(
        CustomConditionRepository $CustomConditionRepository,
        CustomConditionFactory $customConditionFactory
    ) {
        $this->customConditionRepository = $CustomConditionRepository;
        $this->customConditionFactory = $customConditionFactory;
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
        $salesRuleId = $observer->getData('id');
        $discountSalesRuleCollection = $this->customConditionRepository->getBySalesRuleId($salesRuleId);
        foreach ($discountSalesRuleCollection as $discountSalesRule) {
            $model = $this->customConditionFactory->create();
            $model->load($discountSalesRule->getRuleId());
            $model->delete();
        }
        return $this;
    }
}
