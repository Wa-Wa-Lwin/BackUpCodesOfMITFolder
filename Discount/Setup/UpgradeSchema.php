<?php

namespace MIT\Discount\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'discount_image_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Discount image id',
                ]
            );

            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'discount_label',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Discount label',
                ]
            );

            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'width',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Discount image width',
                ]
            );

            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'height',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Discount image height',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('salesrule'),
                'discount_image_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Discount image id',
                ]
            );

            $connection->addColumn(
                $setup->getTable('salesrule'),
                'discount_label',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Discount label',
                ]
            );

            $connection->addColumn(
                $setup->getTable('salesrule'),
                'width',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Discount image width',
                ]
            );

            $connection->addColumn(
                $setup->getTable('salesrule'),
                'height',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Discount image height',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'rule_name_mm',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Rule Name(MM)',
                ]
            );
            $connection->addColumn(
                $setup->getTable('salesrule'),
                'rule_name_mm',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Rule Name(MM)',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'discount_label_color',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Discount Label Color',
                ]
            );

            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'discount_label_style',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Discount Label Style',
                ]
            );
        }
    }
}
