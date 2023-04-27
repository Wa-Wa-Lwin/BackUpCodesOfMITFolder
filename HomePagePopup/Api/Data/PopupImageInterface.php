<?php

namespace MIT\HomePagePopup\Api\Data;

interface PopupImageInterface
{

    const POPUPIMAGE_ID = 'image_id';
    const NAME = 'name';
    const IMAGE = 'image';
    const IS_HOMEPAGE = 'is_homepage';
    const IS_PROMOTION = 'is_promotion';

    /**
     * Get popupimage_id
     * @return string|null
     */
    public function getPopupImageId();

    /**
     * Set popupimage_id
     * @param string $popupimageId
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface
     */
    public function setPopupImageId($popupimageId);

    /**
     * Get Name
     * @return string|null
     */
    public function getName();

    /**
     * Set Name
     * @param string $name
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface
     */
    public function setName($name);

    /**
     * Get image
     * @return string|null
     */
    public function getImage();

    /**
     * Set image
     * @param string $image
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface
     */
    public function setImage($image);

    /**
     * Get is_homepage
     * @return int
     */
    public function getIsHomepage();

    /**
     * Set is_homepage
     * @param int $value
     * @return $this
     */
    public function setIsHomepage($value);

    /**
     * Get is_promotion
     * @return int
     */
    public function getIsPromotion();

    /**
     * Set is_promotion
     * @param int $value
     * @return $this
     */
    public function setIsPromotion($value);

}
