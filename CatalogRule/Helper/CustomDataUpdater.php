<?php

namespace MIT\CatalogRule\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogRule\Api\Data\ConditionInterface;
use Magento\CatalogRule\Model\CatalogRuleRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use MIT\CatalogRule\Api\Data\RuleInterface;
use MIT\HomeSlider\Api\Data\HomeSliderInterface;
use MIT\CatalogRule\Helper\HomeSliderGenerator;
use MIT\HomeSlider\Model\HomeSliderRepository;

class CustomDataUpdater extends AbstractHelper
{
    const BLOCK_IDENTIFIER = 'identifier';
    const HOME_SLIDER = 'et_home_slider';
    const HOME_SLIDER_MM = 'et_home_slider_mm';
    const HOME_SLIDER_ONE = 'home_slider_one';
    const HOME_SLIDER_ONE_MM = 'home_slider_one_mm';
    const PROMOTION_SLIDER = 'promotion_slider';
    const PROMOTION_SLIDER_MM = 'promotion_slider_mm';
    const INACTIVE_STATUS_CODE = 0;
    const ACTIVE_STATUS_CODE = 1;
    const PROMO_TOTAL_DIV_COUNT_TO_REMOVE = 2;

    /**
     * @var CatalogRuleRepository
     */
    private $catalogRuleRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var HomeSliderGenerator
     */
    private $homeSliderGenerator;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $helper;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var HomeSliderInterface
     */
    protected $homeSlider;

