<?php

namespace MIT\Product\Api\Data;

interface ReviewManagementInterface
{
    const REVIEW_ID = 'id';
    const REVIEW_CREATED_AT = 'created_at';
    const REVIEW_PRODUCT_ID = 'product_id';
    const REVIEW_PRODUCT_IMG = 'product_img';
    const REVIEW_PRODUCT_Name = 'product_name';
    const REVIEW_TITLE = 'title';
    const REVIEW_SUMMARY = 'summary';
    const REVIEW_RATING = 'rating';
    const REVIEW_COUNTS = 'review_counts';
    const REVIEW_AVERAGE_RATING = 'average_rating';

    /**
     * get review id
     * @return int
     */
    public function getReviewId();

    /**
     * set review id
     * @param int $id
     * @return $this
     */
    public function setReviewId($id);

    /**
     * get created date for review
     * @return string
     */
    public function getCreatedAt();

    /**
     * set created date for review
     * @param string $createdDate
     * @return $this
     */
    public function setCreatedAt($createdDate);

    /**
     * get product id
     * @return int
     */
    public function getProductId();

    /**
     * set product id
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * get product img
     * @return string
     */
    public function getProductImgPath();

    /**
     * set product img
     * @param string $imgPath
     * @return $this
     */
    public function setProductImgPath($imgPath);

    /**
     * get product name
     * @return string
     */
    public function getProductName();

    /**
     * set product name
     * @param string $productName
     * @return $this
     */
    public function setProductName($productName);

    /**
     * get review title
     * @return string
     */
    public function getTitle();

    /**
     * set review title
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * get review summary
     * @return string
     */
    public function getSummary();

    /**
     * set review summary
     * @param string $summary
     * @return $this
     */
    public function setSummary($summary);

    /**
     * get rating
     * @return \MIT\Product\Api\Data\CustomKeyValInterface[]
     */
    public function getRating();

    /**
     * set rating
     * @param \MIT\Product\Api\Data\CustomKeyValInterface[] $rating
     * @return $this
     */
    public function setRating(array $rating);

    /**
     * get number of reviews
     * @return int
     */
    public function getNoOfReviews();

    /**
     * set number of reviews
     * @param int $reviewsCount
     * @return $this
     */
    public function setNoOfReviews($reviewsCount);

    /**
     * get average rating
     * @return int
     */
    public function getAverageRating();

    /**
     * set average rating
     * @param int $averageRating
     * @return $this
     */
    public function setAverageRating($averageRating);
}
