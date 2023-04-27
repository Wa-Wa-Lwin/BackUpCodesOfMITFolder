<?php

namespace MIT\CatalogRule\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'home_slider_img',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Home Slider Image',
                ]
            );

            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'promo_slider_img',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Promo Slider Image',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'is_home_slider',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'default' => 0,
                    'comment' => 'IsHomeSlider',
                ]
            );

            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'is_home_slider_one',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'default' => 0,
                    'comment' => 'IsHomeSliderOne',
                ]
            );

            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'is_promotion_slider',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'default' => 0,
                    'comment' => 'IsPromotionSlider',
                ]
            );

            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'is_weekly_promotion',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'default' => 0,
                    'comment' => 'IsWeeklyPromotion',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('catalogrule'),
                'home_slider_img_mobile',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Home Slider Image Mobile',
                ]
            );
        }
    }
}
