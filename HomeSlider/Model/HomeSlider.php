<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\HomeSlider\Model;

use Codeception\Step\Retry;
use MIT\HomeSlider\Api\Data\HomeSliderInterface;
use Magento\Framework\Model\AbstractModel;
use PhpParser\Node\Stmt\Return_;
use MIT\HomeSlider\Model\UploaderPool;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;

class HomeSlider extends AbstractModel implements HomeSliderInterface
{

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
        $this->uploaderPool    = $uploaderPool;
    }

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        // @codingStandardsIgnoreEnd
        $this->_init(\MIT\HomeSlider\Model\ResourceModel\HomeSlider::class);
    }

    /**
     * @inheritDoc
     */
    public function setHomesliderId($homesliderId)
    {
        return $this->setData(self::HOMESLIDER_ID, $homesliderId);
    }

    /**
     * @inheritDoc
     */
    public function getHomesliderId()
    {
        return $this->getData(self::HOMESLIDER_ID);
    }


    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }


    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }



    /**
     * @inheritDoc
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setHomeSliderImage($image)
    {
        return $this->setData(self::HOME_SLIDER_IMAGE, $image);
    }

    /**
     * @inheritDoc
     */
    public function getHomeSliderImage()
    {
        return $this->getData(self::HOME_SLIDER_IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setHomeSliderImageMobile($image)
    {
        return $this->setData(self::HOME_SLIDER_IMAGE_MOBILE, $image);
    }

    /**
     * @inheritDoc
     */
    public function getHomeSliderImageMobile()
    {
        return $this->getData(self::HOME_SLIDER_IMAGE_MOBILE);
    }

    /**
     * @inheritDoc
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setIsHomeSlider($isHomeSlider)
    {
        return $this->setData(self::IS_HOME_SLIDER, $isHomeSlider);
    }

    /**
     * @inheritDoc
     */
    public function getIsHomeSlider()
    {
        return $this->getData(self::IS_HOME_SLIDER);
    }
    /** 
     * @inheritdoc
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }


    /**
     * @inheritDoc
     */
    public function setIsHomeSliderOne($isHomeSliderOne)
    {
        return $this->setData(self::IS_HOME_SLIDER_ONE, $isHomeSliderOne);
    }

    /**
     * @inheritDoc
     */
    public function getIsHomeSliderOne()
    {
        return $this->getData(self::IS_HOME_SLIDER_ONE);
    }

    /**
     * Get image URL
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function getHomeSliderImageUrl()
    {
        $url = false;
        $image = $this->getHomeSliderImage();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('home_slider_image');
                $url = $uploader->getBaseUrl() . $uploader->getBasePath() . $image;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }


    /**
     * Get image URL
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function getHomeSliderImageMobileUrl()
    {
        $url = false;
        $image = $this->getHomeSliderImageMobile();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('home_slider_image_mobile');
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
