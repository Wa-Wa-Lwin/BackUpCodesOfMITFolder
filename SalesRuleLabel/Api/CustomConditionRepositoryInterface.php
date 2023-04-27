<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\SalesRuleLabel\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CustomConditionRepositoryInterface
{

    /**
     * Save CustomCondition
     * @param \MIT\SalesRuleLabel\Api\Data\CustomConditionInterface $customCondition
     * @return \MIT\SalesRuleLabel\Api\Data\CustomConditionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \MIT\SalesRuleLabel\Api\Data\CustomConditionInterface $customCondition
    );

    /**
     * Retrieve CustomCondition
     * @param string $customconditionId
     * @return \MIT\SalesRuleLabel\Api\Data\CustomConditionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($customconditionId);

    /**
     * Retrieve CustomCondition matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MIT\SalesRuleLabel\Api\Data\CustomConditionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete CustomCondition
     * @param \MIT\SalesRuleLabel\Api\Data\CustomConditionInterface $customCondition
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \MIT\SalesRuleLabel\Api\Data\CustomConditionInterface $customCondition
    );

    /**
     * Delete CustomCondition by ID
     * @param string $customconditionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customconditionId);
}