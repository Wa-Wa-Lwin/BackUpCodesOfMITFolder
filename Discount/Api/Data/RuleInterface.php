<?php

namespace MIT\Discount\Api\Data;

use Magento\CatalogRule\Api\Data\RuleInterface as DataRuleInterface;

interface RuleInterface extends DataRuleInterface
{
    const DISCOUNT_IMG_ID  = 'discount_image_id';
    const DISCOUNT_LABEL = 'discount_label';
    const IMG_WIDTH = 'width';
    const IMG_HEIGHT = 'height';
    const DISCOUNT_LABEL_COLOR = "discount_label_color";
    const DISCOUNT_LABEL_STYLE = "discount_label_style";

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

    /**
     * get discount label color
     * @return string
     */
    public function getDiscountLabelColor();

    /**
     * set discount label color
     * @param string $discountColor
     * @return $this
     */
    public function setDiscountLabelColor($discountColor);

    /**
     * get discount label color
     * @return string
     */
    public function getDiscountLabelStyle();

    /**
     * set discount label color
     * @param string $discountStyle
     * @return $this
     */
    public function setDiscountLabelStyle($discountStyle);
}
