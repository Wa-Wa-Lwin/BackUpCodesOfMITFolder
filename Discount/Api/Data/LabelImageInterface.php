<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Api\Data;

interface LabelImageInterface
{

    const NAME = 'name';
    const SUB_CSS_CLASS = 'sub_css_class';
    const IMG_PATH = 'img_path';
    const MAIN_CSS_CLASS = 'main_css_class';
    const LABEL_IMAGE_ID = 'label_image_id';
    const IMG_WIDTH = 'width';
    const IMG_HEIGHT = 'height';

    /**
     * Get label_image_id
     * @return string|null
     */
    public function getLabelImageId();

    /**
     * Set label_image_id
     * @param string $labelImageId
     * @return \MIT\Discount\LabelImage\Api\Data\LabelImageInterface
     */
    public function setLabelImageId($labelImageId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \MIT\Discount\LabelImage\Api\Data\LabelImageInterface
     */
    public function setName($name);

    /**
     * Get main_css_class
     * @return string|null
     */
    public function getMainCssClass();

    /**
     * Set main_css_class
     * @param string $mainCssClass
     * @return \MIT\Discount\LabelImage\Api\Data\LabelImageInterface
     */
    public function setMainCssClass($mainCssClass);

    /**
     * Get sub_css_class
     * @return string|null
     */
    public function getSubCssClass();

    /**
     * Set sub_css_class
     * @param string $subCssClass
     * @return \MIT\Discount\LabelImage\Api\Data\LabelImageInterface
     */
    public function setSubCssClass($subCssClass);

    /**
     * Get img_path
     * @return string|null
     */
    public function getImgPath();

    /**
     * Set img_path
     * @param string $imgPath
     * @return \MIT\Discount\LabelImage\Api\Data\LabelImageInterface
     */
    public function setImgPath($imgPath);

    /**
     * set img width
     * @param int $width
     * @return $this
     */
    public function setWidth($width);

    /**
     * get img width
     * @return int
     */
    public function getWidth();

    /**
     * set img height
     * @param int $height
     * @return $this
     */
    public function setHeight($height);

    /**
     * get img height
     * @return int
     */
    public function getHeight();
}