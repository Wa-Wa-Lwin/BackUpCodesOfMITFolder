<?php

namespace MIT\Country\Api;

interface CustomCountryInfoAcquireInterface
{

    /**
     * Get country and region information for the store.
     *
     * @param string $countryId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \MIT\Country\Api\Data\CustomCountryInfoInterface
     */
    public function getCountryById($countryId);
}
