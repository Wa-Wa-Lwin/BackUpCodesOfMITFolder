<?php

namespace MIT\Product\Api;

interface CustomProductInterface
{

    /**
     * get product by id including review/rating
     * @param int $id
     * @return \MIT\Product\Api\Data\CustomProductManagementInterface
     */
    public function getProductDetailBySku($id);

    /**
     * Get product list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param bool $customSort
     * @param string $sortQuery
     * @return \MIT\Product\Api\Data\CustomProductSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $customSort = false, $sortQuery = '');
}

