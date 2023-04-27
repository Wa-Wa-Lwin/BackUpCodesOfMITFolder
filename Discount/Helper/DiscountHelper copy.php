<?php

namespace MIT\Discount\Helper;

use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\SalesRule\Model\RuleRepository;
use \Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use MIT\Discount\Model\LabelFactory;
use MIT\Discount\Model\LabelImageRepository;

class DiscountHelper extends AbstractHelper
{

    /**
     * @var LabelFactory
     */
    private $labelFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var LabelImageRepository
     */
    private $labelImageRepository;

    /**
     * Timezone instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var RuleRepository
     */
    private $saleRuleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $saleRuleFactory;

    /**
     * @var SerializerInterface
     */
    private $serializerInterface;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    public function __construct(
        LabelFactory $labelFactory,
        Session $customerSession,
        StoreManagerInterface $storeManagerInterface,
        LabelImageRepository $labelImageRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        ProductRepository $productRepository,
        RuleRepository $saleRuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder,
        \Magento\SalesRule\Model\RuleFactory $saleRuleFactory,
        SerializerInterface $serializerInterface,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
    ) {
        $this->labelFactory = $labelFactory;
        $this->customerSession = $customerSession;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->labelImageRepository = $labelImageRepository;
        $this->_localeDate = $localeDate;
        $this->productRepository = $productRepository;
        $this->saleRuleRepository = $saleRuleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->saleRuleFactory = $saleRuleFactory;
        $this->serializerInterface = $serializerInterface;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * get discount label info by product id
     * @param int $productId
     * @return array
     */
    public function getLabelInfo($productId)
    {

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/product-discount.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($productId);

        $result = [];

        $catalogRuleResult = $this->getDiscountInfos($productId);

        if (count($catalogRuleResult) == 0) {
            $childIds = $this->getChildIds($productId);
            if ($childIds) {
                $catalogRuleResult = $this->getDiscountInfos($childIds);
            }
        }
        $logger->info(json_encode($catalogRuleResult));

        $salesRuleResult = $this->getDiscountInfosSalesRule($productId);

        $logger->info(json_encode($salesRuleResult));

        if (count($salesRuleResult) > 0 && count($catalogRuleResult) > 0) {
            $result = $salesRuleResult['sort_order'] > $catalogRuleResult['sort_order'] ? $salesRuleResult : $catalogRuleResult;
        } else if (count($salesRuleResult) > 0) {
            $result = $salesRuleResult;
        } else if (count($catalogRuleResult) > 0) {
            $result = $catalogRuleResult;
        }

        $logger->info(json_encode($result));

        return $catalogRuleResult;
    }

    /**
     * get discount infos
     * @param string $productIds
     * @return array
     */
    private function getDiscountInfos($productIds)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/catalogrule.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($productIds);

        $result = [];
        $date = $this->_localeDate->scopeTimeStamp($this->storeManagerInterface->getStore()->getId());
        //$collection = $this->labelCollection->
        $collection = $this->labelFactory->create()->getCollection();
        $collection = $collection->addFieldToSelect(['discount_label', 'width', 'height', 'sort_order']);
        $collection->getSelect()->joinInner('mit_discount_label_image', 'main_table.discount_img_id = mit_discount_label_image.label_image_id', ['img_path', 'main_css_class', 'sub_css_class']);
        // $collection->getSelect()->join('catalogrule as rule', 'main_table.rule_id = rule.rule_id', ['width', 'height']);
        $collection->getSelect()
            ->where('main_table.product_id  IN (?)', explode(',', $productIds))
            ->where('main_table.customer_group_id = ? ', $this->customerSession->getCustomer()->getGroupId())
            ->where('main_table.website_id = ? ', $this->storeManagerInterface->getStore()->getId())
            ->where('main_table.from_time = 0 or main_table.from_time < ?', $date)
            ->where('main_table.to_time = 0 or main_table.to_time > ?', $date);
        $collection->setOrder('main_table.sort_order', 'DESC');
        $collection->setPageSize(1);
        $collection->setCurPage(1);

        foreach ($collection as $discountLabel) {
            $logger->info('got it');
            $result['label'] = $discountLabel->getDiscountLabel();
            $result['imgPath'] = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $discountLabel->getImgPath();
            $result['main_class'] = $discountLabel->getMainCssClass();
            $result['sub_class'] = $discountLabel->getSubCssClass();
            $style = '';
            if ($discountLabel->getWidth() > 0 && $discountLabel->getHeight() > 0) {
                $style = 'width: ' . $discountLabel->getWidth() . 'px !important; height: ' . $discountLabel->getHeight() . 'px !important;';
            } else if ($discountLabel->getWidth() > 0) {
                $style = 'width: ' . $discountLabel->getWidth() . 'px !important;';
            } else if ($discountLabel->getHeight() > 0) {
                $style = 'height: ' . $discountLabel->getHeight() . 'px !important;';
            }
            $result['style'] = $style;
            $result['sort_order'] = $discountLabel->getSortOrder();
        }
        return $result;
    }

