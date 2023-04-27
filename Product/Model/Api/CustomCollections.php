<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\Product\Model\Api;


use ColMage\MyCollection\Api\Data\ImageCollectionSearchResultsInterfaceFactory;
use ColMage\MyCollection\Model\ResourceModel\ImageCollection\CollectionFactory as ImageCollectionCollectionFactory;
use ColMage\MyCollection\Model\Uploader;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use MIT\Product\Api\CustomCollectionsInterface;

class CustomCollections implements CustomCollectionsInterface
{

    /**
     * @var ImageCollection
     */
    protected $searchResultsFactory;

    /**
     * @var ImageCollectionCollectionFactory
     */
    protected $imageCollectionCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    protected $categoryCollectionFactory;

    private $storeManager;


    /**
     * @param ImageCollectionCollectionFactory $imageCollectionCollectionFactory
     * @param ImageCollectionSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ImageCollectionCollectionFactory $imageCollectionCollectionFactory,
        ImageCollectionSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->imageCollectionCollectionFactory = $imageCollectionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->categoryCollectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->imageCollectionCollectionFactory->create();
        $currentStore = $this->storeManager->getStore();
        $baseUrl = $currentStore->getBaseUrl();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $collection = $this->categoryCollectionFactory->create()->addAttributeToFilter('url_key',$model->getAttribute());
            foreach ($collection as $product) {
                $model->setId($product->getId());
            }
            $model->setImage1($baseUrl . 'media/' . Uploader::IMAGE_PATH . $model->getImage1());
            $model->setImage2($baseUrl . 'media/' . Uploader::IMAGE_PATH . $model->getImage2());
            $model->setImage3($baseUrl . 'media/' . Uploader::IMAGE_PATH . $model->getImage3());
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}

