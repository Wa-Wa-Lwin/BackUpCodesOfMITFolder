<?php


namespace MIT\Product\Observer\Currency;

use Magento\Framework\Locale\Currency;
use Magento\Store\Model\StoreManagerInterface;

class DisplayOptionsForming implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $data = $observer->getEvent()->getCurrencyOptions();
        $code = $this->storeManagerInterface->getStore()->getCode();
        $name = $this->storeManagerInterface->getStore()->getName();
        if (strtolower($code) == 'mm') {
            $data->setData(Currency::CURRENCY_OPTION_SYMBOL, ' ကျပ်');
        }
        $data->setData('position', \Magento\Framework\Currency::RIGHT);

        return $this;
    }
}
