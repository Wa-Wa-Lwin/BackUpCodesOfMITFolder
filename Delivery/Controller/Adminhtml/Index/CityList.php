<?php

namespace MIT\Delivery\Controller\Adminhtml\Index;

use Eadesigndev\RomCity\Api\Data\RomCityInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Eadesigndev\RomCity\Model\RomCityRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use MIT\Delivery\Model\Carrier\Customshipping;
use Eadesigndev\RomCity\Model\RomCity;

class CityList extends Action
{
    private $resultJsonFactory;
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

    private $_customShipping;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        RomCityRepository $cityRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Customshipping $customshipping
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_cityRepository = $cityRepository;
        $this->_customShipping = $customshipping;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $param = $this->getRequest()->getParam('param');
        $type = $this->getRequest()->getParam('type');
        if ($param && $type == 'region') {
            return $this->generateCityOptions('eq', $param, RomCityInterface::REGION_ID);
        } else if ($param && $type == 'city') {

            $regionData = $this->_customShipping->getCityIdByCityName('eq', $param, 0, 0, RomCityInterface::ENTITY_ID);

            /** @var RomCity $region */
            foreach ($regionData as $region) {
                if ($region->getRegionId()) {
                    return $this->generateCityOptions('eq', $region->getRegionId(), RomCityInterface::REGION_ID);
                }
            }
        }
        return $resultJson->setData(['res' => ""]);
    }

    private function generateCityOptions($condition, $val, $checkVar)
    {
        $resultJson = $this->resultJsonFactory->create();
        $cityList = $this->_customShipping->getCityIdByCityName($condition, $val, 0, 0, $checkVar);
        $resData = "'<option value=''> Please select a city. </option>'";
        /** @var RomCity $item */
        foreach ($cityList as $item) {
            $resData .= "'<option value='" . $item->getEntityId() . "'>" . $item->getCityName() . "</option>'";
        }
        return $resultJson->setData(['res' => $resData]);
    }
}
