<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare (strict_types = 1);

namespace MIT\Customer\Plugin\Magento\Customer\Api;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use MIT\Customer\Helper\CustomerHelper;
use MIT\Customer\Model\Api\CustomAccountManagement;

class CustomerRepositoryInterface
{
    const TERMS_CONDITIONS_TABLE = 'customer_entity_int';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CustomerHelper
     */
    protected $helper;

    public function __construct(
        ResourceConnection $resourceConnection,
        Registry $registry,
        CustomerHelper $helper,
        CustomAccountManagement $customAccountManagement
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->customerAccountManagement = CustomAccountManagement::RANDOM_OTP_CODE_LENGTH;
    }

    public function beforeSave(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        $customer,
        $passwordHash = null
    ) {
        $mail = $this->normalizePhoneNumber($customer->getEmail());
        $customer->setEmail($mail);
        $termsAndCondition = ((int) $customer->getExtensionAttributes()->getTermsAndConditions());
        if ($termsAndCondition == 1 && null == $customer->getId()) {
            $this->registry->register('custom_terms_conditions', $termsAndCondition);
        }

        if ($termsAndCondition == 0 && null == $customer->getId()) {
            throw new LocalizedException(__('Acceptance of terms and conditions is required.'));
        } else if ($this->registry->registry('custom_terms_conditions') == 1 && null !== $customer->getId()) {
            if (!$this->checkTermsAndConditionsExist($customer->getId())) {
                $this->saveTermsAndConditions($customer->getId(), $this->registry->registry('custom_terms_conditions'));
                $this->registry->unregister('custom_terms_conditions');
            }
        }

        $isMobile = ((int) $customer->getExtensionAttributes()->getIsMobile());
        if ($isMobile == 1 && null == $customer->getId()) {
            $this->registry->register('is_mobile_key', $isMobile);
        }

        if ($this->registry->registry('is_mobile_key') == 1 && null !== $customer->getId()) {
            if (!$this->checkConfimationKeyExist($customer->getId())) {
                $customer->setConfirmation($this->helper->generateRandomOtpCode($this->customerAccountManagement));
                $this->registry->unregister('is_mobile_key');

                $id = $customer->getId();
                $currentDate = date('Y-m-d H:i:s');
                $addedDate = date('Y-m-d H:i:s', strtotime('+3 minutes', strtotime($currentDate)));

                $connection = $this->resourceConnection->getConnection();
                $tableName = $connection->getTableName('customer_entity');
                $connection->update($tableName,
                    ['otp_wrong_count' => 0, 'otp_expired_date' => $addedDate,
                        'confirm_mail_send_count' => 1],
                    ['entity_id = ?' => $id]);
            }
        }

        return [$customer, $passwordHash];
    }

    private function normalizePhoneNumber($mail)
    {
        if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $mail)) {
            if (preg_match('/^09([0-9]{7,15})$/i', $mail)) {
                $mail = substr_replace($mail, '+959', 0, 2);
            } else if (preg_match('/^959([0-9]{7,15})$/i', $mail)) {
                $mail = '+' . $mail;
            }
        }
        return $mail;
    }

    /**
     * check terms and conditions already accepted
     * @param int $customerId
     * @return bool
     */
    private function checkTermsAndConditionsExist($customerId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::TERMS_CONDITIONS_TABLE);
        $sql = $connection->select()
            ->from($tableName, 'value')
            ->where('entity_id = ? ', $customerId)
            ->where('attribute_id = (select attribute_id from eav_attribute where attribute_code = ? ) ', 'terms_conditions');
        $result = $connection->fetchOne($sql);
        return $result;
    }

    /**
     * save terms and conditions data
     * @param int $customerId
     * @param int $value
     */
    private function saveTermsAndConditions($customerId, $value)
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName(self::TERMS_CONDITIONS_TABLE);
        $eavTable = $this->resourceConnection->getTableName('eav_attribute');

        $sql = $connection->select()
            ->from($eavTable, 'attribute_id')
            ->where('attribute_code = ? ', 'terms_conditions');
        $attributeCode = $connection->fetchOne($sql);

        if ($attributeCode > 0) {
            $obj = [
                'attribute_id' => $attributeCode,
                'entity_id' => $customerId,
                'value' => $value,
            ];
            $connection->insert($table, $obj);
        }
    }

    /**
     * check confirmation key exist
     * @param int $customerId
     * @return bool
     */
    public function checkConfimationKeyExist($customerId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('customer_entity');
        $sql = $connection->select()
            ->from($tableName, 'confirmation')
            ->where('entity_id = ? ', $customerId);
        $result = $connection->fetchOne($sql);
        $key = ctype_digit($result);
        return $key;
    }
}
