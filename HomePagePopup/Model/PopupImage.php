<?php

namespace MIT\HomePagePopup\Model;

use Magento\Framework\Api\DataObjectHelper;
use MIT\HomePagePopup\Api\Data\PopupImageInterface;
use MIT\HomePagePopup\Api\Data\PopupImageInterfaceFactory;

class PopupImage extends \Magento\Framework\Model\AbstractModel implements PopupImageInterface
{

    protected $popupImageInterfaceFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'mit_homepagepopup_images';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PopupImageInterfaceFactory $popupImageInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \MIT\HomePagePopup\Model\ResourceModel\PopupImage $resource
     * @param \MIT\HomePagePopup\Model\ResourceModel\PopupImage\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        PopupImageInterfaceFactory $popupImageInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        \MIT\HomePagePopup\Model\ResourceModel\PopupImage $resource,
        \MIT\HomePagePopup\Model\ResourceModel\PopupImage\Collection $resourceCollection,
        array $data = []
    ) {
        $this->popupImageInterfaceFactory = $popupImageInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve imagecollection model with imagecollection data
     * @return PopupImageInterface
     */
    public function getDataModel()
    {
        $popupImageData = $this->getData();

        $popupImageDataObject = $this->popupImageInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $popupImageDataObject,
            $popupImageData,
            PopupImageInterface::class
        );

        return $popupImageDataObject;
    }

    /**
     * Set popupimage_id
     * @param string $popupimageId
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface
     */
    public function setPopupImageId($popupimageId)
    {
        return $this->setData(self::POPUPIMAGE_ID, $popupimageId);
    }

    /**
     * Get popupimage_id
     * @return string|null
     */
    public function getPopupImageId()
    {
        return $this->getData(self::POPUPIMAGE_ID);
    }

    /**
     * Set Name
     * @param string $name
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get Name
     * @return string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set image
     * @param string $image
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Get image
     * @return string|null
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * Get is_homepage
     *
     * @return int
     */
    public function getIsHomepage()
    {
        return $this->getData(self::IS_HOMEPAGE);
    }

    /**
     * Set is_homepage
     *
     * @param $value
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface
     */
    public function setIsHomepage($value)
    {
        return $this->setData(self::IS_HOMEPAGE, $value);
    }

    /**
     * Get is_promotion
     *
     * @return int
     */
    public function getIsPromotion()
    {
        return $this->getData(PopupImageInterface::IS_PROMOTION);
    }

    /**
     * Set is_promotion
     *
     * @param $value
     * @return $this
     */
    public function setIsPromotion($value)
    {
        return $this->setData(PopupImageInterface::IS_PROMOTION, $value);
    }

}
