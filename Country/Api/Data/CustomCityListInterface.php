<?php

namespace MIT\Country\Api\Data;

interface CustomCityListInterface{
    
    const ID = 'id';
    const CITY = 'city';
    const POSTCODE = 'postcode';
    
    /**
    * get city id
    * @return int
    */
    
    public function getId();
    
    /**
    * set product id
    * @param int $id
    * @return $this
    */
    public function setId(int $id);
    
    /**
    * get city name
    * @return string
    */
    
    public function getCity();
    
    /**
    * set city name
    * @param string $city
    * @return $this
    */
    public function setCity(string $city);


    /**
     * get postcode name
     * @return string
     */

     public function getPostCode();
     
    /**
     * set postcode name
     * @param string $postcode
     * @return $this
     */

     public function setPostCode(string $postcode);
  
     
}