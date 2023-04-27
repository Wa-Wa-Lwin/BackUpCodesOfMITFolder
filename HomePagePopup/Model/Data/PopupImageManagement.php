<?php

namespace MIT\HomePagePopup\Model\Data;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\HomePagePopup\Api\Data\PopupImageManagementInterface;

class PopupImageManagement extends AbstractExtensibleModel implements PopupImageManagementInterface
{
    /**
     * @inheritdoc
     */
    public function setImagePath($image)
    {
        return $this->setData(self::IMAGE_PATH, $image);
    }

    /**
     * @inheritdoc
     */
    public function getImagePath()
    {
        return $this->getData(self::IMAGE_PATH);
    }

}
