<?php

namespace MIT\HomePagePopup\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use MIT\HomePagePopup\Api\Data\PopupImageInterfaceFactory;
use MIT\HomePagePopup\Api\Data\PopupImageManagementInterfaceFactory;
use MIT\HomePagePopup\Api\Data\PopupImageSearchResultsInterfaceFactory;
use MIT\HomePagePopup\Api\PopupImageRepositoryInterface;
use MIT\HomePagePopup\Model\ResourceModel\PopupImage as ResourcePopupImage;
use MIT\HomePagePopup\Model\ResourceModel\PopupImage\CollectionFactory as CollectionFactory;

class PopupImageRepository implements PopupImageRepositoryInterface
{

    protected $resource;

    protected $popupImageFactory;

    protected $collectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $popupImageInterfaceFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    /**
     * @var PopupImageManagementInterfaceFactory
     */
    protected $popupImageManagementInterface;

    /**
     * @param ResourcePopupImage $resource
     * @param PopupImageFactory $popupImageFactory
     * @param PopupImageInterfaceFactory $popupImageInterfaceFactory
     * @param CollectionFactory $collectionFactory
     * @param PopupImageSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourcePopupImage $resource,
        PopupImageFactory $popupImageFactory,
        PopupImageInterfaceFactory $popupImageInterfaceFactory,
        CollectionFactory $collectionFactory,
        PopupImageSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        PopupImageManagementInterfaceFactory $popupImageManagementInterface
    ) {
        $this->resource = $resource;
        $this->popupImageFactory = $popupImageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->popupImageInterfaceFactory = $popupImageInterfaceFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->popupImageManagementInterface = $popupImageManagementInterface;

    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \MIT\HomePagePopup\Api\Data\PopupImageInterface $popupImage
    ) {
        /* if (empty($popupImage->getStoreId())) {
        $storeId = $this->storeManager->getStore()->getId();
        $popupImage->setStoreId($storeId);
        } */

        $popupImageData = $this->extensibleDataObjectConverter->toNestedArray(
            $popupImage,
            [],
            \MIT\HomePagePopup\Api\Data\PopupImageInterface::class
        );

        $popupImageModel = $this->popupImageFactory->create()->setData($popupImageData);

        try {
            $this->resource->save($popupImageModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the PopupImage: %1',
                $exception->getMessage()
            ));
        }
        return $popupImageModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($popupImageId)
    {
        $popupImage = $this->popupImageFactory->create();
        $this->resource->load($popupImage, $popupImageId);
        if (!$popupImage->getId()) {
            throw new NoSuchEntityException(__('PopupImage with id "%1" does not exist.', $popupImageId));
        }
        return $popupImage->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \MIT\HomePagePopup\Api\Data\PopupImageInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \MIT\HomePagePopup\Api\Data\PopupImageInterface $popupImage
    ) {
        try {
            $popupImageModel = $this->popupImageFactory->create();
            $this->resource->load($popupImageModel, $popupImage->getPopupImageId());
            $this->resource->delete($popupImageModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the popupImage: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($popupImageId)
    {
        return $this->delete($this->getById($popupImageId));
    }

    /**
     * get popup image by image type
     * @param int $type
     * @return \MIT\HomePagePopup\Api\Data\PopupImageManagementInterface
     */
    public function getPopupImageByType($type)
    {
        $searchResult = $this->popupImageManagementInterface->create();

        $collection = $this->collectionFactory->create();
        if ($collection->getSize() == 0) {
            throw new NoSuchEntityException(__('There is no popup image.'));
        };
        $url = $this->getBaseUrl();
        $imageUrl = $url . 'homepagepopup/images/image';

        foreach ($collection as $items) {
            if ($type == 1) {
                if ($items->getIsHomepage() == 1) {
                    $imagePath = $items->getImage();
                    $image = $imageUrl . $imagePath;
                    $searchResult->setImagePath($image);
                    return $searchResult;
                }
            } elseif ($type == 2) {
                if ($items->getIsPromotion() == 1) {
                    $imagePath = $items->getImage();
                    $image = $imageUrl . $imagePath;
                    $searchResult->setImagePath($image);
                    return $searchResult;
                }
            } else {
                throw new NoSuchEntityException(__('This type of popup image does not exist.', $type));
            }
        }
    }

    /**
     * get base url of popup image
     */
    public function getBaseUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            );
    }
}
