<?php

namespace MIT\Coupon\Controller\Cart;

use Magento\Checkout\Controller\Cart\CouponPost as CartCouponPost;
use MIT\Coupon\Helper\CouponChecker;

class CouponPost extends CartCouponPost
{
    /**
     * @var CouponChecker
     */
    private $couponChecker;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        CouponChecker $couponChecker
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $couponFactory,
            $quoteRepository
        );
        $this->couponChecker = $couponChecker;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {

        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code'));

        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }

        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }

            if ($codeLength) {
                $escaper = $this->_objectManager->get(\Magento\Framework\Escaper::class);
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId()) {
                        $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                } else {
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode()) {
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $requireAmtStr = $this->couponChecker->checkToShowCustomMessage($couponCode, $cartQuote);
                        if ($requireAmtStr) {
                            $this->messageManager->addNoticeMessage(
                                __(
                                    'Add "%1" worth of items to use coupon code "%2".',
                                    $escaper->escapeHtml($requireAmtStr),
                                    $escaper->escapeHtml($couponCode)
                                )
                            );
                        } else {
                            $this->messageManager->addErrorMessage(
                                __(
                                    'The coupon code "%1" is not valid.',
                                    $escaper->escapeHtml($couponCode)
                                )
                            );
                        }
                    }
                }
            } else {
                $this->messageManager->addSuccessMessage(__('You canceled the coupon code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We cannot apply the coupon code.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }

        return $this->_goBack();
    }
}
