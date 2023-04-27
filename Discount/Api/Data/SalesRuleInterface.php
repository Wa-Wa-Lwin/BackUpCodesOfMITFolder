<?php

namespace MIT\Discount\Api\Data;

use Magento\SalesRule\Api\Data\RuleInterface;

interface SalesRuleInterface extends RuleInterface {

    const DISCOUNT_IMG_ID  = 'discount_image_id';
    const DISCOUNT_LABEL = 'discount_label';
    const IMG_WIDTH = 'width';
    const IMG_HEIGHT = 'height';

    /**
     * set discount image id
     * @param int $discountImgId
     * @return $this
     */
    public function setDiscountImageId($discountImgId);

    /**
     * get discount image id
     * @return int
     */
    public function getDiscountImageId();

    /**
     * set discount label
     * @param string $discountLabel
     * @return $this
     */
    public function setDiscountLabel($discountLabel);

    /**
     * get discount label
     * @return string
     */
    public function getDiscountLabel();

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