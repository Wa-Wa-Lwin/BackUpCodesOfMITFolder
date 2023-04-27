<?php

namespace MIT\Cart\Api;

interface CustomCartTotalRepositoryInterface
{

    /**
     * Returns quote totals data for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \MIT\Cart\Api\Data\CustomTotalsInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId);

    /**
     * Returns quote totals data for a specified cart for guest.
     *
     * @param string $cartId The cart ID.
     * @return \MIT\Cart\Api\Data\CustomTotalsInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getTotalForGuest($cartId);
}
