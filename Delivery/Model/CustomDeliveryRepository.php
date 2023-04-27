<?php

namespace MIT\Delivery\Model;


use MIT\Delivery\Api\Data\CustomDeliveryInterface;
use MIT\Delivery\Api\Data\CustomDeliverySearchResultsInterfaceFactory;
use MIT\Delivery\Model\ResourceModel\CustomDelivery as ResourceCustomDelivery;
use MIT\Delivery\Model\ResourceModel\CustomDelivery\CollectionFactory as customDeliveryCollectionFactory;
use MIT\Delivery\Model\ResourceModel\CustomDelivery\Collection as CustomDeliveryCollection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use MIT\Delivery\Api\CustomDeliveryRepositoryInterface;

class CustomDeliveryRepository implements CustomDeliveryRepositoryInterface
{


    /**
     * @var ResourceCustomDelivery
     */
    protected $resource;

    /**
     * @var CustomDeliveryFactory
     */
    protected $customDeliveryFactory;

    /**
     * @var customDeliveryCollectionFactory
     */
    protected $customDeliveryCollectionFactory;

    /**
     * @var CustomDeliverySearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var CustomDeliveryCollection
     */
    protected $customDeliveryCollection;

    /**
     * @var CustomDeliveryInterface[]
     */
    protected $instances = [];

    /**
     * @param ResourceCustomDelivery $resource
     * @param \MageDirect\Faq\Model\CustomDeliveryFactory $customDeliveryFactory
     * @param CustomDeliveryCollectionFactory $customDeliveryCollectionFactory
     * @param CustomDeliverySearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCustomDelivery $resource,
        CustomDeliveryFactory $customDeliveryFactory,
        CustomDeliveryCollectionFactory $customDeliveryCollectionFactory,
        CustomDeliverySearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        CustomDeliveryCollection $customDeliveryCollection
    ) {
        $this->resource = $resource;
        $this->customDeliveryFactory = $customDeliveryFactory;
        $this->customDeliveryCollectionFactory = $customDeliveryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->customDeliveryCollection = $customDeliveryCollection;
    }

    /**
     * @param CustomDeliveryInterface $customDelivery
     * @return CustomDeliveryInterface
     * @throws CouldNotSaveException
     */
    public function save(CustomDeliveryInterface $customDelivery)
    {
        try {
            $this->resource->save($customDelivery);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$customDelivery->getId()]);
        return $customDelivery;
    }

    /**
     * @param int $customDeliveryId
     * @return CustomDeliveryInterface
     * @throws NoSuchEntityException
     */
    public function getById($customDeliveryId)
    {
        if (!isset($this->instances[$customDeliveryId])) {
            $customDelivery = $this->customDeliveryFactory->create();
            $this->resource->load($customDelivery, $customDeliveryId);
            if (!$customDelivery->getId()) {
                throw new NoSuchEntityException(__('Faq with id "%1" does not exist.', $customDeliveryId));
            }
            $this->instances[$customDeliveryId] = $customDelivery;
        }

        return $this->instances[$customDeliveryId];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return CustomDeliverySearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {

        $collection = $this->customDeliveryCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);


        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param CustomDeliveryInterface $customDelivery
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(CustomDeliveryInterface $customDelivery)
    {
        try {
            $customDeliveryId = $customDelivery->getId();
            $this->resource->delete($customDelivery);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        unset($this->instances[$customDeliveryId]);
        return true;
    }

    /**
     * @param int $customDeliveryId
     * @return bool true on success
     */
    public function deleteById($customDeliveryId)
    {
        return $this->delete($this->getById($customDeliveryId));
    }
}
