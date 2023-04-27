<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Model;

use MIT\Discount\Api\Data\LabelImageInterface;
use MIT\Discount\Api\Data\LabelImageInterfaceFactory;
use MIT\Discount\Api\Data\LabelImageSearchResultsInterfaceFactory;
use MIT\Discount\Api\LabelImageRepositoryInterface;
use MIT\Discount\Model\ResourceModel\LabelImage as ResourceLabelImage;
use MIT\Discount\Model\ResourceModel\LabelImage\CollectionFactory as LabelImageCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class LabelImageRepository implements LabelImageRepositoryInterface
{

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceLabelImage
     */
    protected $resource;

    /**
     * @var LabelImageCollectionFactory
     */
    protected $labelImageCollectionFactory;

    /**
     * @var LabelImageInterfaceFactory
     */
    protected $labelImageFactory;

    /**
     * @var LabelImage
     */
    protected $searchResultsFactory;


    /**
     * @param ResourceLabelImage $resource
     * @param LabelImageInterfaceFactory $labelImageFactory
     * @param LabelImageCollectionFactory $labelImageCollectionFactory
     * @param LabelImageSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceLabelImage $resource,
        LabelImageInterfaceFactory $labelImageFactory,
        LabelImageCollectionFactory $labelImageCollectionFactory,
        LabelImageSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->labelImageFactory = $labelImageFactory;
        $this->labelImageCollectionFactory = $labelImageCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(LabelImageInterface $labelImage)
    {
        try {
            $this->resource->save($labelImage);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the labelImage: %1',
                $exception->getMessage()
            ));
        }
        return $labelImage;
    }

    /**
     * @inheritDoc
     */
    public function get($labelImageId)
    {
        $labelImage = $this->labelImageFactory->create();
        $this->resource->load($labelImage, $labelImageId);
        if (!$labelImage->getId()) {
            throw new NoSuchEntityException(__('label_image with id "%1" does not exist.', $labelImageId));
        }
        return $labelImage;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->labelImageCollectionFactory->create();
        
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
    public function delete(LabelImageInterface $labelImage)
    {
        try {
            $labelImageModel = $this->labelImageFactory->create();
            $this->resource->load($labelImageModel, $labelImage->getLabelImageId());
            $this->resource->delete($labelImageModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the label_image: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($labelImageId)
    {
        return $this->delete($this->get($labelImageId));
    }
}
