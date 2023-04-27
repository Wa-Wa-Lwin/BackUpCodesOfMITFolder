<?php

namespace MIT\CatalogRule\Api\Data;

use Magento\CatalogRule\Api\Data\RuleInterface as DataRuleInterface;

interface RuleInterface extends DataRuleInterface
{

    const HOME_SLIDER_IMG = 'home_slider_img';
    const HOME_SLIDER_IMG_MOBILE = 'home_slider_img_mobile';
    const PROMO_SLIDER_IMG = 'promo_slider_img';
    const IS_HOME_SLIDER = 'is_home_slider';
    const IS_HOME_SLIDER_ONE = 'is_home_slider_one';
    const IS_PROMOTION_SLIDER = 'is_promotion_slider';
    const IS_WEEKLY_PROMOTION = 'is_weekly_promotion';

    /**
     * set home slider img
     * @param string $img
     * @return $this
     */
    public function setHomeSliderImg($img);

    /**
     * get home slider img
     * @return string
     */
    public function getHomeSliderImg();

    /**
     * set home slider img mobile
     * @param string $img
     * @return $this
     */
    public function setHomeSliderImgMobile($img);

    /**
     * get home slider img mobile
     * @return string
     */
    public function getHomeSliderImgMobile();

    /**
     * set promo slider img
     * @param string $img
     * @return $this
     */
    public function setPromoSliderImg($img);

    /**
     * get promo slider img
     * @return string
     */
    public function getPromoSliderImg();

    /**
     * set is home slider
     * @param int $isHomeSlider
     * @return $this
     */
    public function setIsHomeSlider($isHomeSlider);

    /**
     * get is home slider
     * @return int
     */
    public function getIsHomeSlider();

    /**
     * set is home slider one
     * @param int $isHomeSliderOne
     * @return $this
     */
    public function setIsHomeSliderOne($isHomeSliderOne);

    /**
     * get is home slider one
     * @return int
     */
    public function getIsHomeSliderOne();

    /**
     * set is promotion slider
     * @param int $isPromotionSlider
     * @return $this
     */
    public function setIsPromotionSlider($isPromotionSlider);

    /**
     * get is promotion slider
     * @return int
     */
    public function getIsPromotionSlider();

    /**
     * set is weekly promotion
     * @param int $weeklyPromotion
     * @return $this
     */
    public function setIsWeeklyPromotion($weeklyPromotion);

    /**
     * get is weekly promotion
     * @return int
     */
    public function getIsWeeklyPromotion();
}
