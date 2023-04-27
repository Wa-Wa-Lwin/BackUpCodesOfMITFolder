<?php

namespace MIT\Customer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class CouponHelper extends AbstractHelper
{

    /**
     * @var CouponCollectionFactory
     */
    private $couponCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    public function __construct(
        CouponCollectionFactory $couponCollectionFactory,
        StoreManagerInterface $storeManagerInterface,
        TimezoneInterface $timezoneInterface
    ) {
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->timezoneInterface = $timezoneInterface;
    }

    public function getCouponMessage()
    {
        $couponCode = '';
        $message = '';
        $collection = $this->couponCollectionFactory->create()->addFieldToSelect(['code']);
        $collection->getSelect()->joinInner('salesrule', 'main_table.rule_id = salesrule.rule_id', ['rule_id']);
        $collection->getSelect()
            ->where('salesrule.is_active = ? ', 1)
            ->where('salesrule.from_date is null or salesrule.from_date <= ? ', $this->timezoneInterface->date()->format('Y-m-d'))
            ->where('salesrule.to_date is null or salesrule.to_date >= ? ', $this->timezoneInterface->date()->format('Y-m-d'));
        foreach ($collection as $item) {
            $couponCode = $item['code'];
        }

        if ($couponCode) {
            $url = $this->storeManagerInterface->getStore()->getBaseUrl();
            $message = sprintf(
                'အနည်းဆုံးကျပ်တစ်သောင်းဖိုးဝယ်ယူပြီး2000ကျပ်Discountရရှိစေရန် %s မှာ couponcodeဖြစ်တဲ့ %s လေးကိုရိုက်ထည့်ပြီးမှာယူလိုက်တော့နော်။',
                $url,
                $couponCode
            );
        }

        return $message;

//အနည်းဆုံးကျပ်တစ်သောင်းဖိုးဝယ်ယူပြီး2000ကျပ်Discountရရှိစေရန် https://gmpshopping .comမှာcouponcodeဖြစ်တဲ့GMP2000လေးကိုရိုက်ထည့်ပြီးမှာယူလိုက်တော့နော်။
        // 2000ကျပ်Discountရရှိစေရန် https://gmpshopping.com မှာ GMP2000ကိုရိုက်ထည့်ပြီးမှာယူလိုက်တော့နော်။

    }
}
