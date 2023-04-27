<?php

namespace MIT\WeeklyPromo\Model\Api;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Cms\Model\PageRepository;
use MIT\WeeklyPromo\Api\WeeklyPromotionInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use MIT\CatalogRule\Helper\customHtmlEditor;
use MIT\Product\Api\Data\CustomProductSearchResultsInterface;
use MIT\Product\Model\Api\CustomProduct;
use MIT\WeeklyPromo\Block\Widget\Promotion;
use MIT\WeeklyPromo\Helper\PromoRetriever;
use MIT\WeeklyPromo\Model\WeeklyPromotionManagementFactory;

class WeeklyPromotion implements WeeklyPromotionInterface
{
    /**
     * cms page identifier for home page
     */
    const HOME_PAGE_IDENTIFIER = ['home-gmp', 'home-gmp-62064337ed129'];

    const WEEKLY_PROMOTION_WIDGET_CLASS = 'MIT\WeeklyPromo\Block\Widget\Promotion';

    const WEEKLY_PROMOTION_WIDGET_END_SYMBOL = '}}';

    const INDEX_ZERO = 0;

    const NO_OF_STEP = 1;

    const WEEKLY_PROMO_TITLE_START_WORD = 'title="';

    const WEEKEY_PROMO_BTN_START_WORD = 'button_title="';

    const DOUBLE_QUOTE = '"';

    const NUMBER_OF_PRODUCT_START_WORD = 'no_of_products="';

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var customHtmlEditor
     */
    private $customHtmlEditor;

    /**
     * @var PromoRetriever
     */
    private $promoRetriever;

    /**
     * @var CustomProduct
     */
    private $customProduct;

    /**
     * @var WeeklyPromotionManagementFactory
     */
    private $weeklyPromotionManagementFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    public function __construct(
        PageRepository $pageRepository,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManagerInterface,
        customHtmlEditor $customHtmlEditor,
        PromoRetriever $promoRetriever,
        CustomProduct $customProduct,
        WeeklyPromotionManagementFactory $weeklyPromotionManagementFactory,
        CategoryFactory $categoryFactory
    ) {
        $this->pageRepository = $pageRepository;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->customHtmlEditor = $customHtmlEditor;
        $this->promoRetriever = $promoRetriever;
        $this->customProduct = $customProduct;
        $this->weeklyPromotionManagementFactory = $weeklyPromotionManagementFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @inheritdoc
     */
    public function getWeeklyPromotion()
    {
        $weeklyPromotion = $this->weeklyPromotionManagementFactory->create();
        $contentArr = $this->getPromotionContent();
        if (count($contentArr) >= 2) {
            if (count($contentArr) == 2) {
                $weeklyPromotion->setItems($this->getPromotionProducts());
            } else if (count($contentArr) == 3) {
                $weeklyPromotion->setItems($this->getPromotionProducts($contentArr[count($contentArr) - 1]));
            }
            $weeklyPromotion->setTitle($contentArr[0]);
            $weeklyPromotion->setBtnName($contentArr[1]);
            $weeklyPromotion->setCategoryId($this->getWeeklyPromotionCategoryId());
        }
        return $weeklyPromotion;
    }

    /**
     * get category id by url path
     * @param string $urlPath
     * @return int
     */
    private function getWeeklyPromotionCategoryId()
    {
        $categories = $this->categoryFactory->create()
            ->getCollection()
            ->addAttributeToFilter('url_path', str_replace(".html", '', substr(Promotion::DEFAULT_WEEKLY_PROMO_URL, 1)))
            ->addAttributeToSelect('*');
        return $categories->getFirstItem()->getId();
    }

    /**
     * get promotion content
     * @return array
     */
    private function getPromotionContent()
    {
        $pageContent = $this->getPageContent();
        if ($pageContent) {
            return $this->getTitleAndBtnName($pageContent->getContent());
        }
        return [];
    }


     /**
     * get sorted promotion products
     * @param int $productCount
     * @return \MIT\Product\Api\Data\CustomProductManagementInterface[]|[]
     */
    private function getPromotionProducts($productCount = 6)
    {
        $result = [];
        $skuString = $this->promoRetriever->getWeeklyPromoList();
        if ($skuString) {
            $skuArr = array_slice(explode(',', $skuString), self::INDEX_ZERO, $productCount);
            $filteredSku = $this->_filterBuilder
                ->setConditionType('in')
                ->setField('sku')
                ->setValue($skuArr)
                ->create();

            $filteredVisibility = $this->_filterBuilder
                ->setConditionType('eq')
                ->setField('visibility')
                ->setValue(4)
                ->create();

            $filterGroupList = [];
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredSku)->create();
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredVisibility)->create();

            $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList)->setPageSize($productCount)->setCurrentPage(1);

