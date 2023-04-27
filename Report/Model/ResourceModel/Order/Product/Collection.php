<?php

namespace MIT\Report\Model\ResourceModel\Order\Product;

use Magento\Framework\DB\Select;
use Zend_Db_Select_Exception;

/**
 * Data collection.
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Order\Collection
{
    /**
     * Set Date range to collection.
     *
     * @param string $from
     * @param string $to
     * @return $this
     * @throws Zend_Db_Select_Exception
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()->addAttributeToSelect(
            '*'
        )->addOrderedQty(
            $from,
            $to
        )->setOrder(
            'ordered_qty',
            self::SORT_ORDER_DESC
        );
        return $this;
    }

    /**
     * Add ordered qty's
     *
     * @param string $from
     * @param string $to
     * @return $this
     * @throws Zend_Db_Select_Exception
     */
    public function addOrderedQty($from = '', $to = '')
    {
        $connection = $this->getConnection();
        $orderTableAliasName = $connection->quoteIdentifier('order');

        $orderJoinCondition = [
            $orderTableAliasName . '.entity_id = order_items.order_id',
            $connection->quoteInto("{$orderTableAliasName}.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED),
        ];

        if ($from != '' && $to != '') {
            $fieldName = $orderTableAliasName . '.created_at';
            $orderJoinCondition[] = $this->prepareBetweenSql($fieldName, $from, $to);
        }

        $this->getSelect()->reset()->from(
            ['order_items' => $this->getTable('sales_order_item')],
            [
                'ordered_qty' => 'order_items.qty_ordered',
                'order_items_name' => 'order_items.name',
                'order_items_sku' => 'order_items.sku',
                'ordered_items_total' => 'order_items.base_row_total_incl_tax',
                'order_items_base_price' => 'order_items.base_price',
                'order_items_tax_amount' => 'order_items.base_tax_amount',
            ]
        )->joinInner(
            ['order' => $this->getTable('sales_order')],
            implode(' AND ', $orderJoinCondition),
            ['order_id' => 'order.increment_id',
             'shipping_method' => 'order.shipping_description',
             'order_status' => 'order.status',
             'ordered_total' => 'order.base_grand_total',
             'shipping_amount' => 'order.base_shipping_amount',
             'discount_amount' => 'order.base_discount_amount',
             'refunded_amount' => 'order.base_total_refunded',
             'rewards_discount_amount' => '(order.mp_reward_base_discount * -1)'
            ]
        )->joinLeft(
            ['invoice' => $this->getTable('sales_invoice')],
            'order.entity_id = invoice.order_id',
            [
                'invoice_id' => 'invoice.increment_id'
            ]
        )->joinLeft(
            ['payment' => $this->getTable('sales_order_payment')],
            'order.entity_id = payment.parent_id',
            [
                'payment_method' =>  "REPLACE(REPLACE(CONCAT(JSON_EXTRACT(payment.additional_information, '$.method_title'), '(', COALESCE(JSON_EXTRACT(payment.additional_information, '$.payment_type'), ''), ')'), '()', ''), '\"', '')"
            ]
        )->joinLeft(
            ['shipment' => $this->getTable('sales_shipment')],
            'order.entity_id = shipment.order_id',
            [
                'shipment_id' => 'shipment.increment_id'
            ]
        )->where(
            'order_items.parent_item_id IS NULL'
        )->having(
            'order_items.qty_ordered > ?',
            0
        )->order('order.entity_id');
        return $this;
    }

    /**
     * Set store filter to collection
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->getSelect()->where('order_items.store_id IN (?)', (array)$storeIds);
        }
        return $this;
    }

    /**
     * Set order
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        if (in_array($attribute, ['orders', 'ordered_qty'])) {
            $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return Select
     * @since 100.2.0
     */
    public function getSelectCountSql()
    {
        $countSelect = clone parent::getSelectCountSql();

        $countSelect->reset(Select::COLUMNS);
        $countSelect->columns('COUNT(DISTINCT order_items.item_id)');

        return $countSelect;
    }

    /**
     * Prepare between sql
     *
     * @param string $fieldName Field name with table suffix ('created_at' or 'main_table.created_at')
     * @param string $from
     * @param string $to
     * @return string Formatted sql string
     */
    protected function prepareBetweenSql($fieldName, $from, $to)
    {
        return sprintf(
            '(%s BETWEEN %s AND %s)',
            $fieldName,
            $this->getConnection()->quote($from),
            $this->getConnection()->quote($to)
        );
    }
}
