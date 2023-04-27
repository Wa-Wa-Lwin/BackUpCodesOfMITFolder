<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\Customer\Model\ResourceModel;

use Magento\Customer\Model\ResourceModel\Customer as ResourceModelCustomer;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * Customer entity resource model
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Customer extends ResourceModelCustomer
{

    /**
     * Check customer scope, email and confirmation key before saving
     *
     * @param \Magento\Framework\DataObject|\Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return $this
     * @throws AlreadyExistsException
     * @throws ValidatorException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _beforeSave(\Magento\Framework\DataObject $customer)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        if ($customer->getStoreId() === null) {
            $customer->setStoreId($this->storeManager->getStore()->getId());
        }
        $customer->getGroupId();

        parent::_beforeSave($customer);

        if (!$customer->getEmail()) {
            throw new ValidatorException(__('The customer email or phone is missing. Enter and try again.'));
        }

        $connection = $this->getConnection();
        $bind = ['email' => $customer->getEmail()];

        $select = $connection->select()->from(
            $this->getEntityTable(),
            [$this->getEntityIdField()]
        )->where(
            'email = :email'
        );
        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $bind['website_id'] = (int)$customer->getWebsiteId();
            $select->where('website_id = :website_id');
        }
        if ($customer->getId()) {
            $bind['entity_id'] = (int)$customer->getId();
            $select->where('entity_id != :entity_id');
        }

        $result = $connection->fetchOne($select, $bind);
        if ($result) {
            throw new AlreadyExistsException(
                __('A customer with the same email address or phone already exists in an associated website.')
            );
        }

        // set confirmation key logic
        if (
            !$customer->getId() &&
            $this->accountConfirmation->isConfirmationRequired(
                $customer->getWebsiteId(),
                $customer->getId(),
                $customer->getEmail()
            )
        ) {
            $customer->setConfirmation($customer->getRandomConfirmationKey());
        }
        // remove customer confirmation key from database, if empty
        if (!$customer->getConfirmation()) {
            $customer->setConfirmation(null);
        }

        if (!$customer->getData('ignore_validation_flag')) {
            $this->_validate($customer);
        }

        return $this;
    }
}
