<?php
namespace MIT\Discount\Plugin\Checkout\CustomerData;

class Cart
{
    protected $checkoutSession;
    protected $checkoutHelper;
    protected $quote;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
    }
    
    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    protected function getDiscountAmount()
    {
        $discountAmount = 0;
        foreach($this->getQuote()->getAllVisibleItems() as $item){
            $discountAmount += ($item->getDiscountAmount() ? $item->getDiscountAmount() : 0);
        }
        return $discountAmount;
    }

    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $result['discount_amount_no_html'] = -$this->getDiscountAmount();
        $result['discount_amount'] = $this->checkoutHelper->formatPrice(-$this->getDiscountAmount());

        return $result;
    }
}