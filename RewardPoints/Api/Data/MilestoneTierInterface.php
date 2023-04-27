<?php

namespace MIT\RewardPoints\Api\Data;

interface MilestoneTierInterface {

    const ID = 'id';
    const NAME = 'name';
    const MIN_POINTS = 'point';
    const IMG_PATH = 'img_path';
    const DESCRIPTION = 'description';
    const IS_CURRENT = 'is_current';
    const PERCENTAGE = 'percentage';

    /**
     * set tier id
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * get tier id
     * @return int
     */
    public function getId();

    /**
     * set tier name
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * get tier name
     * @return string
     */
    public function getName();

    /**
     * set tier min point
     * @param int $points
     * @return $this
     */
    public function setMinPoints($points);

    /**
     * get tier min point
     * @return int
     */
    public function getMinPoints();

    /**
     * set tier image path
     * @param string $imgPath
     * @return $this
     */
    public function setImgPath($imgPath);

    /**
     * get tier image path
     * @return string
     */
    public function getImgPath();

    /**
     * set tier description
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * get tier description
     * @return string
     */
    public function getDescription();

    /**
     * set customer is current tier or not
     * @param bool $isCurrent
     * @return $this
     */
    public function setIsCurrent($isCurrent);

    /**
     * get customer is current tier or not
     * @return bool
     */
    public function getIsCurrent();

    /**
     * set percentage of current customer
     * @param float $percentage
     * @return $this
     */
    public function setPercentage($percentage);

    /**
     * get percentage of current customer
     * @return float
     */
    public function getPercentage();

}