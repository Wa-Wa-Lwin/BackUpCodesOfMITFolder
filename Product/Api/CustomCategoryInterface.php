<?php

namespace MIT\Product\Api;

interface CustomCategoryInterface
{

    /**
     * @param int $id
     * @return \MIT\Product\Api\Data\CustomCategoryManagementInterface
     */
    public function getCategoryById($id);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\CategorySearchResultsInterface
     */
    public function getCategoryListForHomePage(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}

