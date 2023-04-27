<?php

namespace MIT\Country\Model\Api;

use Magento\Directory\Model\CountryInformationAcquirer;
use Magento\Framework\Exception\NoSuchEntityException;
use MIT\Country\Api\CustomCountryInfoAcquireInterface;

class CustomCountryInfoAcquire implements CustomCountryInfoAcquireInterface
{


    protected $countryInformationFactory;


    protected $regionInformationFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \MIT\Country\Model\CustomCountryInfoFactory $countryInformationFactory
     * @param \MIT\Country\Model\CustomRegionInfoFactory $regionInformationFactory
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \MIT\Country\Model\CustomCountryInfoFactory $countryInformationFactory,
        \MIT\Country\Model\CustomRegionInfoFactory $regionInformationFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        //parent::__construct($countryInformationFactory, $regionInformationFactory, $directoryHelper, $scopeConfig, $storeManager);
        $this->countryInformationFactory = $countryInformationFactory;
        $this->regionInformationFactory = $regionInformationFactory;
        $this->directoryHelper = $directoryHelper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }


    public function getCountryById($countryId)
    {
        $store = $this->storeManager->getStore();
        $storeLocale = $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store->getCode()
        );

        $countriesCollection = $this->directoryHelper->getCountryCollection($store)->load();
        $regions = $this->directoryHelper->getRegionData();
        $country = $countriesCollection->getItemById($countryId);

        if (!$country) {
            throw new NoSuchEntityException(
                __(
                    "The country isn't available."
                )
            );
        }
        $countryInfo = $this->setCountryInfo($country, $regions, $storeLocale);

        return $countryInfo;
    }

    /**
     * Creates and initializes the information for \Magento\Directory\Model\Data\CountryInformation
     *
     * @param \Magento\Directory\Model\ResourceModel\Country $country
     * @param array $regions
     * @param string $storeLocale
     * @return \Magento\Directory\Model\Data\CountryInformation
     */
    protected function setCountryInfo($country, $regions, $storeLocale)
    {
        $countryId = $country->getCountryId();
        $countryInfo = $this->countryInformationFactory->create();
        $countryInfo->setId($countryId);
        $countryInfo->setTwoLetterAbbreviation($country->getData('iso2_code'));
        $countryInfo->setThreeLetterAbbreviation($country->getData('iso3_code'));
        $countryInfo->setFullNameLocale($country->getName($storeLocale));
        $countryInfo->setFullNameEnglish($country->getName('en_US'));

        if (array_key_exists($countryId, $regions)) {
            $regionsInfo = [];
            foreach ($regions[$countryId] as $id => $regionData) {
                $regionInfo = $this->regionInformationFactory->create();
                $regionInfo->setId($id);
                $regionInfo->setCode($regionData['code']);
                $regionInfo->setName($regionData['name']);
                $regionsInfo[] = $regionInfo;
            }
            $countryInfo->setAvailableRegions($regionsInfo);
        }

        return $countryInfo;
    }
}
