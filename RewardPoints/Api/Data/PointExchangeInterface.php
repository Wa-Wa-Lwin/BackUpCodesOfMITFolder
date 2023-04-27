<?php

namespace MIT\RewardPoints\Api\Data;

interface PointExchangeInterface {

    const IS_USE_MAX_POINTS = 'is_use_max_points';
    const CURRENT_BALANCE = 'current_balance';
    const POINT_SPENT = 'point_spent';
    const RULE_APPLIED = 'rule_applied';
    const RULES = 'rules';

    /**
     * set is use max points
     * @param bool $isMaxPoint
     * @return $this
     */
    public function setUseMaxPoints($isMaxPoint);

    /**
     * get is use max points
     * @return bool
     */
    public function getUseMaxPoints();

    /**
     * set current balance
     * @param int $currentBlanace
     * @return $this
     */
    public function setCurrentBalance($currentBlanace);

    /**
     * get current balance
     * @return int
     */
    public function getCurrentBalance();

    /**
     * set point spent
     * @param int $pointSpent
     * @return $this
     */
    public function setPointSpent($pointSpent);

    /**
     * get point spent
     * @return int
     */
    public function getPointSpent();

    /**
     * set rule applied
     * @param string $ruleApplied
     * @return $this
     */
    public function setRuleApplied($ruleApplied);

    /**
     * get rule applied
     * @return string
     */
    public function getRuleApplied();

    /**
     * set rules
     * @param \MIT\RewardPoints\Api\Data\RewardRulesInterface[] $rules
     * @return $this
     */
    public function setRules(array $rules);

    /**
     * get rules
     * @return \MIT\RewardPoints\Api\Data\RewardRulesInterface[]|[]
     */
    public function getRules();
}