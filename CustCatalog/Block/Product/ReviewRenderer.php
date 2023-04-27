<?php

namespace MIT\CustCatalog\Block\Product;

use Magento\Review\Block\Product\ReviewRenderer as ProductReviewRenderer;
use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\AppendSummaryDataFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\ReviewSummaryFactory;
use Magento\Review\Observer\PredispatchReviewObserver;
use Magento\Store\Model\ScopeInterface;

class ReviewRenderer extends ProductReviewRenderer
{

    protected $_availableTemplates = [
        self::FULL_VIEW => 'MIT_CustCatalog::helper/summary.phtml',
        self::SHORT_VIEW => 'MIT_CustCatalog::helper/summary_short.phtml',
    ];

    /**
     * Review model factory
     *
     * @var ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var ReviewSummaryFactory
     */
    private $reviewSummaryFactory;

    /**
     * @var AppendSummaryDataFactory
     */
    private $appendSummaryDataFactory;

    /**
     * @param Context $context
     * @param ReviewFactory $reviewFactory
     * @param array $data
     * @param ReviewSummaryFactory|null $reviewSummaryFactory
     * @param AppendSummaryDataFactory|null $appendSummaryDataFactory
     */
    public function __construct(
        Context $context,
        ReviewFactory $reviewFactory,
        array $data = [],
        ReviewSummaryFactory $reviewSummaryFactory = null,
        AppendSummaryDataFactory $appendSummaryDataFactory = null
    ) {
        parent::__construct($context, $reviewFactory, $data, $reviewSummaryFactory, $appendSummaryDataFactory);
        $this->_reviewFactory = $reviewFactory;
        $this->reviewSummaryFactory = $reviewSummaryFactory ??
            ObjectManager::getInstance()->get(ReviewSummaryFactory::class);
        $this->appendSummaryDataFactory = $appendSummaryDataFactory ??
            ObjectManager::getInstance()->get(AppendSummaryDataFactory::class);
    }

    /**
     * Get review summary html
     *
     * @param Product $product
     * @param string $templateType
     * @param bool $displayIfNoReviews
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getReviewsSummaryHtml(
        Product $product,
        $templateType = self::DEFAULT_VIEW,
        $displayIfNoReviews = false
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/review.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        if ($product->getRatingSummary() === null) {
            $this->appendSummaryDataFactory->create()
                ->execute(
                    $product,
                    $this->_storeManager->getStore()->getId(),
                    Review::ENTITY_PRODUCT_CODE
                );
        }

        if (null === $product->getRatingSummary() && !$displayIfNoReviews) {
            // return '';
        }
        // pick template among available
        if (empty($this->_availableTemplates[$templateType])) {
            $logger->info('-------------------------');
            $logger->info(strval($product->getName()));
            $logger->info('null');
            $logger->info('-------------------------');
            $templateType = self::DEFAULT_VIEW;
        }
        $this->setTemplate($this->_availableTemplates[$templateType]);

        $this->setDisplayIfEmpty($displayIfNoReviews);

        $this->setProduct($product);

        return $this->toHtml();
    }
}
