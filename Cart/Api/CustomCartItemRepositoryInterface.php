<?php

namespace MIT\Cart\Api;

interface CustomCartItemRepositoryInterface
{

    /**
     * Lists items that are assigned to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \MIT\Cart\Api\Data\CustomCartItemInterface[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getList($cartId);

    /**
     * Lists items that are assigned to a specified cart.
     *
     * @param string $cartId The cart ID.
     * @return \MIT\Cart\Api\Data\CustomCartItemInterface[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getListForGuest($cartId);

    /**
     * update item by item id
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem
     * @return \Magento\Quote\Api\Data\CartItemInterface
     */
    public function updateItemByItemId(\Magento\Quote\Api\Data\CartItemInterface $cartItem);

    /**
     * update item by item id for guest
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem
     * @return \Magento\Quote\Api\Data\CartItemInterface
     */
    public function updateGuestItemByItemId(\Magento\Quote\Api\Data\CartItemInterface $cartItem);
}