    /**
     * @var HomeSliderRepository
    */
    protected $homeSliderRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    public function __construct(
        CatalogRuleRepository $catalogRuleRepository,
        CategoryRepository $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        HomeSliderGenerator $homeSliderGenerator,
        \Magento\Framework\Pricing\Helper\Data $helper,
        ProductRepository $productRepository,
        HomeSliderInterface $homeSlider,
        HomeSliderRepository $homeSliderRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->homeSliderGenerator = $homeSliderGenerator;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->homeSlider = $homeSlider;
        $this->homeSliderRepository = $homeSliderRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * updated customize data like home slider, home slider one and promotion slider
     * @param int $id
     * @param string $type
     * @param int $categoryId
     */
    public function updateData($id, $type , $categoryId)
    {
      
        if ($type === 'delete') {
            $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
            $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_ONE, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
            $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_MM, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
            $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_ONE_MM, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
            // $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::PROMOTION_SLIDER, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
            // $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::PROMOTION_SLIDER_MM, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
        } else {
            /** @var HomeSliderInterface */
            $homeSliderRule = $this->homeSliderRepository->get($id);
        

            if ($homeSliderRule->getIsActive() == self::INACTIVE_STATUS_CODE) {
                $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
                $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_ONE, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
                $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_MM, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
                $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_ONE_MM, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
                // $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::PROMOTION_SLIDER, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
                // $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::PROMOTION_SLIDER_MM, $id, 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
            } else {

                $this->updateRuleData($homeSliderRule, $categoryId);
            }
        }
    }

    /**
     * update rule data and generate slider
     * @param \MIT\HomeSlider\Api\Data\HomeSliderInterface $catalogRule
     * @param int $categoryId
     */ 
    private function updateRuleData($homeSliderRule, $categoryId) {
        $currentStore = $this->storeManager->getStore();
        $baseUrl = $currentStore->getBaseUrl();
        $url = '';
        $img = '';
        $mobileImgUrl = '';
        $discount = '';
       if($categoryId && $categoryId > 0){
        $category = $this->categoryRepository->get($categoryId);
        $categoryName = $category->getName(); 
        if ($category->getCustomAttributes()) {
            foreach ($category->getCustomAttributes() as $customAttribute) {
                if ($customAttribute->getAttributeCode() == 'url_path') {
                    $url = $baseUrl . $customAttribute->getValue() . '.html';
                }
            }
        }
       }
        $homeSliderRule->getHomeSliderImage();
      
        
        if ($homeSliderRule->getHomeSliderImage()) {
            $img = 'homeslider/images/image/' . $homeSliderRule->getHomeSliderImage();
            $mobileImgUrl = $img;
        } else {
            $img = 'bannerslider/image/slide3.jpg';
        }

        if ($homeSliderRule->getHomeSliderImageMobile()) {
            $mobileImgUrl = 'homeslider/images/image/' . $homeSliderRule->getHomeSliderImageMobile();
        }

        if ($homeSliderRule->getIsHomeSlider() == self::ACTIVE_STATUS_CODE) {
            $this->homeSliderGenerator->generateAndUpdateHomeSlider(self::BLOCK_IDENTIFIER, self::HOME_SLIDER, $homeSliderRule->getHomesliderId(), $img, $mobileImgUrl, $url,$categoryId);
            $this->homeSliderGenerator->generateAndUpdateHomeSlider(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_MM, $homeSliderRule->getHomesliderId(), $img, $mobileImgUrl, $url,$categoryId);
        } else if ($homeSliderRule->getIsHomeSlider() == self::INACTIVE_STATUS_CODE || !$this->checkValidRuleDateRange($homeSliderRule->getFromDate(), $homeSliderRule->getToDate())) {
            $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER, $homeSliderRule->getHomesliderId(), 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
            $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_MM, $homeSliderRule->getHomesliderId(), 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
        }

        if ($homeSliderRule->getIsHomeSliderOne() == self::ACTIVE_STATUS_CODE) {
            $this->homeSliderGenerator->generateAndUpdateHomeSlider(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_ONE, $homeSliderRule->getHomesliderId(), $img, $mobileImgUrl, $url, $categoryId);
            $this->homeSliderGenerator->generateAndUpdateHomeSlider(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_ONE_MM, $homeSliderRule->getHomesliderId(), $img, $mobileImgUrl, $url, $categoryId);
        } else if ($homeSliderRule->getIsHomeSliderOne() == self::INACTIVE_STATUS_CODE || !$this->checkValidRuleDateRange($homeSliderRule->getFromDate(), $homeSliderRule->getToDate())) {
            $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_ONE, $homeSliderRule->getHomesliderId(), 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
            $this->homeSliderGenerator->deleteSliderBlock(self::BLOCK_IDENTIFIER, self::HOME_SLIDER_ONE_MM, $homeSliderRule->getHomesliderId(), 0, self::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
        }

    }


    /**
     * check rule is valid date range or not
     * @param string $fromDate
     * @param string $toDate
     * @return bool
     */
    private function checkValidRuleDateRange($fromDate, $toDate)
    {
        $isValid = false;
        $currentDate = date("Y-m-d");
        if ($fromDate && $toDate) {
            if ($currentDate >= $fromDate && $currentDate <= $toDate) {
                   $isValid = true;
            }
        } else if ($fromDate) {
            if ($currentDate >= $fromDate) {
                $isValid = true;
            }
        } else if ($toDate) {
            if ($currentDate >= $toDate) {
                $isValid = true;
            }
        }
        return $isValid;
    }

    /**
     * format currency
     * @param float $amt
     * @return string
     */
    private function formatCurrency($amt)
    {
        if ($amt > 0) {
            $formattedPrice = $this->helper->currency($amt, true, false);
            $pos = strpos($formattedPrice, '.');
            if ($pos === false) {
                return $formattedPrice;
            } else {
                return rtrim(rtrim($formattedPrice, '0'), '.');
            }
        }
        return '0';
    }

    /**
     * check date range and get skus
     * @param mixed $catalogRule
     * @return int
     */
    private function getCategoryIdFromSkusRule($catalogRule) {
        $skus = $this->getCatalogRuleSkus($catalogRule);

        if ($this->checkValidRuleDateRange($catalogRule['from_date'], $catalogRule['to_date']) && $skus) {
            foreach(explode(',', $skus) as $sku) {
                try {
                    $product = $this->productRepository->get($sku);
                    $categoryIds = $product->getCategoryIds();
                    $collection = $this->categoryCollectionFactory->create()
                        ->addFieldToSelect('entity_id')
                        ->addIsActiveFilter()
                        ->addFieldToFilter('entity_id', ['in' => $categoryIds])
                        ->addFieldToFilter('include_in_menu', 1)
                        ->setPageSize(1)
                        ->setCurPage(1)
                        ->setOrder('entity_id', 'DESC');
                    if ($collection->getSize() > 0) {                        
                        return $collection->getFirstItem()->getId();
                    }
                } catch(NoSuchEntityException $e) {
                    continue;
                }
            }
        }
        return 0;
    }

    /**
     * get sku from catalog rule
     * @param mixed $catalogRule
     * @return string
     */
    private function getCatalogRuleSkus($catalogRule)
    {
        /** @var ConditionInterface[] */
        $conditions = $catalogRule->getRuleCondition()->getConditions();
        if (null !== $conditions && count($conditions) > 0) {
            if (
                $conditions[0]->getType() == 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product' &&
                $conditions[0]->getAttribute() == 'sku' && $conditions[0]->getOperator() == '()'
            ) {
                return $conditions[0]->getValue();
            }
        }
        return '';
    }
}
