<?php

namespace MIT\WeeklyPromo\Block\Widget;

class PromotionSlider extends Promotion
{
    protected $_template = "widget/promotionslider.phtml";


    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if ($this->hasData('no_of_products')) {
            return $this->getData('no_of_products');
        }

        if (null === $this->getData('no_of_products')) {
            $this->setData('no_of_products', self::DEFAULT_PRODUCTS_COUNT);
        }

        return $this->getData('no_of_products');
    }
}
