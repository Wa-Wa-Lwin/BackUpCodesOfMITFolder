<?php

namespace MIT\SalesRuleLabel\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomCondition extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('mit_salesrulelabel', 'rule_id');
    }

    /**
     * @param $ruleId
     */
    public function deleteActionIndex($ruleId)
    {
        $this->deleteMultipleData('mit_salesrule_label_product', ['rule_id = ?' => $ruleId]);
    }

    /**
     * @param $data
     */
    public function insertActionIndex($data)
    {
        $this->updateMultipleData('mit_salesrule_label_product', $data);
    }

    /**
     * delete database
     *
     * @param string $tableName
     * @param array $where
     *
     * @return void
     */
    public function deleteMultipleData($tableName, $where = [])
    {
        $table = $this->getTable($tableName);
        if ($table && !empty($where)) {
            $this->getConnection()->delete($table, $where);
        }
    }

    /**
     * update database
     *
     * @param string $tableName
     * @param array $data
     *
     * @return void
     */
    public function updateMultipleData($tableName, $data = [])
    {
        $table = $this->getTable($tableName);
        if ($table && !empty($data)) {
            $this->getConnection()->insertMultiple($table, $data);
        }
    }

    /**
     * Get salesrulelabel by saleruleid
     *
     * @param  string $saleRuleId
     * @return array
     */
    public function getIdsBySalesRuleId($saleRuleId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from('mit_salesrulelabel', 'rule_id')->where('sale_rule_id = :sale_rule_id');

        $bind = [':sale_rule_id' => (string)$saleRuleId];

        return $connection->fetchAll($select, $bind);
    }
}