<?php

namespace MIT\Product\Api;

interface CustomBestSellerInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \MIT\Product\Api\Data\CustomProductSearchResultsInterface
     */
    public function getBestSellerProductList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}

