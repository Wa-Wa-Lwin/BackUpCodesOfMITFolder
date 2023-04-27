<?php

namespace MIT\Delivery\Model;

use Eadesigndev\RomCity\Model\RomCityRepository;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CityDropDown implements OptionSourceInterface
{

    /** @var SearchCriteriaBuilder  */
    private $searchCriteria;

    /** @var RomCityRepository  */
    private $romCityRepository;

    public function __construct(
        \Magento\Directory\Model\Country $country,
        SearchCriteriaBuilder $searchCriteria,
        RomCityRepository $romCityRepository
    ) {
        $this->country = $country;
        $this->searchCriteria = $searchCriteria;
        $this->romCityRepository = $romCityRepository;
    }

    public function toOptionArray()
    {
        return $this->getOptions();
    }

    protected function getOptions()
    {
        $res = [];
        $searchCriteriaBuilder = $this->searchCriteria;
        $searchCriteria = $searchCriteriaBuilder->create();

        $citiesList = $this->romCityRepository->getList($searchCriteria);
        $items = $citiesList->getItems();
        /** @var RomCity $item */
        foreach ($items as $item) {
            $res[] = array('label' => $item->getCityName(), 'value' => $item->getEntityId());
        }

        if (count($res) > 0) {
            array_unshift(
                $res,
                ['title' => '', 'value' => '', 'label' => __('Please select a city.')]
            );
        }
        return $res;
    }
}
