<?php

namespace MIT\SalesRule\Plugin\Magento\SalesRule\Model\Rule\Action;

class SimpleActionOptionsProvider {

    public function afterToOptionArray(
        \Magento\SalesRule\Model\Rule\Action\SimpleActionOptionsProvider $subject,
        $result
    ) {
        $result[] =  ['label' => __('Buy X get Y amount(fixed amount)'), 'value' => 'buy_x_get_y_amount'];
        return $result;
    }
}