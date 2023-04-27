<?php

namespace MIT\SalesRule\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory as DiscountCalculatorFactory;

class CalculatorFactory extends DiscountCalculatorFactory {

    /**
     * @var array
     */
    protected $classByType = [
        \Magento\SalesRule\Model\Rule::TO_PERCENT_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\ToPercent::class,
        \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\ByPercent::class,
        \Magento\SalesRule\Model\Rule::TO_FIXED_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\ToFixed::class,
        \Magento\SalesRule\Model\Rule::BY_FIXED_ACTION => \Magento\SalesRule\Model\Rule\Action\Discount\ByFixed::class,
        \Magento\SalesRule\Model\Rule::CART_FIXED_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed::class,
        \Magento\SalesRule\Model\Rule::BUY_X_GET_Y_ACTION =>
            \Magento\SalesRule\Model\Rule\Action\Discount\BuyXGetY::class,
        'buy_x_get_y_amount' => \MIT\SalesRule\Model\Rule\Action\Discount\BuyXGetYAmount::class
    ];
}