    /**
     * get discount infos salesrule
     * @param int $productId
     * @return array
     */
    private function getDiscountInfosSalesRule($productId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cartpricerule.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($productId);

        $result = [];

        $collection = $this->saleRuleFactory->create()->getCollection();
        $collection = $collection->addFieldToSelect('*');
        $collection->getSelect()->joinInner('mit_discount_label_image', 'main_table.discount_image_id = mit_discount_label_image.label_image_id', ['img_path', 'main_css_class', 'sub_css_class']);
        $collection->getSelect()
            ->where('main_table.is_active = ? ', 1)
            ->where('main_table.from_date is null or main_table.from_date <= ? ', $this->_localeDate->date()->format('Y-m-d'))
            ->where('main_table.to_date is null or main_table.to_date >= ? ', $this->_localeDate->date()->format('Y-m-d'));
        $collection->setOrder('main_table.sort_order', 'DESC');

        $result = $this->checkAndGetDiscountInfos($collection, $productId);
        $logger->info(json_encode($result));
        // if (count($result) == 0) {
        //     $childIds = $this->getChildIds($productId);
        //     if ($childIds) {
        //         foreach (explode(',', $childIds) as $productId) {
        //             $result = $this->checkAndGetDiscountInfos($collection, $productId);
        //             if (count($result) > 0) {
        //                 return $result;
        //             }
        //         }
        //     }
        // }
        return $result;
    }

