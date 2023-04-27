<?php

namespace MIT\Product\Api;

interface CustomRecentlyViewedProductInterface
{

    /**
     * @param string $customerId
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function getRecentlyViewedProductList($customerId);

    /**
     * @param string $productId
     * @param string $customerId
     * @return \Magento\Framework\DataObject
     */
    public function setRecentlyViewedProduct($productId, $customerId);
}
