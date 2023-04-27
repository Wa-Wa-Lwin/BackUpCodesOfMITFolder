<?php

namespace MIT\Product\Model\Layer;

use Magento\Catalog\Model\Layer\FilterList as LayerFilterList;

class FilterList extends LayerFilterList {

    /**
     * @var string[]
     */
    protected $filterTypes = [
        self::CATEGORY_FILTER  => \Magento\CatalogSearch\Model\Layer\Filter\Category::class,
        self::ATTRIBUTE_FILTER => \Magento\CatalogSearch\Model\Layer\Filter\Attribute::class,
        self::PRICE_FILTER     => \Magento\CatalogSearch\Model\Layer\Filter\Price::class,
        self::DECIMAL_FILTER   => \Magento\CatalogSearch\Model\Layer\Filter\Decimal::class,
    ];
}