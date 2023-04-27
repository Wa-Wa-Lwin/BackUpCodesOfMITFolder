<?php

namespace MIT\RewardPoints\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\RewardPoints\Api\Data\CustomerMilestoneInterface;

class CustomerMilestone extends AbstractExtensibleModel implements CustomerMilestoneInterface {

    /**
     * set current tier name
     * @param string $name
     * @return $this
     */
    public function setName($name){
        return $this->setData(self::NAME, $name);
    }

    /**
     * get current tier name
     * @return string
     */
    public function getName(){
        return $this->getData(self::NAME);
    }

    /**
     * set is up tier exist
     * @param bool $upTierExist
     * @return $this
     */
    public function setIsUpTierExist($upTierExist){
        return $this->setData(self::UP_TIER_EXIST, $upTierExist);
    }

    /**
     * get is up tier exist
     * @return bool
     */
    public function getIsUpTierExist(){
        return $this->getData(self::UP_TIER_EXIST);
    }

    /**
     * set require point to go up tier
     * @param float $requirePoints
     * @return $this
     */
    public function setRequirePoints($requirePoints){
        return $this->setData(self::REQUIRE_POINTS, $requirePoints);
    }

    /**
     * get require point to go up tier
     * @return float
     */
    public function getRequirePoints(){
        return $this->getData(self::REQUIRE_POINTS);
    }

    /**
     * set up tier name
     * @param string $name
     * @return $this
     */
    public function setUpTierName($name){
        return $this->setData(self::UP_TIER_NAME, $name);
    }

    /**
     * get up tier name
     * @return string
     */
    public function getUpTierName(){
        return $this->getData(self::UP_TIER_NAME);
    }

    /**
     * set img Path
     * @param string $imgPath
     * @return $this
     */
    public function setImgPath($imgPath){
        return $this->setData(self::IMG_PATH, $imgPath);
    }

    /**
     * get img Path
     * @return string
     */
    public function getImgPath(){
        return $this->getData(self::IMG_PATH);
    }

    /**
     * set tier list
     * @param \MIT\RewardPoints\Api\Data\MilestoneTierInterface[] $tierList
     * @return $this
     */
    public function setTierList(array $tierList = []){
        return $this->setData(self::TIER_LIST, $tierList);
    }

    /**
     * get tier list
     * @return \MIT\RewardPoints\Api\Data\MilestoneTierInterface[]|[]
     */
    public function getTierList(){
        return $this->getData(self::TIER_LIST);
    }
}