<?php

namespace MIT\Checkout\Model;

use Magento\Checkout\Model\Cart as ModelCart;

class Cart extends ModelCart
{

    /**
     * Update cart items information
     *
     * @param  array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function updateItems($data)
    {
        $infoDataObject = new \Magento\Framework\DataObject($data);
        $this->_eventManager->dispatch(
            'checkout_cart_update_items_before',
            ['cart' => $this, 'info' => $infoDataObject]
        );

        $qtyRecalculatedFlag = false;
        foreach ($data as $itemId => $itemInfo) {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || isset($itemInfo['qty']) && $itemInfo['qty'] == '0') {
                $this->removeItem($itemId);
                continue;
            }

            $qty = isset($itemInfo['qty']) ? (float)$itemInfo['qty'] : false;
            if ($qty > 0) {
                $item->clearMessage();
                $item->setHasError(false);
                $item->setQty($qty);

                if ($item->getHasError()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
                }

                if (isset($itemInfo['before_suggest_qty']) && $itemInfo['before_suggest_qty'] != $qty) {
                    $qtyRecalculatedFlag = true;
                    $this->messageManager->addNoticeMessage(
                        __('Quantity was recalculated from %1 to %2', $itemInfo['before_suggest_qty'], $qty),
                        'quote_item' . $item->getId()
                    );
                }
            }
        }

        if ($qtyRecalculatedFlag) {
            $this->messageManager->addNoticeMessage(
                __('We adjusted product quantities to fit the required increments.')
            );
        }

        $this->_eventManager->dispatch(
            'checkout_cart_update_items_after',
            ['cart' => $this, 'info' => $infoDataObject]
        );

        return $this;
    }

        /**
     * Update item in shopping cart (quote)
     * $requestInfo - either qty (int) or buyRequest in form of array or \Magento\Framework\DataObject
     * $updatingParams - information on how to perform update, passed to Quote->updateItem() method
     *
     * @param int $itemId
     * @param int|array|\Magento\Framework\DataObject $requestInfo
     * @param null|array|\Magento\Framework\DataObject $updatingParams
     * @return \Magento\Quote\Model\Quote\Item|string
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see \Magento\Quote\Model\Quote::updateItem()
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateItem($itemId, $requestInfo = null, $updatingParams = null)
    {
        $product = null;
        $productId = null;

        try {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This quote item does not exist.'));
            }
            $productId = $item->getProduct()->getId();
            $product = $this->_getProduct($productId);
            $request = $this->_getProductRequest($requestInfo);

            if ($productId) {
                $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
                $minimumQty = $stockItem->getMinSaleQty();
                // If product was not found in cart and there is set minimal qty for it
                if ($minimumQty
                    && $minimumQty > 0
                    && !$request->getQty()
                    && !$this->getQuote()->hasProductId($productId)
                ) {
                    $request->setQty($minimumQty);
                }
            }

            $result = $this->getQuote()->updateItem($itemId, $request, $updatingParams);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_checkoutSession->setUseNotice(false);
            $result = $e->getMessage();
        }

        /**
         * We can get string if updating process had some errors
         */
        if (is_string($result)) {
            if ($this->_checkoutSession->getUseNotice() === null) {
                $this->_checkoutSession->setUseNotice(true);
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($result));
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_update_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $result;
    }
}
