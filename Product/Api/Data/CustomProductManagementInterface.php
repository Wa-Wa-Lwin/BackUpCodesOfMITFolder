<?php

namespace MIT\Product\Api\Data;

use Magento\Catalog\Api\Data\ProductInterface;

interface CustomProductManagementInterface extends ProductInterface
{

    const KEY_IS_WISH_LIST = 'is_wish_list';
    const KEY_PRODUCT_URL = 'product_url';
    const KEY_GROUP_PRODUCT_LIST = 'group_product_list';
    const KEY_AVERAGE_RATING = 'average_rating';
    const KEY_WISHLIST_QTY = 'wishlist_qty';
    const KEY_WISHLIST_ITEM_ID = 'wishlist_item_id';
    const KEY_SELECTED_PRODUCT_ID = 'selected_product_id';
    const STOCK_QTY = 'qty';
    const KEY_DISCOUNT_LABEL = 'discount_label';

    /**
     * set rating list
     * @param \Magento\Review\Model\ResourceModel\Review\Collection[]
     * @return $this
     */
    public function setRating(array $ratingList = null);

    /**
     * get rating list
     * @return \Magento\Review\Model\ResourceModel\Review\Collection[]|[]
     */
    public function getRating();

    /**
     * set regular price
     * @param float $regularPrice
     * @return $this
     */
    public function setRegularPrice(float $regularPrice);

    /**
     * get regular price
     * @return float
     */
    public function getRegularPrice();

    /**
     * set final price
     * @param float $finalPrice
     * @return $this
     */
    public function setRealPrice(float $finalPrice);

    /**
     * get final price
     * @return float
     */
    public function getRealPrice();

    /**
     * set discount percentage
     * @param string $discount
     * @return $this
     */
    public function setDiscountPercentage(string $discount);

    /**
     * get discount percentage
     * @return string
     */
    public function getDiscountPercentage();

    /**
     * set weight unit
     * @param string $weightUnit
     * @return $this
     */
    public function setWeightUnit(string $weightUnit);

    /**
     * get weight unit
     * @return string
     */
    public function getWeightUnit();

    /**
     * set configurable product list
     * @param \MIT\Product\Api\Data\CustomProductManagementInterface[] $configProductList
     * @return $this
     */
    public function setConfigurableProductList(array $configProductList = null);

    /**
     * get configurable product list
     * @return \MIT\Product\Api\Data\CustomProductManagementInterface[]|[]
     */
    public function getConfigurableProductList();

    /**
     * set can review
     * @param bool $isReview
     * @return $this
     */
    public function setGuestCanReview($isReview);

    /**
     * get can review
     * @return bool
     */
    public function getGuestCanReview();

    /**
     * set review enabled
     * @param bool $reviewEnabled
     * @return $this
     */
    public function setReviewEnabled($reviewEnabled);

    /**
     * get review enabled
     * @return bool
     */
    public function getReviewEnabled();

    /**
     * get bundle min price
     * @return float
     */
    public function getMinPrice();

    /**
     * set bundle min price
     * @param float $bundleMinPrice
     * @return $this
     */
    public function setMinPrice(float $bundleMinPrice);

    /**
     * get bundle max price
     * @return float
     */
    public function getMaxPrice();

    /**
     * set bundle max price
     * @param float $bundleMaxPrice
     * @return $this
     */
    public function setMaxPrice(float $bundleMaxPrice);

    /**
     * set regular price
     * @param float $regularPrice
     * @return $this
     */
    public function setMaxRegularPrice(float $regularPrice);

    /**
     * get regular price
     * @return float
     */
    public function getMaxRegularPrice();

    /**
     * set final price
     * @param float $finalPrice
     * @return $this
     */
    public function setMaxRealPrice(float $finalPrice);

    /**
     * get final price
     * @return float
     */
    public function getMaxRealPrice();

    /**
     * set product url
     * @param string $url
     * @return $this
     */
    public function setProductShareUrl($url);

    /**
     * get product url
     * @return string
     */
    public function getProductShareUrl();

    /**
     * set is wish list
     * @param bool $isWishList
     * @return $this
     */
    public function setIsWishList($isWishList);

    /**
     * get is wish list
     * @return bool
     */
    public function getIsWishList();

    /**
     * set group product list
     * @param \MIT\Product\Api\Data\GroupProductInterface[] $groupProductList
     * @return $this
     */
    public function setGroupProductList(array $groupProductList = null);

    /**
     * get group product list
     * @return \MIT\Product\Api\Data\GroupProductInterface[]|[]
     */
    public function getGroupProductList();

    /**
     * set product average rating
     * @param string $averageRating
     * @return $this
     */
    public function setAverageRating($averagRating);

    /**
     * get product average rating
     * @return string
     */
    public function getAverageRating();

    /**
     * set wishlist qty
     * @param int $qty
     * @return $this
     */
    public function setWishlistQty($qty);

    /**
     * get wishlist qty
     * @return int
     */
    public function getWishlistQty();

    /**
     * set wishlist item id
     * @param string $wishlistItemId
     * @return $this
     */
    public function setWishlistItemId($wishlistItemId);

    /**
     * get wishlist item id
     * @return int
     */
    public function getWishlistItemId();

    /**
     * set selected product id
     * @param string $selectedProductId
     * @return $this
     */
    public function setSelectedProductId($selectedProductId);

    /**
     * get selected product id
     * @return int
     */
    public function getSelectedProductId();

    /**
     * set stock quantity
     * @param int $qty
     * @return $this
     */
    public function setStockQty($qty);

    /**
     * get stock qty
     * @return int
     */
    public function getStockQty();

    /**
     * set discount label
     * @param \MIT\Product\Api\Data\DiscountLabelInterface $discountLabel
     * @return $this
     */
    public function setDiscountLabel($discountLabel);

    /**
     * get discount label
     * @return \MIT\Product\Api\Data\DiscountLabelInterface
     */
    public function getDiscountLabel();
}
