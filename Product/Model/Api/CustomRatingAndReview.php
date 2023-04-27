<?php

namespace MIT\Product\Model\Api;

use DateTime;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use MIT\Product\Api\CustomRatingAndReviewInterface;
use MIT\Product\Api\Data\ReviewManagementSearchResultsInterfaceFactory;
use MIT\Product\Model\CustomKeyValFactory;
use MIT\Product\Model\ReviewManagementFactory;

class CustomRatingAndReview implements CustomRatingAndReviewInterface
{

    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $ratingFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $request;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var ReviewManagementFactory
     */
    private $reviewManagementFactory;

    /**
     * @var ReviewManagementSearchResultsInterfaceFactory
     */
    private $reviewManagementSearchResultsInterfaceFactory;

    /**
     * @var CustomKeyValFactory
     */
    private $customKeyValFactory;

    /**
     * @var ManagerInterface
     */
    private $managerInterface;

    public function __construct(
        \Magento\Review\Model\RatingFactory $ratingFactory,
        StoreManagerInterface $storeManagerInterface,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\Webapi\Rest\Request $request,
        ProductFactory $productFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        ReviewManagementFactory $reviewManagementFactory,
        ReviewManagementSearchResultsInterfaceFactory $reviewManagementSearchResultsInterfaceFactory,
        CustomKeyValFactory $customKeyValFactory,
        ManagerInterface $managerInterface
    ) {
        $this->ratingFactory = $ratingFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->reviewFactory = $reviewFactory;
        $this->request = $request;
        $this->productFactory = $productFactory;
        $this->objectFactory = $objectFactory;
        $this->reviewManagementFactory = $reviewManagementFactory;
        $this->reviewManagementSearchResultsInterfaceFactory = $reviewManagementSearchResultsInterfaceFactory;
        $this->customKeyValFactory = $customKeyValFactory;
        $this->managerInterface = $managerInterface;
    }

