<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\SalesRuleLabel\Model;

use MIT\SalesRuleLabel\Api\CustomConditionRepositoryInterface;
use MIT\SalesRuleLabel\Api\Data\CustomConditionInterface;
use MIT\SalesRuleLabel\Api\Data\CustomConditionInterfaceFactory;
use MIT\SalesRuleLabel\Api\Data\CustomConditionSearchResultsInterfaceFactory;
use MIT\SalesRuleLabel\Model\ResourceModel\CustomCondition as ResourceCustomCondition;
use MIT\SalesRuleLabel\Model\ResourceModel\CustomCondition\CollectionFactory as CustomConditionCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomConditionRepository implements CustomConditionRepositoryInterface
{

    /**
     * @var CustomCondition
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceCustomCondition
     */
    protected $resource;

    /**
     * @var CustomConditionInterfaceFactory
     */
    protected $customConditionFactory;

    /**
     * @var CustomConditionCollectionFactory
     */
    protected $customConditionCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceCustomCondition $resource
     * @param CustomConditionInterfaceFactory $customConditionFactory
     * @param CustomConditionCollectionFactory $customConditionCollectionFactory
     * @param CustomConditionSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCustomCondition $resource,
        CustomConditionInterfaceFactory $customConditionFactory,
        CustomConditionCollectionFactory $customConditionCollectionFactory,
        CustomConditionSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->customConditionFactory = $customConditionFactory;
        $this->customConditionCollectionFactory = $customConditionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(
        CustomConditionInterface $customCondition
    ) {
        try {
            $this->resource->save($customCondition);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the customCondition: %1',
                $exception->getMessage()
            ));
        }
        return $customCondition;
    }

    /**
     * @inheritDoc
     */
    public function get($customConditionId)
    {
        $customCondition = $this->customConditionFactory->create();
        $this->resource->load($customCondition, $customConditionId);
        if (!$customCondition->getId()) {
            throw new NoSuchEntityException(__('CustomCondition with id "%1" does not exist.', $customConditionId));
        }
        return $customCondition;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->customConditionCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(
        CustomConditionInterface $customCondition
    ) {
        try {
            $customConditionModel = $this->customConditionFactory->create();
            $this->resource->load($customConditionModel, $customCondition->getCustomconditionId());
            $this->resource->delete($customConditionModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the CustomCondition: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($customConditionId)
    {
        return $this->delete($this->get($customConditionId));
    }

    /**
     * getBySalesRuleId
     * @param int $saleRuleId
     * @return \MIT\SalesRuleLabel\Model\ResourceModel\CustomCondition\Collection
     * @throws Magento\Framework\Exception\NoSuchEntityException|\Magento\Framework\Exception\LocalizedException
     * 
     */
    public function getBySalesRuleId($saleRuleId) {
        // $ruleIds = $this->resource->getIdsBySalesRuleId($saleRuleId);
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customcondition.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('sender builder called');
        // $logger->info(json_encode($ruleIds));
        // if (is_array($ruleIds) && count($ruleIds) > 0) {
            return $this->customConditionCollectionFactory->create()->addFieldToFilter('sale_rule_id', ['eq' => $saleRuleId]);
            // }
            //return $this->customConditionCollectionFactory->create();
    }
}