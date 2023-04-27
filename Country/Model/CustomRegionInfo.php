<?php

namespace MIT\Country\Model;

use Eadesigndev\RomCity\Api\Data\RomCityInterface;
use Eadesigndev\RomCity\Model\RomCity;
use Eadesigndev\RomCity\Model\RomCityRepository;
use Magento\Directory\Model\Data\RegionInformation;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use MIT\Country\Api\Data\CustomRegionInfoInterface;

class CustomRegionInfo extends RegionInformation implements CustomRegionInfoInterface
{

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var RomCityRepository
     */
    private $_cityRepository;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;
       
    /**
    * @var \MIT\Country\Model\CustomCityList
    */
    private $cityListFactory;

    /**
     * @var SortOrderBuilder
     */
    private $_sortOrderBuilder;

    protected $_cityList = [];

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    protected $extensionFactory;

    /**
     * @var AttributeValueFactory
     */
    protected $attributeValueFactory;

    public function __construct(
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        RomCityRepository $cityRepository,
        FilterGroupBuilder $filterGroupBuilder,
        array $cityList = [],
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \MIT\Country\Model\CustomCityListFactory $cityListFactory,
        AttributeValueFactory $attributeValueFactory,
        $data = []
    ) {
        parent::__construct($extensionFactory, $attributeValueFactory, $data);
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_sortOrderBuilder = $sortOrderBuilder;
        $this->_cityRepository = $cityRepository;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_cityList = $cityList;
        $this->cityListFactory = $cityListFactory;
    }

    public function setCityList(array $cityList=null)
    {
        $custom_arr = [];
        $customCityList = $this->getCityByRegionId('eq', $this->getId(), 0, 0, RomCityInterface::REGION_ID);
        /** @var RomCity $item */
        foreach ($customCityList as $item) {
            $custom_arr[] = $item->getCityName();
        }
        $this->_cityList = $custom_arr;
    }

    public function getCityList()
    {
        $custom_arr = [];
        $customCityList = $this->getCityByRegionId('eq', $this->getId(), 0, 0, RomCityInterface::REGION_ID);
        /** @var RomCity $item */
        foreach ($customCityList as $item) {
            $cityListFactory = $this->cityListFactory->create();
            $cityListFactory->setId($item->getId());
            $cityListFactory->setCity($item->getCityName());
            $cityListFactory->setPostCode($item->getPostalCode());
            $custom_arr[] = $cityListFactory;
        }
        return $custom_arr;
    }


    /**
     * retrieve city by cityname
     * @param string $condition
     * @param string $val
     * @param int $pageSize
     * @param int $currentPage
     * @param string $field
     * @param string $sortingField
     * @param string @sortingDir
     * @return \Magento\Framework\Api\ExtensibleDataInterface[] items
     */
    public function getCityByRegionId(
        $condition,
        $val,
        $pageSize = 0,
        $currentPage = 0,
        $field = RomCityInterface::CITY_NAME,
        $sortingField = RomCityInterface::ENTITY_ID,
        $sortingDir = SortOrder::SORT_DESC
    ) {
        $filters[] = $this->_filterBuilder
            ->setConditionType($condition)
            ->setField($field)
            ->setValue($val)
            ->create();
        $items = $this->_cityRepository->getListForDelivery($this->generateFilter($filters, $sortingField, $pageSize, $currentPage, $sortingDir)->create());
        return $items->getItems();
    }

    /**
     * generate filter
     * @param array $filters
     * @param string $sortingField
     * @param int $pageSize
     * @param int $currentPage
     * @param string @sortingDir
     * @return \Magento\Framework\Api\SearchCriteriaBuilder _searchCriteriaBuilder
     */
    private function generateFilter(array $filters, $sortingField, $pageSize, $currentPage, $sortingDir = SortOrder::SORT_DESC)
    {
        $filterGroupList = [];
        foreach ($filters as $item) {
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($item)->create();
        }
        $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList);
        $this->_searchCriteriaBuilder->addSortOrder(
            $this->_sortOrderBuilder
                ->setField($sortingField)
                ->setDirection($sortingDir)
                ->create()
        );
        if ($pageSize > 0 && $currentPage > 0) {
            $this->_searchCriteriaBuilder->setPageSize($pageSize);
            $this->_searchCriteriaBuilder->setCurrentPage($currentPage);
        }
        return $this->_searchCriteriaBuilder;
    }
}
