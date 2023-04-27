<?php

namespace MIT\Cart\Api\Data;

use Magento\Quote\Api\Data\CartItemInterface;

interface CustomCartItemInterface extends CartItemInterface
{

    const KEY_PRODUCT_IMG_PATH = 'img_path';
    const KEY_SUBTOTAL = 'subtotal';
    const KEY_ATTRIBUTES = 'attributes';
    const KEY_PARENT_ID = 'parent_id';

    /**
     * set image path for product in cart
     * @param string $imgPath
     * @return $this
     */
    public function setImgPath($imgPath);

    /**
     * get image path for product in cart
     * @return string
     */
    public function getImgPath();

    /**
     * set subtotal for product in cart
     * @param float $subtotal
     * @return $this
     */
    public function setSubTotal($subtotal);

    /**
     * get subtotal for product in cart
     * @return float
     */
    public function getSubTotal();

    /**
     * set attributes for product in cart
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     */
    public function setAttributes($attributes);

    /**
     * get attributes for product in cart
     * @return \Magento\Framework\Api\AttributeInterface[]|[]
     */
    public function getAttributes();

    /**
     * set parent id of product if configurable product
     * if not, set self id
     * @param int $id
     * @return $this
     */
    public function setParentId($id);

    /**
     * get parent id of product if configurable product
     * if not, get self id
     * @return int
     */
    public function getParentId();
}

