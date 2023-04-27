<?php

namespace MIT\Cart\Model\Cart;

use Magento\Quote\Model\Cart\Totals;
use MIT\Cart\Api\Data\CustomTotalsInterface;

class CustomTotals extends Totals implements CustomTotalsInterface
{
    /**
     * @inheritdoc
     */
    public function setShippingAddress($shippingAddress)
    {
        return $this->setData(self::KEY_CUSTOM_SHIPPING_ADDRESS, $shippingAddress);
    }

    /**
     * @inheritdoc
     */
    public function getShippingAddress()
    {
        return $this->getData(self::KEY_CUSTOM_SHIPPING_ADDRESS);
    }

    /**
     * Get totals by items
     *
     * @return \MIT\Cart\Api\Data\CustomTotalsItemInterface[]|null
     */
    public function getItems()
    {
        return $this->getData(self::KEY_ITEMS);
    }

    /**
     * Get totals by items
     *
     * @param \MIT\Cart\Api\Data\CustomTotalsItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }
}
