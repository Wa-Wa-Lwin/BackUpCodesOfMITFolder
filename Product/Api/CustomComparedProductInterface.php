<?php

namespace MIT\Product\Api;

interface CustomComparedProductInterface
{

    /**
     * @param string $customerId
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function getComparedProductList($customerId);

    /**
     * @param string $productId
     * @param string $customerId
     * @return \Magento\Framework\DataObject
     */
    public function setComparedProduct($productId, $customerId);

    /**
     * @param string $id
     * @param string $customerId
     * @return bool
     */
    public function deleteComparedProductById($id, $customerId);
}
