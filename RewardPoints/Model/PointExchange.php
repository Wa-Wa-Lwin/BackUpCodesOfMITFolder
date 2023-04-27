<?php

namespace MIT\RewardPoints\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\RewardPoints\Api\Data\PointExchangeInterface;
use MIT\RewardPoints\Api\Data\RewardRulesInterface;

class PointExchange extends AbstractExtensibleModel implements PointExchangeInterface {

     /**
     * set is use max points
     * @param bool $isMaxPoint
     * @return $this
     */
    public function setUseMaxPoints($isMaxPoint){
        return $this->setData(self::IS_USE_MAX_POINTS, $isMaxPoint);
    }

    /**
     * get is use max points
     * @return bool
     */
    public function getUseMaxPoints(){
        return $this->getData(self::IS_USE_MAX_POINTS);
    }

    /**
     * set current balance
     * @param int $currentBlanace
     * @return $this
     */
    public function setCurrentBalance($currentBlanace) {
        return $this->setData(self::CURRENT_BALANCE, $currentBlanace);
    }

    /**
     * get current balance
     * @return int
     */
    public function getCurrentBalance() {
        return $this->getData(self::CURRENT_BALANCE);
    }

        /**
     * set point spent
     * @param int $pointSpent
     * @return $this
     */
    public function setPointSpent($pointSpent) {
        return $this->setData(self::POINT_SPENT, $pointSpent);
    }

    /**
     * get point spent
     * @return int
     */
    public function getPointSpent() {
        return $this->getData(self::POINT_SPENT);
    }

    /**
     * set rule applied
     * @param string $ruleApplied
     * @return $this
     */
    public function setRuleApplied($ruleApplied) {
        return $this->setData(self::RULE_APPLIED, $ruleApplied);
    }

    /**
     * get rule applied
     * @return string
     */
    public function getRuleApplied() {
        return $this->getData(self::RULE_APPLIED);
    }

    /**
     * set rules
     * @param \MIT\RewardPoints\Api\Data\RewardRulesInterface[] $rules
     * @return $this
     */
    public function setRules($rules){
        return $this->setData(self::RULES, $rules);
    }

    /**
     * get rules
     * @return \MIT\RewardPoints\Api\Data\RewardRulesInterface[]|[]
     */
    public function getRules(){
        return $this->getData(self::RULES);
    }
}