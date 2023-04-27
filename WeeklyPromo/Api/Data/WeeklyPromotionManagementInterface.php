<?php

namespace MIT\WeeklyPromo\Api\Data;

interface WeeklyPromotionManagementInterface
{
    const TITLE = 'title';
    const BTN_NAME = 'btn_name';
    const CATEGORY_ID = 'category_id';
    const ITEMS = 'items';

    /**
     * get title
     * @return string
     */
    public function getTitle();

    /**
     * set title
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * get btn name
     * @return string
     */
    public function getBtnName();

    /**
     * set btn name
     * @param string $btnName
     * @return $this
     */
    public function setBtnName($btnName);

    /**
     * get category id
     * @return int
     */
    public function getCategoryId();

    /**
     * set category id
     * @param int $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId);

    /**
     * get items
     * @return \MIT\Product\Api\Data\CustomProductManagementInterface[]|[]
     */
    public function getItems();

    /**
     * set items
     * @param \MIT\Product\Api\Data\CustomProductManagementInterface[] $items
     * @return $this
     */
    public function setItems(array $items = []);
}
