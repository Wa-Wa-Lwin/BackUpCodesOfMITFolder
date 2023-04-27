<?php

namespace MIT\Product\Api;

interface HomeSliderInterface
{

    /**
     * get home slider
     * @param int $type
     * @return \Magento\Framework\DataObject
     */
    public function getHomeSlider($type);
}