    /**
     * @inheritdoc
     */
    public function getRatingList()
    {
        $ratingCollection = $this->ratingFactory->create()
            ->getResourceCollection()
            ->addFieldToSelect(array('rating_id', 'rating_code'))
            ->addEntityFilter(
                'product'
            )->setPositionOrder()->setStoreFilter(
                $this->storeManagerInterface->getStore()->getId()
            )->addRatingPerStoreName(
                $this->storeManagerInterface->getStore()->getId()
            )->load();

        $ratingCollection->getSelect()->join('rating_option as viewed_item', 'main_table.rating_id = viewed_item.rating_id', 'option_id');    // Add this join statement
        $ratingCollection->getSelect()
            ->order('main_table.rating_id ASC')
            ->order('viewed_item.position ASC');

        $result = [];
        foreach ($ratingCollection->getData() as $rating) {
            $result[$rating['rating_id']][] = $rating;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function submitReview($customerId)
    {
        $data = $this->request->getBodyParams();
        $obj = $this->objectFactory->create();
        $rating = [];
        if (isset($data['ratings'])) {
            $rating = $data['ratings'];
        }

        /** @var \Magento\Review\Model\Review $review */
        $review = $this->reviewFactory->create()->setData($data);
        $review->unsetData('review_id');
        $validate = $review->validate();
        if ($validate === true && isset($data['productId'])) {
            if ($this->productFactory->create()->load($data['productId'])->getId()) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($data['productId'])
                        ->setStatusId(Review::STATUS_PENDING)
                        ->setCustomerId($customerId)
                        ->setStoreId($this->storeManagerInterface->getStore()->getId())
                        ->setStores([$this->storeManagerInterface->getStore()->getId()])
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->ratingFactory->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId($customerId)
                            ->addOptionVote($optionId, $data['productId']);
                    }

                    $review->aggregate();
                    $obj->setItem(array('success' => true, 'message' => 'You submitted your review for moderation.'));
                    $this->managerInterface->dispatch('review_save_after', [
                        'data_object'        => $review
                    ]);
                    return $obj->getData();
                } catch (\Exception $e) {
                    $obj->setItem(array('success' => false, 'message' => 'We can\'t post your review right now.'));
                    return $obj->getData();
                }
            }
        }
        $obj->setItem(array('success' => false, 'message' => 'We can\'t post your review right now.'));
        return $obj->getData();
    }

    /**
     * @inheritdoc
     */
    public function getProductReviewListByCustomerId($customerId, SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->reviewFactory->create()->getCollection()
            ->addFieldToFilter('detail.customer_id', $customerId)->load();
        $collection->getSelect()->joinInner(['vote' => 'rating_option_vote'], 'main_table.review_id = vote.review_id', '*');
        $collection->getSelect()->joinInner(['rating' => 'rating'], 'vote.rating_id = rating.rating_id', 'rating_code');
        $collection->addFieldToFilter('rating.rating_code', 'Quality');
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->getCurPage($searchCriteria->getCurrentPage());
        $collection->setOrder('main_table.review_id');
        $collection->load();
        $currentStore = $this->storeManagerInterface->getStore();
        $baseUrl = $currentStore->getBaseUrl();

        $searchResult = $this->reviewManagementSearchResultsInterfaceFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);

        $reviewList = [];
        foreach ($collection->getData() as $review) {
            if (
                isset($review['review_id']) && isset($review['created_at']) && isset($review['title']) &&
                isset($review['detail']) && isset($review['percent']) && isset($review['entity_pk_value']) &&
                isset($review['rating_code'])
            ) {
                $reviewData = $this->reviewManagementFactory->create();
                $reviewData->setReviewId($review['review_id']);
                $date = new DateTime($review['created_at']);
                $reviewData->setCreatedAt($date->format('d/m/Y'));
                $reviewData->setTitle($review['title']);
                $reviewData->setSummary($review['detail']);
                $reviewData->setProductId($review['entity_pk_value']);
                $rating = $this->customKeyValFactory->create();
                $rating->setKey($review['rating_code']);
                $rating->setValue($review['percent']);
                $reviewData->setRating([$rating]);

                $productAttr = $this->getProductAndRatingData($reviewData->getProductId(), $baseUrl);
                if (
                    count($productAttr) == 4 && isset($productAttr['name']) && isset($productAttr['imgPath']) &&
                    isset($productAttr['average']) && isset($productAttr['counts'])
                ) {
                    $reviewData->setProductName($productAttr['name']);
                    $reviewData->setProductImgPath($productAttr['imgPath']);
                    $reviewData->setAverageRating($productAttr['average']);
                    $reviewData->setNoOfReviews($productAttr['counts']);
                }
                $reviewList[] = $reviewData;
            }
        }
        $searchResult->setItems($reviewList);
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function getReviewByIdAndCustomerId($customerId, $reviewId)
    {
        $collection = $this->reviewFactory->create()->getCollection()
            ->addFieldToFilter('detail.customer_id', $customerId)->load();
        $collection->getSelect()->joinInner(['vote' => 'rating_option_vote'], 'main_table.review_id = vote.review_id', '*');
        $collection->getSelect()->joinInner(['rating' => 'rating'], 'vote.rating_id = rating.rating_id', 'rating_code');
        $collection->addFieldToFilter('main_table.review_id', $reviewId);
        $collection->setOrder('main_table.review_id');
        $collection->load();
        $currentStore = $this->storeManagerInterface->getStore();
        $baseUrl = $currentStore->getBaseUrl();
        $reviewData = $this->reviewManagementFactory->create();

        foreach ($collection->getData() as $review) {
            if (
                isset($review['review_id']) && isset($review['created_at']) && isset($review['title']) &&
                isset($review['detail']) && isset($review['percent']) && isset($review['entity_pk_value']) &&
                isset($review['rating_code'])
            ) {
                $reviewData->setReviewId($review['review_id']);
                $date = new DateTime($review['created_at']);
                $reviewData->setCreatedAt($date->format('d/m/Y'));
                $reviewData->setTitle($review['title']);
                $reviewData->setSummary($review['detail']);
                $reviewData->setProductId($review['entity_pk_value']);
                $rating = $this->customKeyValFactory->create();
                $rating->setKey($review['rating_code']);
                $rating->setValue($review['percent']);
                if ($reviewData->getRating()) {
                    $ratingArr = $reviewData->getRating();
                    $ratingArr[] = $rating;
                    $reviewData->setRating($ratingArr);
                } else {
                    $reviewData->setRating([$rating]);
                }

                $productAttr = $this->getProductAndRatingData($reviewData->getProductId(), $baseUrl);
                if (
                    count($productAttr) == 4 && isset($productAttr['name']) && isset($productAttr['imgPath']) &&
                    isset($productAttr['average']) && isset($productAttr['counts'])
                ) {
                    $reviewData->setProductName($productAttr['name']);
                    $reviewData->setProductImgPath($productAttr['imgPath']);
                    $reviewData->setAverageRating($productAttr['average']);
                    $reviewData->setNoOfReviews($productAttr['counts']);
                }
            }
        }
        if (!$reviewData->getId()) {
            throw new NoSuchEntityException(__('Review with id "%1" does not exist.', $reviewId));
        }
        return $reviewData;
    }

    /**
     * get product name and image url by product id
     * @param int $productId
     * @param string $baseUrl
     * @return array
     */
    private function getProductAndRatingData($productId, $baseUrl)
    {
        $product = $this->productFactory->create()->load($productId);
        $productAttr = [];
        if ($product->getId()) {
            $productAttr = $this->getProductAvgRatingAndReviewCounts($product);
            if ($product->getCustomAttributes()) {
                $productAttr['name'] = $product->getName();
                foreach ($product->getCustomAttributes() as $customAttribute) {
                    if ($customAttribute->getAttributeCode() == 'image') {
                        $productAttr['imgPath'] = $baseUrl . 'media/catalog/product' .  $customAttribute->getValue();
                    }
                }
            }
        }
        return $productAttr;
    }

    /**
     * get product average rating and review counts
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    private function getProductAvgRatingAndReviewCounts($product)
    {
        $this->reviewFactory->create()->getEntitySummary($product, $this->storeManagerInterface->getStore()->getId());
        return array('average' => $product->getRatingSummary()->getRatingSummary(), 'counts' => $product->getRatingSummary()->getReviewsCount());
    }
}

