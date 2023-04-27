<?php

namespace MIT\Customer\Model;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\CustomerExtractor as ModelCustomerExtractor;
use Magento\Framework\App\RequestInterface;

class CustomerExtractor extends ModelCustomerExtractor
{

    public function extract($formCode, RequestInterface $request, array $attributeValues = [])
    {
        $customerForm = $this->formFactory->create(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $formCode,
            $attributeValues
        );

        $customerData = $customerForm->extractData($request);
        $customerData = $customerForm->compactData($customerData);

        if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $customerData['email'])) {
            if (preg_match('/^09([0-9]{7,15})$/i', $customerData['email'])) {
                $customerData['email'] = substr_replace($customerData['email'], '+959', 0, 2);
            } else if (preg_match('/^959([0-9]{7,15})$/i', $customerData['email'])) {
                $customerData['email'] = '+' . $customerData['email'];
            }
        }

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/registration.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Your text message');
        foreach ($customerData as $key_arr => $value_arr) {
            $logger->info('array value: key:' . $key_arr . ', value:' . $value_arr);
        }

        $allowedAttributes = $customerForm->getAllowedAttributes();
        $isGroupIdEmpty = !isset($allowedAttributes['group_id']);

        $customerDataObject = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $customerData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );

        $store = $this->storeManager->getStore();
        $storeId = $store->getId();

        if ($isGroupIdEmpty) {
            $groupId = isset($customerData['group_id']) ? $customerData['group_id']
                : $this->customerGroupManagement->getDefaultGroup($storeId)->getId();
            $customerDataObject->setGroupId(
                $groupId
            );
        }

        $customerDataObject->setWebsiteId($store->getWebsiteId());
        $customerDataObject->setStoreId($storeId);

        return $customerDataObject;
    }
}