    /**
     * check and get discount infos salesrule
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $collection
     * @pram int $productId
     * @return array
     */
    private function checkAndGetDiscountInfos($collection, $productId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cartpricerulecond.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        //$logger->info($productId);
        $result = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $rules = $objectManager->create('Magento\SalesRule\Model\Rule')->getCollection();
        $products_id = $productId;
        // $_rules = $this->saleRuleFactory->create()->getCollection();

        $_currentTime = $this->_localeDate->scopeTimeStamp($this->storeManagerInterface->getStore()->getId());
        // $objectManager = $this->_objectManager;

        $html = " ";
        foreach ($rules as $rule) {
            $fromDate = $rule->getFromDate();
            $toDate = $rule->getToDate();
            if ($rule->getIsActive()) {
                $logger->info($productId);
                //$product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);
                try {
                    $stock = $this->stockItemRepository->get($productId);
                    if ($stock->getIsInStock() && $stock->getQty() > 0) {
                        $product = $this->productRepository->getById($productId);
                        $item = $objectManager->create('Magento\Quote\Model\Quote');
                        $item->addProduct($product, 1);
                        $logger->info('qty ' . $stock->getQty());
                        //$logger->info('rule id ' . $rule->getId());
                        //Return True if Sales Rule validate product
                        $validate = $rule->getConditions()->validate($item);
                        if ($validate) {
                            $logger->info('valid');
                            $logger->info($rule->getId());
                            //if ($rule->getId() == '8') $html .= '<span class="coupon-label"><span>' . $rule->getName() . '</span></span>';
                        }
                    }
                } catch(NoSuchEntityException $e) {

                }
                
            }
            // if (isset($fromDate) && $_currentTime >= strtotime($fromDate)) {
            //     if (isset($toDate)) {
            //         if (strtotime($toDate) >= $_currentTime) {

            //         }
            //     }
            // }
        }
        // foreach ($rules as $rule) {
        //     $product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);
        //     $item = $objectManager->create('Magento\Catalog\Model\Product');
        //     $item->setProduct($product);
        //     $validate = $rule->getActions()->validate($item);
        //     $logger->info($validate);
        // }
        // foreach ($collection as $saleRule) {
        //     try {
        //         // $product = $this->productRepository->getById($productId);
        //         $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        //         $item = $objectManager->create('Magento\Catalog\Model\Product');
        //         $item->setProduct($product);
        //         $logger->info($saleRule->getName());
        //         $logger->info($saleRule->getCondition());
        //         $conditions = $this->getConditions($saleRule);
        //         if (null !== $conditions && count($conditions) > 0) {
        //             $logger->info('conditon not null');
        //             foreach ($conditions as $condition) {
        //                 $validated = $condition->validate([$item]);
        //                 if ($validated) {
        //                     $logger->info('conditon valid');
        //                     $result['label'] = $saleRule->getDiscountLabel();
        //                     $result['imgPath'] = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $saleRule->getImgPath();
        //                     $result['main_class'] = $saleRule->getMainCssClass();
        //                     $result['sub_class'] = $saleRule->getSubCssClass();
        //                     $style = '';
        //                     if ($saleRule->getWidth() > 0 && $saleRule->getHeight() > 0) {
        //                         $style = 'width: ' . $saleRule->getWidth() . 'px !important; height: ' . $saleRule->getHeight() . 'px !important;';
        //                     } else if ($saleRule->getWidth() > 0) {
        //                         $style = 'width: ' . $saleRule->getWidth() . 'px !important;';
        //                     } else if ($saleRule->getHeight() > 0) {
        //                         $style = 'height: ' . $saleRule->getHeight() . 'px !important;';
        //                     }
        //                     $result['style'] = $style;
        //                     $result['sort_order'] = $saleRule->getSortOrder();
        //                     return $result;
        //                 }
        //             }
        //         }
        //         // if ($saleRule->getCondition()->validate($item)) {

        //         // }
        //     } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        //         continue;
        //     }
        // }
        return $result;
    }


    /**
     * get child product by parent id
     * @param int $parentId
     * @return string|null
     */
    private function getChildIds($parentId)
    {
        try {
            $product = $this->productRepository->getById($parentId);
            if ($product->getExtensionAttributes()->getConfigurableProductLinks()) {
                return implode(',', $product->getExtensionAttributes()->getConfigurableProductLinks());
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
        return null;
    }

    /**
     * get discount label info by label image id
     * @param int $id
     * @return \MIT\Discount\Api\Data\LabelImageInterface
     */
    public function getLabelImageById($id)
    {
        //return $this->labelImageRepository->get($id);
        $result = [];
        $data =  $this->labelImageRepository->get($id);
        if ($data) {
            $result['imgPath'] = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $data->getImgPath();
            $result['main_class'] = $data->getMainCssClass();
            $result['sub_class'] = $data->getSubCssClass();
            $result['width'] = $data->getWidth();
            $result['height'] = $data->getHeight();
        }
        return $result;
    }

    /**
     * unserialize sales rule
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return array
     */
    protected function getConditions($rule)
    {
        if ($rule['rule_id']) {
            return $this->getRuleConditionUnserialized($rule['conditions_serialized']);
        }
        return [];
    }

    /**
     * unserialized conditons
     * @param string $serializedData
     * @return array
     */
    private function getRuleConditionUnserialized($serializedData)
    {
        $data = $this->serializerInterface->unserialize($serializedData);
        if (isset($data['conditions'])) {
            return $data['conditions'];
        }
        return [];
    }
}
