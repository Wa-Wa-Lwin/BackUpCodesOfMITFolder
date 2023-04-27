<?php

namespace MIT\WeeklyPromo\Observer\Adminhtml;

use MIT\WeeklyPromo\Helper\Data;
use MIT\WeeklyPromo\Helper\PromoRetriever;

class ControllerSaleruleAfterSaveCustom implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var PromoRetriever
     */
    private $promoRetriever;

    public function __construct(
        Data $helper,
        PromoRetriever $promoRetriever
    ) {
        $this->helper = $helper;
        $this->promoRetriever = $promoRetriever;
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
        $id = $observer->getData('id');
        if ($id) {
            $skus = $this->promoRetriever->getWeeklyPromoList();
            $this->helper->updateProductCategory($skus);
        }
        return $this;
    }
}
