<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Model;

use MIT\Discount\Api\Data\LabelImageInterface;
use Magento\Framework\Model\AbstractModel;

class LabelImage extends AbstractModel implements LabelImageInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\MIT\Discount\Model\ResourceModel\LabelImage::class);
    }

    /**
     * @inheritDoc
     */
    public function getLabelImageId()
    {
        return $this->getData(self::LABEL_IMAGE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setLabelImageId($labelImageId)
    {
        return $this->setData(self::LABEL_IMAGE_ID, $labelImageId);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getMainCssClass()
    {
        return $this->getData(self::MAIN_CSS_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setMainCssClass($mainCssClass)
    {
        return $this->setData(self::MAIN_CSS_CLASS, $mainCssClass);
    }

    /**
     * @inheritDoc
     */
    public function getSubCssClass()
    {
        return $this->getData(self::SUB_CSS_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setSubCssClass($subCssClass)
    {
        return $this->setData(self::SUB_CSS_CLASS, $subCssClass);
    }

    /**
     * @inheritDoc
     */
    public function getImgPath()
    {
        return $this->getData(self::IMG_PATH);
    }

    /**
     * @inheritDoc
     */
    public function setImgPath($imgPath)
    {
        return $this->setData(self::IMG_PATH, $imgPath);
    }

    /**
     * @inheritDoc
     */
    public function setWidth($width)
    {
        return $this->setData(self::IMG_WIDTH, $width);
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        return $this->getData(self::IMG_WIDTH);
    }

    /**
     * @inheritDoc
     */
    public function setHeight($height)
    {
        return $this->setData(self::IMG_HEIGHT, $height);
    }

    /**
     * @inheritDoc
     */
    public function getHeight()
    {
        return $this->getData(self::IMG_HEIGHT);
    }

}
