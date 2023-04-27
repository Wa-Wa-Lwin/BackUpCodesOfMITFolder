<?php

namespace MIT\Product\Api;

interface CustomCollectionsInterface
{

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \ColMage\MyCollection\Api\Data\ImageCollectionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
