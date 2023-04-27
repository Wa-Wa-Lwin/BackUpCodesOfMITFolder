<?php

namespace MIT\Search\Api;

interface SearchInterface
{

    /**
     * make full text search and return product list
     * @param \Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria
     * @return \MIT\Product\Api\Data\CustomProductSearchResultsInterface
     */
    public function search(\Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria);
}
