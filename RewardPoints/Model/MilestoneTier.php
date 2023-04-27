<?php

namespace MIT\RewardPoints\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\RewardPoints\Api\Data\MilestoneTierInterface;

class MilestoneTier extends AbstractExtensibleModel implements MilestoneTierInterface {

    /**
     * set tier id
     * @param int $id
     */
    public function setId($id){
        return $this->setData(self::ID, $id);
    }

    /**
     * get tier id
     * @return $this
     */
    public function getId(){
        return $this->getData(self::ID);
    }

    /**
     * set tier name
     * @param string $name
     * @return $this
     */
    public function setName($name){
        return $this->setData(self::NAME, $name);
    }

    /**
     * get tier name
     * @return string
     */
    public function getName(){
        return $this->getData(self::NAME);
    }

    /**
     * set tier min point
     * @param int $point
     * @return $this
     */
    public function setMinPoints($points){
        return $this->setData(self::MIN_POINTS, $points);
    }

    /**
     * get tier min point
     * @return int
     */
    public function getMinPoints(){
        return $this->getData(self::MIN_POINTS);
    }

    /**
     * set tier image path
     * @param string $imgPath
     * @return $this
     */
    public function setImgPath($imgPath){
        return $this->setData(self::IMG_PATH, $imgPath);
    }

    /**
     * get tier image path
     * @return string
     */
    public function getImgPath(){
        return $this->getData(self::IMG_PATH);
    }

    /**
     * set tier description
     * @param string $description
     * @return $this
     */
    public function setDescription($description){
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * get tier description
     * @return string
     */
    public function getDescription(){
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * set customer is current tier or not
     * @param bool $isCurrent
     * @return $this
     */
    public function setIsCurrent($isCurrent){
        return $this->setData(self::IS_CURRENT, $isCurrent);
    }

    /**
     * get customer is current tier or not
     * @return bool
     */
    public function getIsCurrent(){
        return $this->getData(self::IS_CURRENT);
    }

    /**
     * set percentage of current customer
     * @param float $percentage
     * @return $this
     */
    public function setPercentage($percentage){
        return $this->setData(self::PERCENTAGE, $percentage);
    }

    /**
     * get percentage of current customer
     * @return float
     */
    public function getPercentage(){
        return $this->getData(self::PERCENTAGE);
    }
}