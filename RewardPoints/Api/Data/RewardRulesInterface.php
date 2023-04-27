<?php

namespace MIT\RewardPoints\Api\Data;

interface RewardRulesInterface {
    const ID = 'id';
    const LABEL = 'label';
    const MIN = 'min';
    const MAX = 'max';
    const STEP = 'step';
    const IS_DISPLAY_FILTER = 'is_display_filter';

    /**
     * set id
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * get id
     * @return string
     */
    public function getId();

    /**
     * set label
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * get label
     * @return string
     */
    public function getLabel();

    /**
     * set min
     * @param int $min
     * @return $this
     */
    public function setMin($min);

    /**
     * get min
     * @return int
     */
    public function getMin();

    /**
     * set max
     * @param int $max
     * @return $this
     */
    public function setMax($max);

    /**
     * get max
     * @return int
     */
    public function getMax();

    /**
     * set step
     * @param int $step
     * @return $this
     */
    public function setStep($step);

    /**
     * get step
     * @return int
     */
    public function getStep();

    /**
     * set is display filter
     * @param bool $isFilter
     * @return $this
     */
    public function setIsDisplayFilter($isFilter);

    /**
     * get is display filter
     * @return bool
     */
    public function getIsDisplayFilter();
}