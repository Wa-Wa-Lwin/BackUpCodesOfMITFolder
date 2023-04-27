<?php


namespace MIT\Coupon\Controller\Index;

use Eadesigndev\RomCity\Api\Data\RomCityInterface;
use Eadesigndev\RomCity\Model\RomCity;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Eadesigndev\RomCity\Model\RomCityRepository;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Store\Model\StoreManagerInterface;
use MIT\Delivery\Api\Data\CustomDeliveryInterface;
use MIT\Delivery\Model\CustomDelivery;

class Cost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

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
     * @var \MIT\Delivery\Model\CustomDeliveryRepository
     */
    private $customDeliveryRepository;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $currencyHelper;

    /**
     * @var \MIT\Delivery\Helper\Data
     */
    private $deliveryHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        RomCityRepository $cityRepository,
        FilterGroupBuilder $filterGroupBuilder,
        \MIT\Delivery\Model\CustomDeliveryRepository $customDeliveryRepository,
        \Magento\Framework\Pricing\Helper\Data $currencyHelper,
        \MIT\Delivery\Helper\Data $deliveryHelper,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->checkoutSession = $checkoutSession;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_sortOrderBuilder = $sortOrderBuilder;
        $this->_cityRepository = $cityRepository;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->customDeliveryRepository = $customDeliveryRepository;
        $this->currencyHelper = $currencyHelper;
        $this->deliveryHelper = $deliveryHelper;
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $city = $this->getRequest()->getParam('cit');
            $regionId = $this->getRequest()->getParam('reg');
            return $this->jsonResponse($this->generateMessage($city, $regionId));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    /**
     * generate message
     * @param string $isShippingStep
     * @return string
     */
    private function generateMessage($city, $regionId)
    {
        $quote = $this->checkoutSession->getQuote();
        if ($city && $regionId) {
            return $this->getRequiredAmtMessage($quote->getSubtotal(), -1, $city, $regionId);
        } else {
            $shippingCarrierCode = explode('_', $quote->getShippingAddress()->getShippingMethod())[0];
            $code = $this->deliveryHelper->getScopeConfig('carriers/' . $shippingCarrierCode . '/keyword');
            if ($quote->getShippingAddress()->getShippingMethod() && $quote->getShippingAddress()->getShippingMethod() != 'payatstore_payatstore') {
                if ($quote->getShippingAddress()->getShippingAmount() == 0) {
                    if ($this->isMMLocale()) {
                        return 'အခမဲ့ပို့ဆောင်ခြင်း';
                    } else {
                        return 'Free Delivery';
                    }
                } else {
                    return $this->getRequiredAmtMessage(
                        $quote->getSubtotal(),
                        $code,
                        $quote->getShippingAddress()->getCity(),
                        $quote->getShippingAddress()->getRegionId()
                    );
                }
            }
        }
        return '';
    }

    /**
     * get require Amount message
     * @param float $subTotal
     * @param int $code
     * @param string $city
     * @return string
     */
    private function getRequiredAmtMessage($subTotal, $code, $city, $regionId)
    {
        $requiredAmt = $this->calculateRequirementAmt(
            $city,
            $regionId,
            $subTotal,
            $code
        );
        if ($requiredAmt > 0) {
            if ($this->isMMLocale()) {
                if ($code > -1) {
                    return sprintf(
                        'အခမဲ့ ပို့ဆောင်ပေးခြင်းကို ရရှိရန်အတွက် %s တန်ဖိုးရှိ ပစ္စည်းများ ထပ်ဖြည့်ပါ',
                        $this->currencyHelper->currency($requiredAmt, true, false)
                    );
                } else if ($requiredAmt > $subTotal) {
                    return sprintf(
                        'အခမဲ့ ပို့ဆောင်မှုရရှိရန် အတွက် ပစ္စည်းတန်ဖိုး %s နှင့်အထက်ဝယ်ယူပါ',
                        $this->currencyHelper->currency($requiredAmt, true, false)
                    );
                }
            } else {
                if ($code > -1) {
                    return sprintf(
                        'Add %s worth of items to get FREE delivery',
                        $this->currencyHelper->currency($requiredAmt, true, false)
                    );
                } else if ($requiredAmt > $subTotal) {
                    return sprintf(
                        'Shop items worth %s or more for FREE Delivery',
                        $this->currencyHelper->currency($requiredAmt, true, false)
                    );
                }
            }
        }
        return '';
    }

    /**
     * check current locale is mm
     * @return bool
     */
    private function isMMLocale()
    {
        $code = $this->storeManagerInterface->getStore()->getCode();
        return strtolower($code) == 'mm';
    }

    /**
     * calculate custom shipping fees
     * @param string $city
     * @param int $regionId
     * @param float $subTotal
     * @param int $code
     * @return int $requiredCost
     */
    private function calculateRequirementAmt($city, $regionId, $subTotal, $code)
    {
        $requiredCost = 0;
        $cityList = $this->getCityIdByCityName('eq', $city);
        /** @var RomCity $item */
        foreach ($cityList as $item) {
            if ($item->getEntityId() > 0) {
                $filters[] = $this->_filterBuilder
                    ->setConditionType('eq')
                    ->setField(CustomDeliveryInterface::REGION)
                    ->setValue(strval($regionId))
                    ->create();
                $filters[] = $this->_filterBuilder
                    ->setConditionType('eq')
                    ->setField(CustomDeliveryInterface::CITY)
                    ->setValue(strval($item->getEntityId()))
                    ->create();
                $filters[] = $this->_filterBuilder
                    ->setConditionType('eq')
                    ->setField(CustomDeliveryInterface::SHIPPING)
                    ->setValue('0')
                    ->create();

                if ($code > -1) {
                    $filters[] = $this->_filterBuilder
                        ->setConditionType('eq')
                        ->setField(CustomDeliveryInterface::DELIVERY_TYPE)
                        ->setValue(strval($code))
                        ->create();
                } else {
                    $codeList = $this->getActiveCustomDeliveryCode();
                    if (count($codeList) > 0) {
                        $filters[] = $this->_filterBuilder
                            ->setConditionType('in')
                            ->setField(CustomDeliveryInterface::DELIVERY_TYPE)
                            ->setValue($codeList)
                            ->create();
                    }
                }

                /** @var CustomDelivery[] $deliveryCostList */
                $deliveryCostList = $this->getDeliveryCost($filters);

                /** @var CustomDelivery $item */
                foreach ($deliveryCostList as $item) {
                    if ($item->getWeight() == 0 && $item->getItems() == 0 && $item->getShipping() == 0) {
                        if ($code == -1) {
                            return $item->getTotal();
                        }
                        if ($subTotal >= $item->getTotal()) {
                            return $requiredCost;
                        } else if ($item->getTotal() > $subTotal) {
                            return $item->getTotal() - $subTotal;
                        }
                    }
                }
            }
        }
        return $requiredCost;
    }

    /**
     * get active custom delivery code list
     * @return array
     */
    private function getActiveCustomDeliveryCode()
    {
        $codeList = [];
        $shippingCodeArr = ['customshipping', 'customshippingone', 'customshippingtwo', 'customshippingthree', 'customshippingfour'];
        foreach ($shippingCodeArr as $shipping) {
            if ($this->deliveryHelper->getScopeConfig('carriers/' . $shipping . '/active')) {
                $codeList[] = $this->deliveryHelper->getScopeConfig('carriers/' . $shipping . '/keyword');
            }
        }
        return $codeList;
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
        $sortingField = CustomDeliveryInterface::TOTAL,
        $sortingDir = SortOrder::SORT_ASC
    ) {
        $items = $this->customDeliveryRepository->getList($this->generateFilter($filters, $sortingField, $pageSize, $currentPage, $sortingDir)->create());
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

