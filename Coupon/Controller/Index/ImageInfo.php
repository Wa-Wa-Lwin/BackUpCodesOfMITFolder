<?php

namespace MIT\Coupon\Controller\Index;

class ImageInfo extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $jsonHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    private $cartItemRepositoryInterface;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface
     */
    private $itemResolver;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepositoryInterface,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface $itemResolver
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->checkoutSession = $checkoutSession;
        $this->cartItemRepositoryInterface = $cartItemRepositoryInterface;
        $this->imageHelper = $imageHelper;
        $this->itemResolver = $itemResolver;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = [];
            $quote = $this->checkoutSession->getQuote();
            $quoteId = $quote->getId();
            $items = $this->cartItemRepositoryInterface->getList($quoteId);
            $itemId = $this->getRequest()->getParam('item_id');
            if ($itemId) {
                //$result[$itemId] = $this->getProductImageData($itemId);
                foreach($items as $item) {
                    if ($itemId == $item->getItemId()) {
                       $result[$itemId] = $this->getProductImageData($item);
                    }
                }
            }

            return $this->jsonResponse($result);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Get product image data
     *
     * @param \Magento\Quote\Model\Quote\Item $cartItem
     *
     * @return array
     */
    private function getProductImageData($cartItem)
    {
        $imageHelper = $this->imageHelper->init(
            $this->itemResolver->getFinalProduct($cartItem),
            'mini_cart_product_thumbnail'
        );
        $imageData = [
            'src' => $imageHelper->getUrl(),
            'alt' => $imageHelper->getLabel(),
            'width' => $imageHelper->getWidth(),
            'height' => $imageHelper->getHeight(),
        ];
        return $imageData;
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
