<?php

namespace MIT\HomePagePopup\Model\Data;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use MIT\HomePagePopup\Api\Data\PopupImageInterface;
use MIT\HomePagePopup\Model\UploaderPool;

class PopupImage extends AbstractModel implements PopupImageInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'mit_homepagepopup_images';

    /**
     * @var UploaderPool
     */
    protected $uploaderPool;

    /**
     * Sliders constructor.
     * @param Context $context
     * @param Registry $registry
     * @param UploaderPool $uploaderPool
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        UploaderPool $uploaderPool,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->uploaderPool = $uploaderPool;
    }

    /**
     * Initialise resource model
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        // @codingStandardsIgnoreEnd
        $this->_init('MIT\HomePagePopup\Model\ResourceModel\PopupImage');
    }

    /**
     * Get cache identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getPopupImageId()];
    }

    /**
     * Get popupimage_id
     * @return string|null
     */
    public function getPopupImageId()
    {
        return $this->_get(self::POPUPIMAGE_ID);
    }

    /**
     * Set popupimage_id
     *
     * @param string $popupimageId
     * @return \MIT\HomePagePopup\Api\Data\PopupImageInterface
     */
    public function setPopupImageId($popupimageId)
    {
        return $this->setData(self::POPUPIMAGE_ID, $popupimageId);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(PopupImageInterface::NAME);
    }

    /**
     * Set name
     *
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(PopupImageInterface::NAME, $name);
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->getData(PopupImageInterface::IMAGE);
    }

    /**
     * Set image
     *
     * @param $image
     * @return $this
     */
    public function setImage($image)
    {
        return $this->setData(PopupImageInterface::IMAGE, $image);
    }

    /**
     * Get is_homepage
     *
     * @return int
     */
    public function getIsHomepage()
    {
        return $this->getData(PopupImageInterface::IS_HOMEPAGE);
    }

    /**
     * Set is_homepage
     *
     * @param $value
     * @return $this
     */
    public function setIsHomepage($value)
    {
        return $this->setData(PopupImageInterface::IS_HOMEPAGE, $value);
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

    /**
     * Get image URL
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function getImageUrl()
    {
        $url = false;
        $image = $this->getImage();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('image');
                $url = $uploader->getBaseUrl() . $uploader->getBasePath() . $image;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }

}
