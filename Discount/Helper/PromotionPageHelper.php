<?php

namespace MIT\Discount\Helper;

use Magento\Catalog\Model\ProductRepository;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Store\Model\StoreManagerInterface;
use MIT\Discount\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use MIT\SalesRuleLabel\Model\ResourceModel\LabelProduct\CollectionFactory as LabelProductCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Bundle\Model\ResourceModel\Selection as Bundle;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use MIT\CatalogRule\Helper\CustomDataUpdater;
use MIT\CatalogRule\Helper\HomeSliderGenerator;

class PromotionPageHelper extends AbstractHelper
{

    const PAGE_DEFAULT_STYLE = '
    <style>
    [[REPLACE_STYLE_CODE]] {
        justify-content: flex-start;
        display: flex;
        flex-direction: column;
        background-position: left top;
        background-size: cover;
        background-repeat: no-repeat;
        background-attachment: scroll
    }
    </style>
    ';

    const PAGE_STYLE_CODE_PREFIX = '#html-body [data-pb-style=';

    const TEMPLATE_PATHS = ['Magento_CatalogWidget::product/widget/content/grid.phtml', 'Magento_CatalogWidget::product/widget/content/grid-six.phtml'];

    const PAGE_PRODUCT_BLOCK = '
    <div data-content-type="row" data-appearance="contained" data-element="main">
        <div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="[[REPLACE_STYLE_CODE]]">
            <h2 data-content-type="heading" data-appearance="default" style="font-size: 20px;font-weight: 600;margin-bottom: 1rem; color: #cc6633;" data-element="main">[[REPLACE_TITLE]]</h2>
            <div data-content-type="products" data-appearance="grid" data-element="main">{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" template="[[TEMPLATE_PATH]]" anchor_text="" id_path="" show_pager="0" products_count="[[SHOW_COUNT]]" condition_option="sku" condition_option_value="[[REPLACE_SKUS]]" type_name="Catalog Products List" conditions_encoded="^[`1`:^[`aggregator`:`all`,`new_child`:``,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`value`:`1`^],`1--1`:^[`operator`:`()`,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`value`:`[[REPLACE_SKUS]]`^]^]" sort_order="position_by_sku"}}
            </div>
        </div>
    </div>
    ';

    const PAGE_IMAGE_BLOCK = '
    <div data-content-type="row" data-appearance="contained" data-element="main">
        <div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image"
            data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true"
            data-video-fallback-src="" data-element="inner"  data-pb-style="[[REPLACE_STYLE_CODE]]">
            <div data-content-type="html" data-appearance="default" data-element="main">
                <div class="category-view"><div class="category-image"><img src="[[REPLACE_IMAGE_PATH]]" alt="[[REPLACE_IMAGE_TITLE]]" title="[[REPLACE_IMAGE_TITLE]]" class="image"></div></div>
            </div>
        </div>
    </div>
    ';

    //http://ab.magento.com/media/catalog/category/sample_image.jpeg

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var LabelCollectionFactory
     */
    private $labelCollectionFactory;

    /**
     * @var LabelProductCollectionFactory
     */
    private $labelProductCollectionFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var Grouped
     */
    private $groupType;

    /**
     * @var Bundle
     */
    private $bundleType;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var HomeSliderGenerator
     */
    private $homeSliderGenerator;

    /**
     * @var ResourceProduct
     */
    private $resourceProduct;

    public function __construct(
        PageRepository $pageRepository,
        PageFactory $pageFactory,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManagerInterface,
        LabelCollectionFactory $labelCollectionFactory,
        LabelProductCollectionFactory $labelProductCollectionFactory,
        CustomerSession $customerSession,
        TimezoneInterface $timezoneInterface,
        Configurable $configurableType,
        Grouped $groupType,
        Bundle $bundleType,
        ProductRepository $productRepository,
        HomeSliderGenerator $homeSliderGenerator,
        ResourceProduct $resourceProduct
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageFactory = $pageFactory;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->labelProductCollectionFactory = $labelProductCollectionFactory;
        $this->customerSession = $customerSession;
        $this->timezoneInterface = $timezoneInterface;
        $this->configurableType = $configurableType;
        $this->groupType = $groupType;
        $this->bundleType = $bundleType;
        $this->productRepository = $productRepository;
        $this->homeSliderGenerator = $homeSliderGenerator;
        $this->resourceProduct = $resourceProduct;
    }

    /**
     * Delete Page by id
     * @param array $ids
     * @return bool
     */
    public function deletePage($ids)
    {
        $identifierList = $this->getIdentifierList($ids);
        if (count($ids) > 0) {
            foreach ($ids as $id) {
                $this->homeSliderGenerator->deleteSliderBlock(CustomDataUpdater::BLOCK_IDENTIFIER, CustomDataUpdater::PROMOTION_SLIDER, $id, 0, CustomDataUpdater::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
                $this->homeSliderGenerator->deleteSliderBlock(CustomDataUpdater::BLOCK_IDENTIFIER, CustomDataUpdater::PROMOTION_SLIDER_MM, $id, 0, CustomDataUpdater::PROMO_TOTAL_DIV_COUNT_TO_REMOVE);
            }
        }

        if (count($identifierList) > 0) {
            $filteredId = $this->_filterBuilder
                ->setConditionType('in')
                ->setField('identifier')
                ->setValue(($identifierList))
                ->create();
            $filterGroupList = [];
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
            $this->searchCriteriaBuilder->setFilterGroups($filterGroupList);
            $result = $this->pageRepository->getList($this->searchCriteriaBuilder->create());
            if ($result->getTotalCount() > 0) {
                foreach ($result->getItems() as $item) {
                    try {
                        $this->pageRepository->deleteById($item->getId());
                    } catch (CouldNotDeleteException $e) {
                    } catch (NoSuchEntityException $e) {
                    }
                }
            }
        }
        return true;
    }

    /**
     * get identifier list for page
     * @param array $ids
     * @return array
     */
    private function getIdentifierList($ids)
    {
        $identifierList = [];
        $storeList =  $this->storeManagerInterface->getStores();
        foreach ($storeList as $store) {
            foreach ($ids as $id) {
                $identifierList[] = 'promotion-page-' . $store->getCode() . '-' . $id;
            }
        }
        return $identifierList;
    }

    /**
     * generate promotion page
     * @param int $ruleId
     * @param string $imagePathEng
     * @param string $imagePathMm
     * @param string $ruleName
     * @param array $catalogRuleIds
     * @param array $cartPriceRuleIds
     * @param int $type
     */
    public function generatePromotionPage($ruleId, $imagePathEng, $imagePathMm, $promoImgEng, $promoImgMm, $ruleName, $catalogRuleIds, $cartPriceRuleIds, $type)
    {
        $storeList =  $this->storeManagerInterface->getStores();
        $mediaUrl = $this->storeManagerInterface->getStore()->getBaseUrl() . 'media/other/images/image';

        $promoSlideIdentifier = CustomDataUpdater::PROMOTION_SLIDER;
        foreach ($storeList as $store) {
            if ($store->getCode() == 'mm') {
                $imagePathEng = $imagePathMm ? $imagePathMm : $imagePathEng;
                $promoImgEng = $promoImgMm ? $promoImgMm : $promoImgEng;
                $promoSlideIdentifier = CustomDataUpdater::PROMOTION_SLIDER_MM;
            }
            if ($imagePathEng) {
                $imagePathEng = $mediaUrl . $imagePathEng;
            }

            $this->generatePage($ruleId, $store->getCode(), $store->getId(), $imagePathEng, $ruleName, $catalogRuleIds, $cartPriceRuleIds, $type);
            $this->homeSliderGenerator->generateAndUpdatePromoSlider(CustomDataUpdater::BLOCK_IDENTIFIER, $promoSlideIdentifier, $ruleId, $this->storeManagerInterface->getStore()->getBaseUrl() . 'promotion-page-' . $store->getCode() . '-' . $ruleId, 'other/images/image' . $promoImgEng);
        }
    }

    /**
     * generate promotion page
     * @param int $ruleId
     * @param int $storeCode
     * @param int $storeId
     * @param string $imagePathEng
     * @param string $ruleName
     * @param array $catalogRuleIds
     * @param array $cartPriceRuleIds
     * @param int $type
     */
    private function generatePage($ruleId, $storeCode, $storeId, $imagePathEng, $ruleName, $catalogRuleIds, $cartPriceRuleIds, $type)
    {
        $identifier = 'promotion-page-' . $storeCode . '-' . $ruleId;

       // $promotionProductList = $this->getCartPriceRuleProductIdList($cartPriceRuleIds);
        $promotionProductList = array_merge(
            $this->getCatalogPriceRuleProductIdList($catalogRuleIds, $storeCode),
            $this->getCartPriceRuleProductIdList($cartPriceRuleIds, $storeCode)
        );

        if (count($promotionProductList) > 0) {
            usort($promotionProductList, fn ($a, $b) => strcmp($b['sort_order'], $a['sort_order']));
            $promotionProductList = $this->generateParentSkus($promotionProductList);

            $filteredId = $this->_filterBuilder
                ->setConditionType('eq')
                ->setField('identifier')
                ->setValue(($identifier))
                ->create();
            $filterGroupList = [];
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
            $this->searchCriteriaBuilder->setFilterGroups($filterGroupList);
            $result = $this->pageRepository->getList($this->searchCriteriaBuilder->create());
            if ($result->getTotalCount() > 0) {
                foreach ($result->getItems() as $item) {
                    $item->setContent($this->generatePageContent($promotionProductList, $imagePathEng, $type))
                        ->setTitle($ruleName)
                        ->setIdentifier($identifier)
                        ->setIsActive(true)
                        ->setStores(array($storeId))
                        ->setPageLayout('cms-full-width');
                    $this->pageRepository->save($item);
                    return true;
                }
            } else {
                $page = $this->pageFactory->create();
                $page->setTitle($ruleName)
                    ->setIdentifier($identifier)
                    ->setIsActive(true)
                    ->setPageLayout('cms-full-width')
                    ->setStores(array($storeId))
                    ->setContent($this->generatePageContent($promotionProductList, $imagePathEng, $type));
                $this->pageRepository->save($page);
                return true;
            }
        } else {
            $filteredId = $this->_filterBuilder
                ->setConditionType('eq')
                ->setField('identifier')
                ->setValue(($identifier))
                ->create();
            $filterGroupList = [];
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
            $this->searchCriteriaBuilder->setFilterGroups($filterGroupList);
            $result = $this->pageRepository->getList($this->searchCriteriaBuilder->create());
            foreach($result->getItems() as $item) {
                $this->pageRepository->deleteById($item->getId());
            }
        }
    }

    /**
     * Get catalog rule List
     * @param array $ruleIdArr
     * @param string $storeCode
     * @return array
     */
    public function getCatalogPriceRuleProductIdList(array $ruleIdArr, $storeCode)
    {
        $result = [];
        $titleList = [];
        $sortOrderList = [];
        $catalogProductIdList = [];
        if (count($ruleIdArr) > 0) {
            $scopeTz = new \DateTimeZone(
                $this->timezoneInterface->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $this->storeManagerInterface->getStore()->getWebsiteId())
            );
            $date = (new \DateTime('now', $scopeTz))->getTimestamp();
            // $date = $this->timezoneInterface->scopeTimeStamp($this->storeManagerInterface->getStore()->getId());
            $collection = $this->labelCollectionFactory->create();
            $collection = $collection->addFieldToSelect(['sort_order', 'rule_id']);
            $collection->getSelect()->joinInner('catalog_product_entity', 'main_table.product_id = catalog_product_entity.entity_id', 'sku');
            $collection->getSelect()->joinInner('catalogrule', 'main_table.rule_id = catalogrule.rule_id', ['name', 'rule_name_mm']);
            $collection->getSelect()
                ->where('main_table.rule_id  IN (?)', $ruleIdArr)
                ->where('main_table.customer_group_id = ? ', $this->customerSession->getCustomer()->getGroupId())
                ->where('main_table.from_time = 0 or main_table.from_time < ?', $date)
                ->where('main_table.to_time = 0 or main_table.to_time > ?', $date);
            $collection->setOrder('main_table.sort_order', 'DESC');

            foreach ($collection as $item) {
                $result[$item['rule_id']][] = $item['sku'];
                if ($storeCode == 'mm') {
                    $titleList[$item['rule_id']] = $item['rule_name_mm'] ? $item['rule_name_mm'] : $item['name'];
                } else {
                    $titleList[$item['rule_id']] = $item['name'];
                }
                $sortOrderList[$item['rule_id']] = $item['sort_order'];
            }

            foreach (array_keys($result) as $key) {
                $catalogProductIdList[] = array('product_skus' => implode(',', $result[$key]), 'sort_order' => $sortOrderList[$key], 'title' => $titleList[$key]);
            }
        }
        return $catalogProductIdList;
    }

    /**
     * Get cart rule List
     * @param array $ruleIdArr
     * @param string $storeCode
     * @return array
     */
    public function getCartPriceRuleProductIdList(array $ruleIdArr, $storeCode)
    {
        $result = [];
        $titleList = [];
        $sortOrderList = [];
        $salesRuleProductList = [];
        if (count($ruleIdArr) > 0) {
            $scopeTz = new \DateTimeZone(
                $this->timezoneInterface->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $this->storeManagerInterface->getStore()->getWebsiteId())
            );
            $date = (new \DateTime('now', $scopeTz))->getTimestamp();
            // $date = $this->timezoneInterface->scopeTimeStamp($this->storeManagerInterface->getStore()->getId());
            $collection = $this->labelProductCollectionFactory->create();
            $collection = $collection->addFieldToSelect(['sort_order']);
            $collection->getSelect()->joinInner('catalog_product_entity', 'main_table.product_id = catalog_product_entity.entity_id', 'sku');
            $collection->getSelect()->joinInner('mit_salesrulelabel', 'main_table.rule_id = mit_salesrulelabel.rule_id', 'rule_status');
            $collection->getSelect()->joinInner('salesrule', 'mit_salesrulelabel.sale_rule_id = salesrule.rule_id', ['name', 'rule_id', 'rule_name_mm']);

            $collection->getSelect()
                ->where('mit_salesrulelabel.sale_rule_id IN (?)', $ruleIdArr)
                ->where('main_table.customer_group_id = ? ', $this->customerSession->getCustomer()->getGroupId())
                ->where('main_table.from_time = 0 or main_table.from_time < ?', $date)
                ->where('main_table.to_time = 0 or main_table.to_time > ?', $date);
            $collection->setOrder('main_table.sort_order', 'DESC');

            foreach ($collection as $item) {
                $result[$item['rule_id']][] = $item['sku'];
                if ($storeCode == 'mm') {
                    $titleList[$item['rule_id']] = $item['rule_name_mm'] ? $item['rule_name_mm'] : $item['name'];
                } else {
                    $titleList[$item['rule_id']] = $item['name'];
                }
                $sortOrderList[$item['rule_id']] = $item['sort_order'];
            }

            foreach (array_keys($result) as $key) {
                $salesRuleProductList[] = array('product_skus' => implode(',', $result[$key]), 'sort_order' => $sortOrderList[$key], 'title' => $titleList[$key]);
            }
        }
        return $salesRuleProductList;
    }

