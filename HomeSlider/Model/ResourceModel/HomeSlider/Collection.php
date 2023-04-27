<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\HomeSlider\Model\ResourceModel\HomeSlider;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'homeslider_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \MIT\HomeSlider\Model\HomeSlider::class,
            \MIT\HomeSlider\Model\ResourceModel\HomeSlider::class
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $data = $this->getSelect()->joinLeft(
            ['secondTable' => $this->getTable('catalog_category_entity_varchar')], //2nd table name by which you want to join mail table
            '`main_table`.category_id = `secondTable`.entity_id', // common column which available in both table 
        )->joinLeft(
            ['thirdTable' => $this->getTable('catalog_category_entity')],
            "`main_table`.category_id = `thirdTable`.entity_id AND `secondTable`.attribute_id =  (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'name' and entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_category'))",
            ['category' => 'secondTable.value']
        )->group('main_table.homeslider_id');
        $this->getSelect()->columns(new \Zend_Db_Expr('secondTable.value as category'));
        $this->addFilterToMap(
            'category',
            new \Zend_Db_Expr('secondTable.value')
        );
    }
}
