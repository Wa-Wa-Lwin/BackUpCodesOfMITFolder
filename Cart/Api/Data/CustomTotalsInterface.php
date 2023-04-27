<?php

namespace MIT\Cart\Api\Data;

use Magento\Quote\Api\Data\TotalsInterface;

interface CustomTotalsInterface extends TotalsInterface
{

    const KEY_CUSTOM_SHIPPING_ADDRESS = 'customshippingaddress';

    /**
     * set shipping address
     * @param \Magento\Quote\Api\Data\AddressInterface $shippingAddress
     * @return $this
     */
    public function setShippingAddress($shippingAddress);

    /**
     * get shipping address
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getShippingAddress();

    /**
     * Get totals by items
     *
     * @return \MIT\Cart\Api\Data\CustomTotalsItemInterface[]|null
     */
    public function getItems();

    /**
     * Set totals by items
     *
     * @param \MIT\Cart\Api\Data\CustomTotalsItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}
