<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Model;

use MIT\Discount\Api\Data\LabelInterface;
use MIT\Discount\Api\Data\LabelInterfaceFactory;
use MIT\Discount\Api\Data\LabelSearchResultsInterfaceFactory;
use MIT\Discount\Api\LabelRepositoryInterface;
use MIT\Discount\Model\ResourceModel\Label as ResourceLabel;
use MIT\Discount\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class LabelRepository implements LabelRepositoryInterface
{

    /**
     * @var ResourceLabel
     */
    protected $resource;

    /**
     * @var Label
     */
    protected $searchResultsFactory;

    /**
     * @var LabelCollectionFactory
     */
    protected $labelCollectionFactory;

    /**
     * @var LabelInterfaceFactory
     */
    protected $labelFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceLabel $resource
     * @param LabelInterfaceFactory $labelFactory
     * @param LabelCollectionFactory $labelCollectionFactory
     * @param LabelSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceLabel $resource,
        LabelInterfaceFactory $labelFactory,
        LabelCollectionFactory $labelCollectionFactory,
        LabelSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->labelFactory = $labelFactory;
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(LabelInterface $label)
    {
        try {
            $this->resource->save($label);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the label: %1',
                $exception->getMessage()
            ));
        }
        return $label;
    }

    /**
     * @inheritDoc
     */
    public function get($labelId)
    {
        $label = $this->labelFactory->create();
        $this->resource->load($label, $labelId);
        if (!$label->getId()) {
            throw new NoSuchEntityException(__('label with id "%1" does not exist.', $labelId));
        }
        return $label;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->labelCollectionFactory->create();
        
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
    public function delete(LabelInterface $label)
    {
        try {
            $labelModel = $this->labelFactory->create();
            $this->resource->load($labelModel, $label->getLabelId());
            $this->resource->delete($labelModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the label: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($labelId)
    {
        return $this->delete($this->get($labelId));
    }
}

