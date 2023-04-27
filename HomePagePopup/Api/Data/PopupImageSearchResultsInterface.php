<?php


namespace MIT\HomePagePopup\Api\Data;

interface PopupImageSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Popup Image list.
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface[]
     */
    public function getItems();

    /**
     * Set Name list.
     * @param \MIT\HomePagePopup\Api\Data\PopupImageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
