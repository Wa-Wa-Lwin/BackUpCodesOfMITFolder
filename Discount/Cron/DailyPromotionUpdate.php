<?php

namespace MIT\Discount\Cron;

use MIT\Discount\Helper\PromotionPageHelper;
use MIT\SalesRuleLabel\Model\Indexer\Rule\RuleProductProcessor;
use Other\Rule\Model\ResourceModel\ImageCollection\CollectionFactory;


/**
 * Daily update catalog price rule by cron
 */
class DailyPromotionUpdate
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var PromotionPageHelper
     */
    private $promotionPageHelper;

    /**
     * @param RuleProductProcessor $ruleProductProcessor
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        PromotionPageHelper $promotionPageHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->promotionPageHelper = $promotionPageHelper;
    }

    /**
     * Daily update catalog price rule by cron
     * Update include interval 3 days - current day - 1 days before + 1 days after
     * This method is called from cron process, cron is working in UTC time and
     * we should generate data for interval -1 day ... +1 day
     *
     * @return void
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $deleteIds = [];
        foreach($collection as $otherRule) {
            if ($otherRule->getIsActive()) {
                $this->promotionPageHelper->generatePromotionPage($otherRule->getImagecollectionId(), $otherRule->getImage1(),
                $otherRule->getImage2(), $otherRule->getImage3(), $otherRule->getImage4(), $otherRule->getName(), explode(',',$otherRule->getCatalogRuleId()), explode(',', $otherRule->getSaleRuleId()),
                $otherRule->getType());
            } else {
                $deleteIds[] = $otherRule->getImagecollectionId();
            }
        }
        if (count($deleteIds) > 0) {
            $this->promotionPageHelper->deletePage($deleteIds);
        }
    }
}
