<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\HomeSlider\Model;

use MIT\HomeSlider\Api\Data\HomeSliderInterface;
use MIT\HomeSlider\Api\Data\HomeSliderInterfaceFactory;
use MIT\HomeSlider\Api\Data\HomeSliderSearchResultsInterfaceFactory;
use MIT\HomeSlider\Api\HomeSliderRepositoryInterface;
use MIT\HomeSlider\Model\ResourceModel\HomeSlider as ResourceHomeSlider;
use MIT\HomeSlider\Model\ResourceModel\HomeSlider\CollectionFactory as HomeSliderCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class HomeSliderRepository implements HomeSliderRepositoryInterface
{

    /**
     * @var ResourceHomeSlider
     */
    protected $resource;

    /**
     * @var HomeSliderInterfaceFactory
     */
    protected $homeSliderFactory;

    /**
     * @var HomeSliderCollectionFactory
     */
    protected $homeSliderCollectionFactory;

    /**
     * @var HomeSlider
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceHomeSlider $resource
     * @param HomeSliderInterfaceFactory $homeSliderFactory
     * @param HomeSliderCollectionFactory $homeSliderCollectionFactory
     * @param HomeSliderSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceHomeSlider $resource,
        HomeSliderInterfaceFactory $homeSliderFactory,
        HomeSliderCollectionFactory $homeSliderCollectionFactory,
        HomeSliderSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->homeSliderFactory = $homeSliderFactory;
        $this->homeSliderCollectionFactory = $homeSliderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(HomeSliderInterface $homeSlider)
    {
        try {
            $this->resource->save($homeSlider);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the homeSlider: %1',
                $exception->getMessage()
            ));
        }
        return $homeSlider;
    }

    /**
     * @inheritDoc
     */
    public function get($homeSliderId)
    {
        $homeSlider = $this->homeSliderFactory->create();
        $this->resource->load($homeSlider, $homeSliderId);
        if (!$homeSlider->getId()) {
            throw new NoSuchEntityException(__('HomeSlider with id "%1" does not exist.', $homeSliderId));
        }
        return $homeSlider;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->homeSliderCollectionFactory->create();

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
    public function delete(HomeSliderInterface $homeSlider)
    {
        try {
            $homeSliderModel = $this->homeSliderFactory->create();
            $this->resource->load($homeSliderModel, $homeSlider->getHomesliderId());
            $this->resource->delete($homeSliderModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the HomeSlider: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($homeSliderId)
    {
        return $this->delete($this->get($homeSliderId));
    }
}
