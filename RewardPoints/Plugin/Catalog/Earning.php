<?php

namespace MIT\RewardPoints\Plugin\Catalog;

use Mageplaza\RewardPointsPro\Plugin\Catalog\Earning as CatalogEarning;
use Closure;
use Exception;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

class Earning extends CatalogEarning
{

    /**
     * @param AbstractProduct $subject
     * @param Closure $proceed
     * @param Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     *
     * @return string
     * @throws LocalizedException
     */
    public function aroundGetReviewsSummaryHtml(
        AbstractProduct $subject,
        Closure $proceed,
        $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
        $result = $proceed($product, $templateType, $displayIfNoReviews);
        $action = $subject->getRequest()->getFullActionName();
        if ($action === 'catalog_category_view') {
            $product = $this->productFactory->create()->load($product->getId());
        }

        $html = '';
        if (
            $this->pointHelper->isEnabled()
            && (in_array($action, ['catalog_category_view', 'cms_index_index', ''], true)
                || strpos($subject->getRequest()->getFullActionName(), 'dailydeal') !== false)
            && ($pointEarn = $this->catalogEarning->create()->getPointEarnFromRules($product))
        ) {
            try {
                $pointLabel = $this->pointHelper->format($pointEarn);
                $label      = in_array($product->getTypeId(), ['grouped', 'bundle', 'configurable']) ?
                    __('Earn from %1', $pointLabel) : __('Earn %1', $pointLabel);

                $html = '<div class="catalog-points" style="margin-bottom:2px">';
                $html .= $this->pointHelper->getIconHtml();
                $html .= '<div class="mp-point-label" style="display: inline-block">
<span class="points" style="margin-left: 5px">' . $label . '</span></div><div class="clr"></div></div>';
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        } else if ($this->pointHelper->isEnabled() && (in_array($action, ['catalog_category_view', 'cms_index_index', ''], true)
            || strpos($subject->getRequest()->getFullActionName(), 'dailydeal') !== false)) {
            $html = '<div class="empty-catalog-points" style="height: 22px;"></div>';
        }

        return $html . $result;
    }
}
