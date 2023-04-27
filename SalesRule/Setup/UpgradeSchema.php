<?php

namespace MIT\SalesRule\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('salesrule'),
                'is_weekly_promotion',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'default' => 0,
                    'comment' => 'IsWeeklyPromotion',
                ]
            );
        }
    }
}
