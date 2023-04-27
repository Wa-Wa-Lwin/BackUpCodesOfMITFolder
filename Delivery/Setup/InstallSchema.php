<?php

namespace MIT\Delivery\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('mit_delivery_customdelivery')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mit_delivery_customdelivery')
            )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'region',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Region'
                )
                ->addColumn(
                    'city',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'City'
                )
                ->addColumn(
                    'weight',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [],
                    'Weight'
                )
                ->addColumn(
                    'items',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Items'
                )
                ->addColumn(
                    'total',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [],
                    'Total'
                )
                ->addColumn(
                    'shipping',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [],
                    'Shipping'
                )->setComment('MIT Delivery Shipping table');

            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('mit_delivery_customdelivery'),
                $setup->getIdxName(
                    $installer->getTable('mit_delivery_customdelivery'),
                    ['region', 'city'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['region', 'city'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
        }

        $installer->endSetup();
    }
}
