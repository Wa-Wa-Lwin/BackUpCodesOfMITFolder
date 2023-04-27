<?php

namespace MIT\WeeklyPromo\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\WeeklyPromo\Api\Data\WeeklyPromotionManagementInterface;

class WeeklyPromotionManagement extends AbstractExtensibleModel  implements WeeklyPromotionManagementInterface
{

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function getBtnName()
    {
        return $this->getData(self::BTN_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setBtnName($btnName)
    {
        return $this->setData(self::BTN_NAME, $btnName);
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }

    /**
     * @inheritdoc
     */
    public function setItems(array $items = [])
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * @inheritdoc
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }
}
