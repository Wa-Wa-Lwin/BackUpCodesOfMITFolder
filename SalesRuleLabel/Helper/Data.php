<?php

namespace MIT\SalesRuleLabel\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use MIT\Discount\Model\LabelImageRepository;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\RuleRepository;

class Data extends AbstractHelper
{

    /**
     * @var LabelImageRepository
     */
    private $labelImageRepository;

    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteriaInterface;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var RuleRepository
     */
    private $ruleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        LabelImageRepository $labelImageRepository,
        SearchCriteriaInterface $searchCriteriaInterface,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        RuleRepository $ruleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->labelImageRepository = $labelImageRepository;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->ruleRepository = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * get Discount Image Drop Down list 
     * @return array
     */
    public function getImageOptions()
    {
        $res = [];
        $imageDataList = $this->labelImageRepository->getList($this->searchCriteriaInterface);
        foreach ($imageDataList->getItems() as $item) {
            $res[] = array('label' => $item->getName(), 'value' => $item->getLabelImageId());
        }
        if (count($res) > 0) {
            array_unshift(
                $res,
                ['value' => -1, 'label' => __('Please select image.')]
            );
        }
        return $res;
    }

    /**
     * get sales rule active DropDown List
     * @return array
     */
    public function getSalesRuleDropDownList()
    {
        $res = [];
        // $filteredId = $this->_filterBuilder
        //     ->setConditionType('eq')
        //     ->setField('is_active')
        //     ->setValue((1))
        //     ->create();
        // $filterGroupList = [];
        // $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
        // $this->searchCriteriaBuilder->setFilterGroups($filterGroupList);
        $ruleList = $this->ruleRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($ruleList->getItems() as $rule) {
            $res[] = array('label' => $rule->getName(), 'value' => $rule->getRuleId());
        }
        if (count($res) > 0) {
            array_unshift(
                $res,
                ['value' => -1, 'label' => __('Please select Cart Price Rule.')]
            );
        }
        return $res;
    }

    /**
     * get Sale Rule by Id
     * @return RuleInterface|null
     */
    public function getSalesRuleById($id)
    {
        try {
            return $this->ruleRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            return null;
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
