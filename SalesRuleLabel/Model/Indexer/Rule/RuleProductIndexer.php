<?php

namespace MIT\SalesRuleLabel\Model\Indexer\Rule;

use MIT\SalesRuleLabel\Model\Indexer\RuleIndexer;

class RuleProductIndexer implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{

    /**
     * @var RuleIndexer
     */
    protected $ruleIndexer;

    public function __construct(
        RuleIndexer $ruleIndexer
    ) {
        $this->ruleIndexer = $ruleIndexer;
    }

    /*
     * Used by mview, allows process indexer in the "Update on schedule" mode
     */
    public function execute($ids)
    {
        $this->ruleIndexer->reindexByIds($ids);
    }

    /*
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */
    public function executeFull()
    {
        $this->ruleIndexer->reindexFull();
        //Should take into account all placed orders in the system
    }

    /*
     * Works with a set of entity changed (may be massaction)
     */
    public function executeList(array $ids)
    {
        $this->ruleIndexer->reindexByIds($ids);
        //Works with a set of placed orders (mass actions and so on)
    }

    /*
     * Works in runtime for a single entity using plugins
     */
    public function executeRow($id)
    {
        $this->ruleIndexer->reindexById($id);
        //Works in runtime for a single order using plugins
    }
}
