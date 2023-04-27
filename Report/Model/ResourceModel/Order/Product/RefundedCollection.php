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
class RefundedCollection extends \Magento\Reports\Model\ResourceModel\Order\Collection
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
        );
        // ->setOrder(
        //     'ordered_qty',
        //     self::SORT_ORDER_DESC
        // );
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
            $orderTableAliasName . '.entity_id = credit_memo.order_id',
            $connection->quoteInto("{$orderTableAliasName}.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED),
        ];

        if ($from != '' && $to != '') {
            $fieldName = 'credit_memo.created_at';
            $orderJoinCondition[] = $this->prepareBetweenSql($fieldName, $from, $to);
        }

        $this->getSelect()->reset()->from(
            ['credit_memo' => $this->getTable('sales_creditmemo')],
            [
                'sub_total' => 'credit_memo.subtotal',
                'adjustment' => 'credit_memo.adjustment',
                'refunded_amt' => 'credit_memo.base_grand_total',
                'discount_amt' => 'credit_memo.discount_amount',
                'refund_id' => 'credit_memo.increment_id'
            ]
        )->joinInner(
            ['credit_memo_item' => $this->getTable('sales_creditmemo_item')],
            'credit_memo.entity_id = credit_memo_item.parent_id',
            [
                'order_items_sku' => 'credit_memo_item.sku',
                'order_items_name' => 'credit_memo_item.name',
                'refunded_qty' => 'credit_memo_item.qty'
            ]
        )->joinInner(
            ['order' => $this->getTable('sales_order')],
            implode(' AND ', $orderJoinCondition),
            [
                'order_id' => 'order.increment_id',
                'order_status' => 'order.status',
            ]
        )->where('credit_memo_item.price > 0')
            ->order('order.entity_id');

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
            $this->getSelect()->where('credit_memo.store_id IN (?)', (array)$storeIds);
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
        $countSelect->columns('COUNT(DISTINCT credit_memo_item.entity_id)');

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
