<?php

namespace MIT\Country\Api\Data;

use Magento\Directory\Api\Data\CountryInformationInterface;

interface CustomCountryInfoInterface extends CountryInformationInterface
{

    /**
     * Get the available regions for the store.
     *
     * @return \MIT\Country\Api\Data\CustomRegionInfoInterface[]|null
     */
    public function getAvailableRegions();

    /**
     * Set the available regions for the store
     *
     * @param \MIT\Country\Api\Data\CustomRegionInfoInterface[] $availableRegions
     * @return $this
     */
    public function setAvailableRegions($availableRegions);
}
