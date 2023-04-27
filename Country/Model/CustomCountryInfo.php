<?php

namespace MIT\Country\Model;

use Magento\Directory\Model\Data\CountryInformation;
use MIT\Country\Api\Data\CustomCountryInfoInterface;

class CustomCountryInfo extends CountryInformation implements CustomCountryInfoInterface
{
}
