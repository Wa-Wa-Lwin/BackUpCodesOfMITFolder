<?php

namespace MIT\SalesRuleLabel\Console\Command;

use Exception;
use Magento\Framework\App\State;
use MIT\SalesRuleLabel\Model\Indexer\RuleIndexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Reindex extends Command
{
    /**
     * @var State
     */
    protected $appState;

    /**
     * @var RuleIndexer
     */
    protected $ruleIndexer;

    /**
     * Reindex constructor.
     *
     * @param State $appState
     * @param RuleIndexer $ruleIndexer
     * @param string|null $name
     */
    public function __construct(
        State $appState,
        RuleIndexer $ruleIndexer,
        string $name = null
    ) {
        $this->appState    = $appState;
        $this->ruleIndexer = $ruleIndexer;

        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('frontend');
            $this->ruleIndexer->reindexFull();
            $output->writeln('<info>Reindex Successfully!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('abc:reindex')
            ->setDescription('Reindex Auto Related Product');
    }
}
