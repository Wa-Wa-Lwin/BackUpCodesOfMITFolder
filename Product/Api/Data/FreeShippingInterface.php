<?php

namespace MIT\Product\Api\Data;

interface FreeShippingInterface{
    const IS_FREE_SHIPPING ='is_free_shipping';
    const FREE_SHIPPING_IMG_URL = 'free_shipping_image_url';

    /**
     * get is free shipping
     * @return bool
     */
    public function getIsFreeShipping();

    /**
     * set is free shipping
     * @param bool $isFreeShipping
     * @return $this
     */
    public function setIsFreeShipping($isFreeShipping);

    /**
     * get free shipping image url
     * @return string
     */
    public function getFreeShippingImgPath();

    /**
     * set shipping image url
     * @param string $freeShippingImageUrl
     * @return $this
     */
    public function setFreeShippingImgPath($freeShippingImageUrl);
}