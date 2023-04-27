<?php

namespace MIT\Product\Api\Data;

interface GroupProductInterface{
    
    const ID = 'id';
    const NAME = 'name';
    const SKU = 'sku';
    const REGULAR_PRICE = 'regular_price';
    const REAL_PRICE = 'real_price';
    const QUANTITY = 'qty';
    const DEFAULY_QUANTITY = 'default_qty';
    
    /**
    * get product id
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
     * get product name
     * @return string
     */
    
    public function getName();

    /**
    * set product name
    * @param string $name
    * @return $this
    */

    public function setName(string $name);

    /**
     * get sku
     * @return string
     */

    public function getSku();

    /**
     * set sku
     * @param string $sku
     * @return $this
     */

    public function setSku(string $sku);

    /**
     * get price
     * @return string
     */

    public function getRegularPrice();

    /**
     * set price
     * @param string $price
     * @return $this
     */
    public function setRegularPrice(string $price);

    /**
     * get price
     * @return string
     */

    public function getRealPrice();

    /**
     * set price
     * @param string $price
     * @return $this
     */
    public function setRealPrice(string $price);

    // /**
    //  * get actual quantity
    //  * @return int
    //  */

    // public function getQuantity();

    // /**
    //  * set qty
    //  * @param int $qty
    //  * @return $this
    //  */
    // public function setQuantity(int $qty);

    /**
     * get default quantity
     * @return int
     */

    public function getDefaultQuantity();

    /**
     * set default quantity
     * @param int default_qty
     * @return $this
     */

    public function setDefaultQuantity(int $default_qty);
     
}