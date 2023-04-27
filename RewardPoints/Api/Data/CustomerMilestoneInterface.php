<?php

namespace MIT\RewardPoints\Api\Data;

interface CustomerMilestoneInterface {

    const NAME = 'name';
    const UP_TIER_EXIST = 'up_tier_exist';
    const REQUIRE_POINTS = 'require_points';
    const UP_TIER_NAME = 'up_tier_name';
    const IMG_PATH = 'img_path';
    const TIER_LIST = 'tier_list';

    /**
     * set current tier name
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * get current tier name
     * @return string
     */
    public function getName();

    /**
     * set is up tier exist
     * @param bool $upTierExist
     * @return $this
     */
    public function setIsUpTierExist($upTierExist);

    /**
     * get is up tier exist
     * @return bool
     */
    public function getIsUpTierExist();

    /**
     * set require point to go up tier
     * @param float $requirePoints
     * @return $this
     */
    public function setRequirePoints($requirePoints);

    /**
     * get require point to go up tier
     * @return float
     */
    public function getRequirePoints();

    /**
     * set up tier name
     * @param string $name
     * @return $this
     */
    public function setUpTierName($name);

    /**
     * get up tier name
     * @return string
     */
    public function getUpTierName();

    /**
     * set img Path
     * @param string $imgPath
     * @return $this
     */
    public function setImgPath($imgPath);

    /**
     * get img Path
     * @return string
     */
    public function getImgPath();

    /**
     * set tier list
     * @param \MIT\RewardPoints\Api\Data\MilestoneTierInterface[] $tierList
     * @return $this
     */
    public function setTierList(array $tierList = []);

    /**
     * get tier list
     * @return \MIT\RewardPoints\Api\Data\MilestoneTierInterface[]|[]
     */
    public function getTierList();
}