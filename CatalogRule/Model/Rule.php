<?php

namespace MIT\CatalogRule\Model;

use Magento\CatalogRule\Model\Rule as ModelRule;
use MIT\CatalogRule\Api\Data\RuleInterface;

class Rule extends ModelRule implements RuleInterface
{
    /**
     * @inheritdoc
     */
    public function setHomeSliderImg($img)
    {
        return $this->setData(self::HOME_SLIDER_IMG, $img);
    }

    /**
     * @inheritdoc
     */
    public function getHomeSliderImg()
    {
        return $this->getData(self::HOME_SLIDER_IMG);
    }

    /**
     * @inheritdoc
     */
    public function setHomeSliderImgMobile($img)
    {
        return $this->setData(self::HOME_SLIDER_IMG_MOBILE, $img);
    }

    /**
     * @inheritdoc
     */
    public function getHomeSliderImgMobile()
    {
        return $this->getData(self::HOME_SLIDER_IMG_MOBILE);
    }

    /**
     * @inheritdoc
     */
    public function setPromoSliderImg($img)
    {
        return $this->setData(self::PROMO_SLIDER_IMG, $img);
    }

    /**
     * @inheritdoc
     */
    public function getPromoSliderImg()
    {
        return $this->getData(self::PROMO_SLIDER_IMG);
    }

    /**
     * @inheritdoc
     */
    public function setIsHomeSlider($isHomeSlider)
    {
        return $this->setData(self::IS_HOME_SLIDER, $isHomeSlider);
    }

    /**
     * @inheritdoc
     */
    public function getIsHomeSlider()
    {
        return $this->getData(self::IS_HOME_SLIDER);
    }

    /**
     * @inheritdoc
     */
    public function setIsHomeSliderOne($isHomeSliderOne)
    {
        return $this->setData(self::IS_HOME_SLIDER_ONE, $isHomeSliderOne);
    }

    /**
     * @inheritdoc
     */
    public function getIsHomeSliderOne()
    {
        return $this->getData(self::IS_HOME_SLIDER_ONE);
    }

    /**
     * @inheritdoc
     */
    public function setIsPromotionSlider($isPromotionSlider)
    {
        return $this->setData(self::IS_PROMOTION_SLIDER, $isPromotionSlider);
    }

    /**
     * @inheritdoc
     */
    public function getIsPromotionSlider()
    {
        return $this->getData(self::IS_PROMOTION_SLIDER);
    }

    /**
     * @inheritdoc
     */
    public function setIsWeeklyPromotion($weeklyPromotion)
    {
        return $this->setData(self::IS_WEEKLY_PROMOTION, $weeklyPromotion);
    }

    /**
     * @inheritdoc
     */
    public function getIsWeeklyPromotion()
    {
        return $this->getData(self::IS_WEEKLY_PROMOTION);
    }
}
