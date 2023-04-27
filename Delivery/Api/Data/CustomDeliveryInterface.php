<?php

namespace MIT\Delivery\Api\Data;

interface CustomDeliveryInterface
{

    const ID = 'id';
    const REGION = 'region';
    const CITY = 'city';
    const TOTAL = 'total';
    const ITEMS = 'items';
    const WEIGHT = 'weight';
    const SHIPPING = 'shipping';
    const DELIVERY_TYPE = 'custom_delivery_type';


    public function getId();
    public function setId($id);
    public function getRegion();
    public function setRegion($region);
    public function getCity();
    public function setCity($city);
    public function getTotal();
    public function setTotal($total);
    public function getItems();
    public function setItems($items);
    public function getWeight();
    public function setWeight($weight);
    public function getShipping();
    public function setShipping($shipping);
    public function getDeliveryType();
    public function setDeliveryType($deliveryType);
}
