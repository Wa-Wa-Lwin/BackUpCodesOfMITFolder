<?php

namespace MIT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\Product\Api\Data\ReviewManagementInterface;

class ReviewManagement extends AbstractExtensibleModel implements ReviewManagementInterface
{
    /**
     * @inheritdoc
     */
    public function getReviewId()
    {
        return $this->getData(self::REVIEW_ID);
    }

    /**
     * @inheritdoc
     */
    public function setReviewId($id)
    {
        return $this->setData(self::REVIEW_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::REVIEW_CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdDate)
    {
        return $this->setData(self::REVIEW_CREATED_AT, $createdDate);
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->getData(self::REVIEW_PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($productId)
    {
        return $this->setData(self::REVIEW_PRODUCT_ID, $productId);
    }

    /**
     * @inheritdoc
     */
    public function getProductName()
    {
        return $this->getData(self::REVIEW_PRODUCT_Name);
    }

    /**
     * @inheritdoc
     */
    public function setProductName($productName)
    {
        return $this->setData(self::REVIEW_PRODUCT_Name, $productName);
    }

    /**
     * @inheritdoc
     */
    public function getProductImgPath()
    {
        return $this->getData(self::REVIEW_PRODUCT_IMG);
    }

    /**
     * @inheritdoc
     */
    public function setProductImgPath($imgPath)
    {
        return $this->setData(self::REVIEW_PRODUCT_IMG, $imgPath);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getData(self::REVIEW_TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::REVIEW_TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function getSummary()
    {
        return $this->getData(self::REVIEW_SUMMARY);
    }

    /**
     * @inheritdoc
     */
    public function setSummary($summary)
    {
        return $this->setData(self::REVIEW_SUMMARY, $summary);
    }

    /**
     * @inheritdoc
     */
    public function getRating()
    {
        return $this->getData(self::REVIEW_RATING);
    }

    /**
     * @inheritdoc
     */
    public function setRating(array $rating = [])
    {
        return $this->setData(self::REVIEW_RATING, $rating);
    }

    /**
     * @inheritdoc
     */
    public function getNoOfReviews()
    {
        return $this->getData(self::REVIEW_COUNTS);
    }

    /**
     * @inheritdoc
     */
    public function setNoOfReviews($reviewsCount)
    {
        return $this->setData(self::REVIEW_COUNTS, $reviewsCount);
    }

    /**
     * @inheritdoc
     */
    public function getAverageRating()
    {
        return $this->getData(self::REVIEW_AVERAGE_RATING);
    }

    /**
     * @inheritdoc
     */
    public function setAverageRating($averageRating)
    {
        return $this->setData(self::REVIEW_AVERAGE_RATING, $averageRating);
    }
}
