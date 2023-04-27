<?php

namespace MIT\RewardPoints\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\RewardPoints\Api\Data\RewardRulesInterface;

class RewardRules extends AbstractExtensibleModel implements RewardRulesInterface {

    /**
     * set id
     * @param string $id
     * @return $this
     */
    public function setId($id){
        return $this->setData(self::ID, $id);
    }

    /**
     * get id
     * @return string
     */
    public function getId(){
        return $this->getData(self::ID);
    }

    /**
     * set label
     * @param string $label
     * @return $this
     */
    public function setLabel($label){
        return $this->setData(self::LABEL, $label);
    }

    /**
     * get label
     * @return string
     */
    public function getLabel(){
        return $this->getData(self::LABEL);
    }

    /**
     * set min
     * @param int $min
     * @return $this
     */
    public function setMin($min){
        return $this->setData(self::MIN, $min);
    }

    /**
     * get min
     * @return int
     */
    public function getMin(){
        return $this->getData(self::MIN);
    }

    /**
     * set max
     * @param int $max
     * @return $this
     */
    public function setMax($max){
        return $this->setData(self::MAX, $max);
    }

    /**
     * get max
     * @return int
     */
    public function getMax(){
        return $this->getData(self::MAX);
    }

    /**
     * set step
     * @param int $step
     * @return $this
     */
    public function setStep($step){
        return $this->setData(self::STEP, $step);
    }

    /**
     * get step
     * @return int
     */
    public function getStep(){
        return $this->getData(self::STEP);
    }

    /**
     * set is display filter
     * @param bool $isFilter
     * @return $this
     */
    public function setIsDisplayFilter($isFilter){
        return $this->setData(self::IS_DISPLAY_FILTER, $isFilter);
    }

    /**
     * get is display filter
     * @return bool
     */
    public function getIsDisplayFilter(){
        return $this->getData(self::IS_DISPLAY_FILTER);
    }
}