    /**
     * generate page content
     * @param array $dataArr
     * @param string $imgPath
     * @param int $type
     * @return string
     */
    private function generatePageContent($dataArr, $imgPath, $type)
    {
        $content = '';
        $content_data = '';
        $imageData = '';
        $style_Arr = [];

        if ($imgPath) {
            $imageId = uniqid();
            $style_Arr[] =  self::PAGE_STYLE_CODE_PREFIX . $imageId . ']';;
            $imageData = str_replace('[[REPLACE_STYLE_CODE]]', $imageId, self::PAGE_IMAGE_BLOCK);
            $imageData = str_replace('[[REPLACE_IMAGE_TITLE]]', 'Promotion Page', $imageData);
            $imageData = str_replace('[[REPLACE_IMAGE_PATH]]', $imgPath, $imageData);
        }

        if (count($dataArr) > 0) {
            foreach ($dataArr as $data) {
                $unique_id = uniqid();
                $style_Arr[] = self::PAGE_STYLE_CODE_PREFIX . $unique_id . ']';
                $blockContent = str_replace('[[REPLACE_STYLE_CODE]]', $unique_id, self::PAGE_PRODUCT_BLOCK);
                $blockContent = str_replace('[[REPLACE_TITLE]]', $data['title'], $blockContent);
                $blockContent = str_replace('[[REPLACE_SKUS]]', $data['product_skus'], $blockContent);
                $blockContent = str_replace('[[TEMPLATE_PATH]]', self::TEMPLATE_PATHS[$type - 1], $blockContent);
                $blockContent = str_replace('[[SHOW_COUNT]]', count(explode(',', $data['product_skus'])), $blockContent);
                $content .= $blockContent;
            }

            $content_data = str_replace('[[REPLACE_STYLE_CODE]]', implode(',', $style_Arr), self::PAGE_DEFAULT_STYLE);
            $content_data .= $imageData;
            $content_data .= $content;
        }
        return $content_data;
    }

    /**
     * generate parent sku if sku is child
     * @param array $promotionProductList
     * @return array
     */
    public function generateParentSkus($promotionProductList) {
        $promoProductList = [];
        foreach($promotionProductList as $promotion) {
            $skuList = explode(',', $promotion['product_skus']);
            $parentSkus = [];
            foreach($skuList as $key=>$sku) {
                try {
                    $product = $this->productRepository->get($sku);
                    if ($product->getVisibility() != 4) {
                        unset($skuList[$key]);
                    }
                    $parentIds = $this->configurableType->getParentIdsByChild($product->getId());
                    if (!empty($parentIds)) {
                        $productArr = $this->resourceProduct->getProductsSku($parentIds);
                        $parentSkus[] = $productArr[0]['sku'];
                    }
                } catch(NoSuchEntityException $e) {

                }
               
            }
            $promoProductList[] = array('product_skus' => implode(',',array_merge($skuList, array_unique($parentSkus))), 
            'sort_order' => $promotion['sort_order'], 'title' => $promotion['title']);
        }
        return $promoProductList;
    }
}
