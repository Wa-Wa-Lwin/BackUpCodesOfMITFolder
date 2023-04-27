<?php

namespace MIT\WeeklyPromo\Api;

interface WeeklyPromotionInterface {

    /**
     * get weekly promotion data
     * @return \MIT\WeeklyPromo\Api\Data\WeeklyPromotionManagementInterface
     */
    public function getWeeklyPromotion();
}