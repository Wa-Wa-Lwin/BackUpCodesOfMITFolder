<?php

namespace MIT\ProductSorter\Plugin\Magento\Catalog\Model;

class Config
{

    public function afterGetAttributeUsedForSortByArray(
        \Magento\Catalog\Model\Config $subject,
        $result
    ) {

        $modifiedResult = [];
        foreach ($result as $key => $val) {
            if ($key === 'price') {
                $modifiedResult[$key . '_asc'] = $val . ': Low to high ';
                $modifiedResult[$key . '_desc'] = $val . ': High to low ';
            } else if ($key === 'name' || $key === 'position') {
                continue;
            } else {
                $modifiedResult[$key] = $val;
            }
        }
        return $modifiedResult;
    }
}
