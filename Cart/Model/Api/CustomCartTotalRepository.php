<?php

namespace MIT\Cart\Model\Api;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Api\Data\TotalsInterfaceFactory;
use MIT\Cart\Api\Data\CustomTotalsInterfaceFactory;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Model\Cart\TotalsConverter;
use Magento\Quote\Model\ShippingAddressManagement;
use Magento\Store\Model\StoreManagerInterface;
use MIT\Cart\Api\CustomCartTotalRepositoryInterface;
use MIT\Cart\Api\Data\CustomTotalsItemInterfaceFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;


class CustomCartTotalRepository implements CustomCartTotalRepositoryInterface
{
    /**
     * Cart totals factory.
     *
     * @var CustomTotalsInterfaceFactory
     */
    private $totalsFactory;

    /**
     * Cart totals factory.
     *
     * @var TotalsInterfaceFactory
     */
    private $totalsInterfaceFactory;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ItemConverter
     */
    private $itemConverter;

    /**
     * @var CouponManagementInterface
     */
    protected $couponService;

    /**
     * @var TotalsConverter
     */
    protected $totalsConverter;

    /**
     * @var ShippingAddressManagement
     */
    private $shippingAddressManagement;

    /**
     * @var CustomTotalsItemInterfaceFactory
     */
    private $customTotalsItemInterfaceFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @param CustomTotalsInterfaceFactory $totalsFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param CouponManagementInterface $couponService
     * @param TotalsConverter $totalsConverter
     * @param ItemConverter $converter
     * @param TotalsInterfaceFactory $totalsInterfaceFactory
     * @param ShippingAddressManagement  $shippingAddressManagement
     * @param CustomTotalsItemInterfaceFactory $customTotalsItemInterfaceFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param ProductRepository $productRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        CustomTotalsInterfaceFactory $totalsFactory,
        CartRepositoryInterface $quoteRepository,
        DataObjectHelper $dataObjectHelper,
        CouponManagementInterface $couponService,
        TotalsConverter $totalsConverter,
        ItemConverter $converter,
        TotalsInterfaceFactory $totalsInterfaceFactory,
        ShippingAddressManagement  $shippingAddressManagement,
        CustomTotalsItemInterfaceFactory $customTotalsItemInterfaceFactory,
        StoreManagerInterface $storeManagerInterface,
        ProductRepository $productRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->totalsFactory = $totalsFactory;
        $this->quoteRepository = $quoteRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->couponService = $couponService;
        $this->totalsConverter = $totalsConverter;
        $this->itemConverter = $converter;
        $this->totalsInterfaceFactory = $totalsInterfaceFactory;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->customTotalsItemInterfaceFactory = $customTotalsItemInterfaceFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->productRepository = $productRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @inheritdoc
     */
    public function get($cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->isVirtual()) {
            $addressTotalsData = $quote->getBillingAddress()->getData();
            $addressTotals = $quote->getBillingAddress()->getTotals();
        } else {
            $addressTotalsData = $quote->getShippingAddress()->getData();
            $addressTotals = $quote->getShippingAddress()->getTotals();
        }

        unset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);

        /** @var TotalsInterface $quoteTotals */
        $quoteTotals = $this->totalsInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $quoteTotals,
            $addressTotalsData,
            TotalsInterface::class
        );
        $items = array_map([$this->itemConverter, 'modelToDataObject'], $quote->getAllVisibleItems());
        $customItems = [];

        foreach ($items as $key => $item) {
            $customItem = $this->customTotalsItemInterfaceFactory->create();
            $customItem->setItemId($item->getItemId());
            $customItem->setPrice($item->getPrice());
            $customItem->setBasePrice($item->getBasePrice());
            $customItem->setQty($item->getQty());
            $customItem->setRowTotal($item->getRowTotal());
            $customItem->setBaseRowTotal($item->getBaseRowTotal());
            $customItem->setRowTotalWithDiscount($item->getRowTotalWithDiscount());
            $customItem->setTaxAmount($item->getTaxAmount());
            $customItem->setBaseTaxAmount($item->getTaxAmount());

            $customItem->setTaxPercent($item->getTaxPercent());
            $customItem->setDiscountAmount($item->getDiscountAmount());
            $customItem->setBaseDiscountAmount($item->getBaseDiscountAmount());
            $customItem->setDiscountPercent($item->getDiscountPercent());
            $customItem->setPriceInclTax($item->getPriceInclTax());
            $customItem->setBasePriceInclTax($item->getBasePriceInclTax());
            $customItem->setRowTotalInclTax($item->getRowTotalInclTax());
            $customItem->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax());
            $customItem->setOptions($item->getOptions());
            $customItem->setWeeeTaxAppliedAmount($item->getWeeeTaxAppliedAmount());
            $customItem->setWeeeTaxApplied($item->getWeeeTaxApplied());
            $customItem->setName($item->getName());

            $customItem->setImgPath($this->getImageFullPathBySku($quote->getAllVisibleItems()[$key]));
            $customItems[] = $customItem;
        }
        $calculatedTotals = $this->totalsConverter->process($addressTotals);

        $customQuoteTotals = $this->totalsFactory->create();

        $quoteTotals->setTotalSegments($calculatedTotals);

        $amount = $quoteTotals->getGrandTotal() - $quoteTotals->getTaxAmount();
        $amount = $amount > 0 ? $amount : 0;
        $quoteTotals->setCouponCode($this->couponService->get($cartId));
        $quoteTotals->setGrandTotal($amount);
        $quoteTotals->setItemsQty($quote->getItemsQty());
        $quoteTotals->setBaseCurrencyCode($quote->getBaseCurrencyCode());
        $quoteTotals->setQuoteCurrencyCode($quote->getQuoteCurrencyCode());
        $customQuoteTotals = $quoteTotals;
        if (!$quote->isVirtual()) {
            $address = $this->shippingAddressManagement->get($cartId);
            $customQuoteTotals->setShippingAddress($address);
        }
        $customQuoteTotals->setItems($customItems);

        return $customQuoteTotals;
    }

    /**
     * @inheritdoc
     */
    public function getTotalForGuest($cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->get($quoteIdMask->getQuoteId());
    }

    /**
     * getImage path by product
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return string
     */
    private function getImageFullPathBySku($item)
    {
        $currentStore = $this->storeManagerInterface->getStore();
        $baseUrl = $currentStore->getBaseUrl();
        if ($item->getProductType() == 'bundle') {
            $product = $this->productRepository->getById($item->getProduct()->getId());
            return $baseUrl . 'media/catalog/product' .  $product->getData('image');
        } else {
            $product = $this->productRepository->get($item->getSku());
            return $baseUrl . 'media/catalog/product' .  $product->getData('image');
        }
    }
}
