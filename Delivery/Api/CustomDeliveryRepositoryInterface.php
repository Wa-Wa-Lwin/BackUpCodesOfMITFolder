<?php

namespace MIT\Delivery\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CustomDeliveryRepositoryInterface
{

    public function save(Data\CustomDeliveryInterface $delivery);

    public function getById($deliveryId);

    public function getList(SearchCriteriaInterface $searchCriteria);

    public function delete(Data\CustomDeliveryInterface $delivery);

    public function deleteById($deliveryId);
}
