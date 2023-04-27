<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface LabelRepositoryInterface
{

    /**
     * Save label
     * @param \MIT\Discount\Api\Data\LabelInterface $label
     * @return \MIT\Discount\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \MIT\Discount\Api\Data\LabelInterface $label
    );

    /**
     * Retrieve label
     * @param string $labelId
     * @return \MIT\Discount\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($labelId);

    /**
     * Retrieve label matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MIT\Discount\Api\Data\LabelSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete label
     * @param \MIT\Discount\Api\Data\LabelInterface $label
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \MIT\Discount\Api\Data\LabelInterface $label
    );

    /**
     * Delete label by ID
     * @param string $labelId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($labelId);
}

