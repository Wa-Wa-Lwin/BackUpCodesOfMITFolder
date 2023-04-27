<?php

namespace MIT\Product\Api;

use MIT\Product\Api\Data\DownloadableProductSearchResultInterface;

interface CustomerDownloadableProductInterface
{

    /**
     * get downloable product list by customerId
     * @param int $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return DownloadableProductSearchResultInterface
     */
    public function getDownloadableProductList($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
