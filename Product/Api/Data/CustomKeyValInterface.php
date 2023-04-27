<?php

namespace MIT\Product\Api\Data;

interface CustomKeyValInterface
{
    const KEY = 'key';
    const VAL = 'val';

    /**
     * set key data
     * @param mixed $key
     * @return $this
     */
    public function setKey($key);

    /**
     * get key data
     * @return mixed
     */
    public function getKey();

    /**
     * set val data
     * @param mixed $val
     * @return $this
     */
    public function setValue($val);

    /**
     * get val data
     * @return mixed
     */
    public function getValue();
}
