<?php

namespace MIT\Cart\Model\Cart\Totals;

use Magento\Quote\Model\Cart\Totals\Item;
use MIT\Cart\Api\Data\CustomTotalsItemInterface;

class CustomItem extends Item implements CustomTotalsItemInterface
{
    /**
     * @inheritdoc
     */
    public function setImgPath($imgPath)
    {
        return $this->setData(self::KEY_IMG_Path, $imgPath);
    }

    /**
     * @inheritdoc
     */
    public function getImgPath()
    {
        return $this->_get(self::KEY_IMG_Path);
    }
}
