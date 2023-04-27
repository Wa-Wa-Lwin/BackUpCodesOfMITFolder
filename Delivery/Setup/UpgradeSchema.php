<?php

namespace MIT\Delivery\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/dbschema.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($context->getVersion());
        if (version_compare($context->getVersion(), '0.1.1') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('mit_delivery_customdelivery'),
                'custom_delivery_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Custom Delivery Type'
                ]
            );
        }
    }
}
