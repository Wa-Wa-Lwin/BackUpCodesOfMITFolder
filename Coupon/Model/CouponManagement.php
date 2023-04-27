<?php

namespace MIT\Coupon\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\CouponManagement as ModelCouponManagement;
use MIT\Coupon\Helper\CouponChecker;

class CouponManagement extends ModelCouponManagement
{
    /**
     * @var CouponChecker
     */
    private $couponChecker;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManagerInterface;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        CouponChecker $couponChecker,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface
    ) {
        parent::__construct($quoteRepository);
        $this->couponChecker = $couponChecker;
        $this->objectManagerInterface = $objectManagerInterface;
    }

    /**
     * @inheritDoc
     */
    public function set($cartId, $couponCode)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }
        if (!$quote->getStoreId()) {
            throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__('The coupon code couldn\'t be applied: ' . $e->getMessage()), $e);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __("The coupon code couldn't be applied. Verify the coupon code and try again."),
                $e
            );
        }
        if ($quote->getCouponCode() != $couponCode) {
            $escaper = $this->objectManagerInterface->get(\Magento\Framework\Escaper::class);
            $requireAmtStr = $this->couponChecker->checkToShowCustomMessage($couponCode, $quote);
            if ($requireAmtStr) {
                throw new NoSuchEntityException(__(
                    'Add "%1" worth of items to use coupon code "%2".',
                    $escaper->escapeHtml($requireAmtStr),
                    $escaper->escapeHtml($couponCode)
                ));
            } else {
                throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
            }
        }
        return true;
    }
}
