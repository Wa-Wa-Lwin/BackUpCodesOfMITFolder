<?php

namespace MIT\Product\Api;

interface CustomNewProductInterface {

    /**
     * Get product list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MIT\Product\Api\Data\CustomProductSearchResultsInterface
     */
    public function getNewProductList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}