<?php

namespace MIT\Product\Api;

interface CustomRatingAndReviewInterface
{

    /**
     * get rating list
     * @return Magento\Review\Model\ResourceModel\Rating\Collection
     */
    public function getRatingList();

    /**
     * submit customer product review
     * @param int $customerId
     * @return \Magento\Framework\DataObject
     */
    public function submitReview($customerId);

    /**
     * get product review list by customer id
     * @param int $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MIT\Product\Api\Data\ReviewManagementSearchResultsInterface
     */
    public function getProductReviewListByCustomerId($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * get product review detail by id and customer id
     * @param int $customerId
     * @param int $reviewId
     * @return \MIT\Product\Api\Data\ReviewManagementInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getReviewByIdAndCustomerId($customerId, $reviewId);
}

