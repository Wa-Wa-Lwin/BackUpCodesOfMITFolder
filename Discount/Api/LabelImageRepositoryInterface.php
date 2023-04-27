<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface LabelImageRepositoryInterface
{

    /**
     * Save label_image
     * @param \MIT\Discount\Api\Data\LabelImageInterface $labelImage
     * @return \MIT\Discount\Api\Data\LabelImageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \MIT\Discount\Api\Data\LabelImageInterface $labelImage
    );

    /**
     * Retrieve label_image
     * @param string $labelImageId
     * @return \MIT\Discount\Api\Data\LabelImageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($labelImageId);

    /**
     * Retrieve label_image matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MIT\Discount\Api\Data\LabelImageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete label_image
     * @param \MIT\Discount\Api\Data\LabelImageInterface $labelImage
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \MIT\Discount\Api\Data\LabelImageInterface $labelImage
    );

    /**
     * Delete label_image by ID
     * @param string $labelImageId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($labelImageId);
}