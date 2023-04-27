<?php

namespace MIT\Delivery\Model\Carrier;

use Eadesigndev\RomCity\Api\Data\RomCityInterface;
use Eadesigndev\RomCity\Model\RomCity;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use MIT\Delivery\Api\Data\CustomDeliveryInterface;
use MIT\Delivery\Model\CustomDeliveryRepository;
use Eadesigndev\RomCity\Model\RomCityRepository;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use MIT\Delivery\Helper\Data;
use MIT\Delivery\Model\CustomDelivery;
use MIT\WeeklyPromo\Helper\PromoRetriever;

/**
 * Custom shipping model
 */
class Customshipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'customshipping';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    private $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @var CustomDeliveryRepository
     */
    private $_deliveryRepository;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $_sortOrderBuilder;

    /**
     * @var RomCityRepository
     */
    private $_cityRepository;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var Data
     */
    private $_helper;

    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory
     */
    public $trackFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    public $trackStatusFactory;

    /**
     * @var PromoRetriever
     */
    protected $promoRetriever;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \MIT\Delivery\Model\CustomDeliveryRepository $customRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Eadesigndev\RomCity\Model\RomCityRepository $cityRepository
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param Data $helper
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactor
     * @param PromoRetriever $promoRetriever
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        CustomDeliveryRepository $customRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        RomCityRepository $cityRepository,
        FilterGroupBuilder $filterGroupBuilder,
        Data $helper,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        PromoRetriever $promoRetriever,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->_deliveryRepository = $customRepository;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_sortOrderBuilder = $sortOrderBuilder;
        $this->_cityRepository = $cityRepository;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_helper = $helper;
        $this->_trackFactory = $trackFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        $this->promoRetriever = $promoRetriever;
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        $customShippingCost = 0;
        $customShippingCost = $this->calculateShipping($request);

        if ($customShippingCost == -1) {
            return false;
        }

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice($customShippingCost);
        $result->append($method);

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * get Tracking info
     * @param $tracking_number
     *
     * @return Status
     */
    public function getTrackingInfo($tracking_number)
    {
        $result = $this->_trackFactory->create();
        $tracking = $this->_trackStatusFactory->create();

        $tracking->setCarrier($this->_code);
        $tracking->setCarrierTitle($this->getConfigData('title'));
        $tracking->setTracking($tracking_number);
        $result->append($tracking);
        return $tracking;
    }

    /**
     * calculate custom shipping fees
     * @param RateRequest $request
     * @return int $customShippingCost
     */
    private function calculateShipping(RateRequest $request)
    {

        $customShippingCost = -1;
        $cityList = $this->getCityIdByCityName('eq', $request->getDestCity());
        /** @var RomCity $item */
        foreach ($cityList as $item) {
            if ($item->getEntityId() > 0) {
                $filters[] = $this->_filterBuilder
                    ->setConditionType('eq')
                    ->setField(CustomDeliveryInterface::REGION)
                    ->setValue(strval($request->getDestRegionId()))
                    ->create();
                $filters[] = $this->_filterBuilder
                    ->setConditionType('eq')
                    ->setField(CustomDeliveryInterface::CITY)
                    ->setValue(strval($item->getEntityId()))
                    ->create();

                $filters[] = $this->_filterBuilder
                    ->setConditionType('eq')
                    ->setField(CustomDeliveryInterface::DELIVERY_TYPE)
                    ->setValue(strval($this->_helper->getScopeConfig('carriers/' . $this->_code . '/keyword')))
                    ->create();

                $deliveryCostList = $this->getDeliveryCost($filters, 0, 0, 
                    [CustomDeliveryInterface::TOTAL, CustomDeliveryInterface::WEIGHT, CustomDeliveryInterface::ITEMS],
                    [ SortOrder::SORT_DESC, SortOrder::SORT_DESC, SortOrder::SORT_DESC]);

                $total = $request->getPackageValue();
                $weight = $request->getPackageWeight();
                $itemCount = $request->getPackageQty();

                // usort($deliveryCostList, fn ($a, $b) => strcmp($b->getTotal(), $a->getTotal()));

                /** @var CustomDelivery $item */
                foreach ($deliveryCostList as $item) {

                    if ($item->getWeight() > 0 && $item->getTotal() > 0 && $item->getItems() > 0) {
                        if ($weight >= $item->getWeight() && $total >= $item->getTotal() && $itemCount >= $item->getItems()) {
                            $customShippingCost = $item->getShipping();
                            break;
                        }
                    } else if ($item->getWeight() > 0 && $item->getTotal() > 0) {
                        if ($weight >= $item->getWeight() && $total >= $item->getTotal()) {
                            $customShippingCost = $item->getShipping();
                            break;
                        }
                    } else if ($item->getTotal() > 0 && $item->getItems() > 0) {
                        if ($total >= $item->getTotal() && $itemCount >= $item->getItems()) {
                            $customShippingCost = $item->getShipping();
                            break;
                        }
                    } else if ($item->getWeight() > 0 && $item->getItems() > 0) {
                        if ($weight >= $item->getWeight() && $itemCount >= $item->getItems()) {
                            $customShippingCost = $item->getShipping();
                            break;
                        }
                    } else if ($item->getWeight() > 0) {
                        if ($weight >= $item->getWeight()) {
                            $customShippingCost = $item->getShipping();
                            break;
                        }
                    } else if ($item->getTotal() > 0) {
                        if ($total >= $item->getTotal()) {
                            $customShippingCost = $item->getShipping();
                            break;
                        }
                    } else if ($item->getItems() > 0) {
                        if ($itemCount >= $item->getItems()) {
                            $customShippingCost = $item->getShipping();
                            break;
                        }
                    } else {
                        $customShippingCost = -1;
                    }
                }
            }
        }
        if ($this->isDeliveryCostFree($request) && $customShippingCost > - 1) {
            return 0;
        }
        return $customShippingCost;
    }

    /**
     * generate filter
     * @param array $filters
     * @param array $sortingField
     * @param int $pageSize
     * @param int $currentPage
     * @param array $sortingDir
     * @return \Magento\Framework\Api\SearchCriteriaBuilder _searchCriteriaBuilder
     */
    private function generateFilter(array $filters, array $sortingField, $pageSize, $currentPage, array $sortingDir)
    {
        $filterGroupList = [];
        foreach ($filters as $item) {
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($item)->create();
        }
        $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList);
        foreach($sortingField as $key => $val) {
            $this->_searchCriteriaBuilder->addSortOrder(
                $this->_sortOrderBuilder
                    ->setField($val)
                    ->setDirection($sortingDir[$key])
                    ->create()
            );
        }
        if ($pageSize > 0 && $currentPage > 0) {
            $this->_searchCriteriaBuilder->setPageSize($pageSize);
            $this->_searchCriteriaBuilder->setCurrentPage($currentPage);
        }
        return $this->_searchCriteriaBuilder;
    }

    /**
     * retrieve delivery cost from db
     * @param array $filters
     * @param int $pageSize
     * @param int $currentPage
     * @param string $sortingField
     * @param string @sortingDir
     * @return \Magento\Framework\Api\ExtensibleDataInterface[] items
     */
    private function getDeliveryCost(
        $filters,
        $pageSize = 0,
        $currentPage = 0,
        $sortingField = [ CustomDeliveryInterface::ID ],
        $sortingDir = [ SortOrder::SORT_ASC ]
    ) {

        $items = $this->_deliveryRepository->getList($this->generateFilter($filters, $sortingField, $pageSize, $currentPage, $sortingDir)->create());
        return $items->getItems();
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
    public function getCityIdByCityName(
        $condition,
        $val,
        $pageSize = 0,
        $currentPage = 0,
        $field = RomCityInterface::CITY_NAME,
        $sortingField = [ RomCityInterface::ENTITY_ID ],
        $sortingDir = [ SortOrder::SORT_DESC ]
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
     * add tracking available status
     * return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * check can delivery with free shipping
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return bool
     */
    public function isDeliveryCostFree($request) {
        $productIdList = [];
        $items = $request->getAllItems();
        $c = count($items);
        for ($i = 0; $i < $c; $i++) {
            if ($items[$i]->getProduct() instanceof \Magento\Catalog\Model\Product) {
                $productIdList[] = $items[$i]->getProduct()->getId();
            }
        }

        return $this->promoRetriever->isFreeShipping($productIdList);
    }
}
