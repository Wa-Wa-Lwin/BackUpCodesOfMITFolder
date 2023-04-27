<?php

namespace MIT\RewardPoints\Api;

use MIT\RewardPoints\Api\Data\PointExchangeInterface;

interface CustomRewardPointInterface {


    /**
     * get milstone by customer
     * @param int $customerId
     * @return \MIT\RewardPoints\Api\Data\CustomerMilestoneInterface
     */
    public function getMileStoneByCustomer($customerId);

    /**
     * get point exchange by quote id
     * @param int $cartId
     * @param int $customerId
     * @return PointExchangeInterface
     */
    public function getPointExchangeByQuoteCustomer($cartId, $customerId);

    /**
     * get point exchange by quote guest
     * @param string $cartId
     * @return PointExchangeInterface
     */
    public function getPointExchangeByQuoteGuest($cartId);
}