            /** @var CustomProductSearchResultsInterface $products */
            $products = $this->customProduct->getList($this->_searchCriteriaBuilder->create());
            for ($outer = self::INDEX_ZERO; $outer < count($skuArr); $outer++) {
                foreach ($products->getItems() as $item) {
                    if ($item->getSku() == $skuArr[$outer]) {
                        $result[] = $item;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * get page content by identifier and active status
     * @return \Magento\Cms\Api\Data\PageInterface|null
     */
    private function getPageContent()
    {
        $filteredActive = $this->_filterBuilder
            ->setConditionType('eq')
            ->setField('is_active')
            ->setValue(1)
            ->create();


        $identifier = $this->getCurrentStoreId() > 1 ? self::HOME_PAGE_IDENTIFIER[1] : self::HOME_PAGE_IDENTIFIER[0];
        $filteredIdentifier = $this->_filterBuilder
            ->setConditionType('eq')
            ->setField('identifier')
            ->setValue($identifier)
            ->create();

        $filterGroupList = [];
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredActive)->create();
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredIdentifier)->create();

        $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList);
        $page = $this->pageRepository->getList($this->_searchCriteriaBuilder->create());
        foreach ($page->getItems() as $data) {
            if ($data) {
                if ($data->getContent()) {
                    $data->setContent($this->customHtmlEditor->convertHTMLEntities($data->getContent()));
                }
                return $data;
            }
        }
        return null;
    }

    /**
     * get current store id
     * @return int
     */
    private function getCurrentStoreId()
    {
        return $this->storeManagerInterface->getStore()->getId();
    }

    /**
     * retrieve title and btn name from content
     * @param string $content
     * @return array
     */
    private function getTitleAndBtnName($content)
    {
        $dataArr = [];
        $firstIdx = strpos($content, self::WEEKLY_PROMOTION_WIDGET_CLASS);
        $lastIdx = $this->customHtmlEditor->getLastIdxToRemove(
            self::WEEKLY_PROMOTION_WIDGET_CLASS,
            self::WEEKLY_PROMOTION_WIDGET_END_SYMBOL,
            $content,
            self::INDEX_ZERO,
            self::NO_OF_STEP
        );

        $actLen = $lastIdx - $firstIdx;
        $updatedBlock = substr($content, $firstIdx, $actLen);
        if ($updatedBlock) {
            $dataArr = $this->customHtmlEditor->getCustomData(
                $updatedBlock,
                self::WEEKLY_PROMO_TITLE_START_WORD,
                self::DOUBLE_QUOTE,
                self::WEEKEY_PROMO_BTN_START_WORD,
                self::DOUBLE_QUOTE,
                $dataArr
            );

            $productCountArr =
                $this->customHtmlEditor->getCustomData(
                    $updatedBlock,
                    self::NUMBER_OF_PRODUCT_START_WORD,
                    self::DOUBLE_QUOTE,
                    '',
                    '',
                    []
                );
            if (count($productCountArr) > self::INDEX_ZERO) {
                $dataArr[] = $productCountArr[self::INDEX_ZERO];
            }
        }
        return $dataArr;
    }
}

