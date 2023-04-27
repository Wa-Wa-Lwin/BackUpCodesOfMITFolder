<?php

namespace MIT\HomePagePopup\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PopupImageRepositoryInterface
{

    /**
     * Save PopupImage
     * @param \MIT\HomePagePopup\Api\Data\PopupImageInterface $popupImage
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \MIT\HomePagePopup\Api\Data\PopupImageInterface $popupImage
    );

    /**
     * Retrieve popupImage
     * @param string $popupImageId
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($popupImageId);

    /**
     * Retrieve popupImage matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MIT\HomePagePopup\Api\Data\PopupImageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PopupImage
     * @param \MIT\HomePagePopup\Api\Data\PopupImageInterface $popupImage
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \MIT\HomePagePopup\Api\Data\PopupImageInterface $popupImage
    );

    /**
     * Delete PopupImage by ID
     * @param string $popupImageId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($popupImageId);

    /**
     * get popup image by image type
     * @param int $type
     * @return \MIT\HomePagePopup\Api\Data\PopupImageManagementInterface
     */
    public function getPopupImageByType($type);
}
