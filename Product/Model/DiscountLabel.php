<?php

namespace MIT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\Product\Api\Data\DiscountLabelInterface;

class DiscountLabel extends AbstractExtensibleModel implements DiscountLabelInterface {

      /**
     * get label
     * @return string
     */
    public function getLabel() {
        return $this->getData(self::LABEL);
    }

    /**
     * set label
     * @param string $label
     * @return $this
     */
    public function setLabel($label) {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * get image path
     * @return string
     */
    public function getImgPath() {
        return $this->getData(self::IMG_PATH);
    }

    /**
     * set image path
     * @param string $imgPath
     * @return $this
     */
    public function setImgPath($imgPath) {
        return $this->setData(self::IMG_PATH, $imgPath);
    }

    /**
     * get main_class
     * @return string
     */
    public function getMainClass() {
        return $this->getData(self::MAIN_CLASS);
    }

    /**
     * set main_class
     * @param string $mainClass
     * @return $this
     */
    public function setMainClass($mainClass) {
        return $this->setData(self::MAIN_CLASS, $mainClass);
    }

    /**
     * get subClass
     * @return string
     */
    public function getSubClass() {
        return $this->getData(self::SUB_CLASS);
    }

    /**
     * set subClass
     * @param string $subClass
     * @return $this
     */
    public function setSubClass($subClass) {
        return $this->setData(self::SUB_CLASS, $subClass);
    }

    /**
     * get style
     * @return string
     */
    public function getStyle() {
        return $this->getData(self::STYLE);
    }

    /**
     * set style
     * @param string $style
     * @return $this
     */
    public function setStyle($style) {
        return $this->setData(self::STYLE, $style);
    }

    /**
     * @inheritdoc
    */
    public function setLabelStyle(string $labelStyle)
    {
        return $this->setData(self::LABEL_STYLE, $labelStyle);
    }

    /**
     * @inheritdoc
    */
    public function getLabelStyle()
    {
        return $this->getData(self::LABEL_STYLE);
    } 
}