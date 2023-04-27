<?php

namespace MIT\Product\Api\Data;

interface DiscountLabelInterface
{
    const LABEL = 'label';
    const IMG_PATH = 'img_path';
    const MAIN_CLASS = 'main_class';
    const SUB_CLASS = 'sub_class';
    const STYLE = 'style';
    const LABEL_STYLE = 'label_style';

    /**
     * get label
     * @return string
     */
    public function getLabel();

    /**
     * set label
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * get image path
     * @return string
     */
    public function getImgPath();

    /**
     * set image path
     * @param string $imgPath
     * @return $this
     */
    public function setImgPath($imgPath);

    /**
     * get main_class
     * @return string
     */
    public function getMainClass();

    /**
     * set main_class
     * @param string $mainClass
     * @return $this
     */
    public function setMainClass($mainClass);

    /**
     * get subClass
     * @return string
     */
    public function getSubClass();

    /**
     * set subClass
     * @param string $subClass
     * @return $this
     */
    public function setSubClass($subClass);

    /**
     * get style
     * @return string
     */
    public function getStyle();

    /**
     * set style
     * @param string $style
     * @return $this
     */
    public function setStyle($style);
       
    /**
     * get label style
     * @return string
    */
    public function getLabelStyle();

    /**
     * set label style
     * @param string $labelStyle
     * @return $this
    */
    public function setLabelStyle(string $labelStyle);
}
