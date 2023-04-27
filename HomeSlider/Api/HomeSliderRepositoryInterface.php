<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\HomeSlider\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface HomeSliderRepositoryInterface
{

    /**
     * Save HomeSlider
     * @param \MIT\HomeSlider\Api\Data\HomeSliderInterface $homeSlider
     * @return \MIT\HomeSlider\Api\Data\HomeSliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \MIT\HomeSlider\Api\Data\HomeSliderInterface $homeSlider
    );

    /**
     * Retrieve HomeSlider
     * @param string $homesliderId
     * @return \MIT\HomeSlider\Api\Data\HomeSliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($homesliderId);

    /**
     * Retrieve HomeSlider matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MIT\HomeSlider\Api\Data\HomeSliderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete HomeSlider
     * @param \MIT\HomeSlider\Api\Data\HomeSliderInterface $homeSlider
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \MIT\HomeSlider\Api\Data\HomeSliderInterface $homeSlider
    );

    /**
     * Delete HomeSlider by ID
     * @param string $homesliderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($homesliderId);
}

