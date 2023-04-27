<?php

namespace MIT\Coupon\Plugin\CustomerData;

use Magento\Store\Model\StoreManagerInterface;
use MIT\Coupon\Helper\CouponChecker;

class Cart {

    /**
     * @var CouponChecker
     */
    private $couponChecker;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        CouponChecker $couponChecker,
        StoreManagerInterface $storeManagerInterface
    )
    {
        $this->couponChecker = $couponChecker;
        $this->storeManagerInterface = $storeManagerInterface;
    }
        
    /**
     * Add Remain amt to use coupon data to result
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $message = $this->couponChecker->getCouponMessageMiniCart();
        if ($message && count($message) == 2) {
            $result['couponDsAmt'] = $message[0];
            $result['couponDsCode'] = $message[1];
            $result['localeEng'] = $this->storeManagerInterface->getStore()->getCode() == 'mm' ? false : true ;
        }
        return $result;
    }
}