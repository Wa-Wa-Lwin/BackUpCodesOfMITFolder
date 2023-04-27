<?php

namespace MIT\Product\Api\Data;

interface ProductAttributesInterface
{

    const LABEL ='label';
    const VALUE = 'value';

    /**
     * get label
     * @return string
     */
    public function getLabel();

    /**
     * set label 
     * @var string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * get value
     * @return string
     */
    public function getValue();

    /**
     *set value
     *@var string $value
     *@return $this
    */
    public function setValue($value);

}

 