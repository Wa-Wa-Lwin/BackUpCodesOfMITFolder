<?php

namespace MIT\ProductSorter\Model\Api;

use Magento\Store\Model\StoreManagerInterface;
use MIT\ProductSorter\Api\ProductSortOrderInterface;

class ProductSortOrder implements ProductSortOrderInterface
{

    const SORT_ORDER_ASC = 'ASC';
    const SORT_ORDER_DESC = 'DESC';

    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $catalogConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        \Magento\Catalog\Model\Config $catalogConfig,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * @inheritdoc
     */
    public function getProductSortOrder()
    {
        $result = [];
        $sortOrder = $this->catalogConfig->getAttributeUsedForSortByArray();
        foreach ($sortOrder as $key => $value) {
            if ($key !== 'mpmembership_price_fixed') {
                if ($key == 'price_asc') {
                    $data['key'] = 'price';
                    $data['order'] = self::SORT_ORDER_ASC;
                } else if ($key == 'price_desc') {
                    $data['key'] = 'price';
                    $data['order'] = self::SORT_ORDER_DESC;
                } else {
                    $data['key'] = $key;
                    $data['order'] = self::SORT_ORDER_DESC;
                }
                $data['value'] = $value;
                $data['is_default'] = $this->catalogConfig->getProductListDefaultSortBy($this->storeManagerInterface->getStore()->getId()) == $key;
                $result[] = $data;
            }
        }
        return $result;
    }
}
