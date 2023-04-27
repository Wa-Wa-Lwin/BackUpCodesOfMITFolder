<?php

namespace MIT\Discount\Helper;

use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\ScopeInterface;
use MIT\Discount\Model\LabelFactory;
use MIT\Discount\Model\LabelImageRepository;
use MIT\SalesRuleLabel\Model\LabelProductFactory;

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

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var LabelImageRepository
     */
    private $labelImageRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistryRepository;

    /**
     * @var LabelProductFactory
     */
    private $labelProductFactory;

    public function __construct(
        LabelFactory $labelFactory,
        Session $customerSession,
        StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        ProductRepository $productRepository,
        \Magento\SalesRule\Model\RuleFactory $saleRuleFactory,
        SerializerInterface $serializerInterface,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        QuoteFactory $quoteFactory,
        LabelImageRepository $labelImageRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistryRepository,
        LabelProductFactory $labelProductFactory
    ) {
        $this->labelFactory = $labelFactory;
        $this->customerSession = $customerSession;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->_localeDate = $localeDate;
        $this->productRepository = $productRepository;
        $this->saleRuleFactory = $saleRuleFactory;
        $this->serializerInterface = $serializerInterface;
        $this->stockItemRepository = $stockItemRepository;
        $this->quoteFactory = $quoteFactory;
        $this->labelImageRepository = $labelImageRepository;
        $this->stockRegistryRepository = $stockRegistryRepository;
        $this->labelProductFactory = $labelProductFactory;
    }

    /**
     * get discount label info by product id
     * @param int $productId
     * @return array
     */
    public function getLabelInfo($productId)
    {

        $result = [];

        $catalogRuleResult = $this->getDiscountInfosCatalogRule($productId);

        if (count($catalogRuleResult) == 0) {
            $childIds = $this->getChildIds($productId);
            if ($childIds) {
                $catalogRuleResult = $this->getDiscountInfosCatalogRule($childIds);
            }
        }

        //$salesRuleResult = $this->getDiscountInfosSalesRule($productId);
        $salesRuleResult = $this->getDiscountInfosSalesRuleNew($productId);
        if (count($salesRuleResult) == 0) {
            $childIds = $this->getChildIds($productId);
            if ($childIds) {
                $salesRuleResult = $this->getDiscountInfosSalesRuleNew($childIds);
            }
        }

        if (count($salesRuleResult) > 0 && count($catalogRuleResult) > 0) {
            $result = $salesRuleResult['sort_order'] > $catalogRuleResult['sort_order'] ? $salesRuleResult : $catalogRuleResult;
        } else if (count($salesRuleResult) > 0) {
            $result = $salesRuleResult;
        } else if (count($catalogRuleResult) > 0) {
            $result = $catalogRuleResult;
        }

        return $result;
    }

    /**
     * get discount label info by label image id
     * @param int $id
     * @return \MIT\Discount\Api\Data\LabelImageInterface
     */
    public function getLabelImageById($id)
    {
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
     * get discount infos
     * @param string $productIds
     * @return array
     */
    private function getDiscountInfosCatalogRule($productIds)
    {

        $result = [];
        $scopeTz = new \DateTimeZone(
            $this->_localeDate->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $this->storeManagerInterface->getStore()->getWebsiteId())
        );
        $date = (new \DateTime('now', $scopeTz))->getTimestamp();
        // $date = $this->_localeDate->scopeTimeStamp($this->storeManagerInterface->getStore()->getId());
        $collection = $this->labelFactory->create()->getCollection();
        $collection = $collection->addFieldToSelect(['discount_label', 'width', 'height', 'sort_order', 'discount_label_color', 'discount_label_style']);
        // $collection->getSelect()->joinInner('catalogrule_product', 'main_table.rule_id = catalogrule_product.rule_id', ['action_stop']);
        $collection->getSelect()->joinInner('mit_discount_label_image', 'main_table.discount_img_id = mit_discount_label_image.label_image_id', ['img_path', 'main_css_class', 'sub_css_class']);

        $collection->getSelect()
            ->where('main_table.product_id  IN (?)', explode(',', $productIds))
            ->where('main_table.customer_group_id = ? ', $this->customerSession->getCustomer()->getGroupId())
           // ->where('catalogrule_product.website_id = ? ', $this->storeManagerInterface->getStore()->getId())
            ->where('main_table.from_time = 0 or main_table.from_time < ?', $date)
            ->where('main_table.to_time = 0 or main_table.to_time > ?', $date);
        $collection->setOrder('main_table.sort_order', 'DESC');
        $collection->setPageSize(1);
        $collection->setCurPage(1);

        foreach ($collection as $discountLabel) {
            $result['label'] = $discountLabel->getDiscountLabel();
            $result['imgPath'] = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $discountLabel->getImgPath();
            $result['main_class'] = $discountLabel->getMainCssClass();
            $result['sub_class'] = $discountLabel->getSubCssClass();
            $labelStyles = '';
            if ($discountLabel->getDiscountLabelColor()) {
                $labelStyles .= 'color:' . $discountLabel->getDiscountLabelColor() . ';';
            }
            if ($discountLabel->getDiscountLabelStyle()) {
                $labelStyles .= $discountLabel->getDiscountLabelStyle();
            }
            $result['label_styles'] = $labelStyles;

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

    private function getDiscountInfosSalesRuleNew($productIds) {
        $result = [];
        $scopeTz = new \DateTimeZone(
            $this->_localeDate->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $this->storeManagerInterface->getStore()->getWebsiteId())
        );
        $date = (new \DateTime('now', $scopeTz))->getTimestamp();
        // $date = $this->_localeDate->scopeTimeStamp($this->storeManagerInterface->getStore()->getId());
        $collection = $this->labelProductFactory->create()->getCollection();
        $collection = $collection->addFieldToSelect(['discount_label', 'width', 'height', 'sort_order', 'discount_label_color', 'discount_label_style']);
        $collection->getSelect()->joinInner('mit_discount_label_image', 'main_table.discount_image_id = mit_discount_label_image.label_image_id', ['img_path', 'main_css_class', 'sub_css_class']);

        $collection->getSelect()
            ->where('main_table.product_id IN (?)', explode(',', $productIds))
            ->where('main_table.customer_group_id = ? ', $this->customerSession->getCustomer()->getGroupId())
            ->where('main_table.from_time = 0 or main_table.from_time < ?', $date)
            ->where('main_table.to_time = 0 or main_table.to_time > ?', $date);
        $collection->setOrder('main_table.sort_order', 'DESC');
        $collection->setPageSize(1);
        $collection->setCurPage(1);

        foreach ($collection as $discountLabel) {
            $result['label'] = $discountLabel->getDiscountLabel();
            $result['imgPath'] = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $discountLabel->getImgPath();
            $result['main_class'] = $discountLabel->getMainCssClass();
            $result['sub_class'] = $discountLabel->getSubCssClass();
            $labelStyles = '';
            if ($discountLabel->getDiscountLabelColor()) {
                $labelStyles .= 'color:' . $discountLabel->getDiscountLabelColor() . ';';
            }
            if ($discountLabel->getDiscountLabelStyle()) {
                $labelStyles .= $discountLabel->getDiscountLabelStyle();
            }
            $result['label_styles'] = $labelStyles;

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
        $result = [];

        $collection = $this->saleRuleFactory->create()->getCollection();
        $collection = $collection->addFieldToSelect('*');
        $collection->getSelect()->joinInner('mit_discount_label_image', 'main_table.discount_image_id = mit_discount_label_image.label_image_id', ['img_path', 'main_css_class', 'sub_css_class']);
        $collection->getSelect()
            ->where('main_table.is_active = ? ', 1)
            ->where('main_table.from_date is null or main_table.from_date <= ? ', $this->_localeDate->date()->format('Y-m-d'))
            ->where('main_table.to_date is null or main_table.to_date >= ? ', $this->_localeDate->date()->format('Y-m-d'));
        $collection->setOrder('main_table.sort_order', 'DESC');

        $result = $this->checkAndGetDiscountInfosSalesRule($collection, $productId);
        if (count($result) == 0) {
            $childIds = $this->getChildIds($productId);
            if ($childIds) {
                foreach (explode(',', $childIds) as $productId) {
                    $result = $this->checkAndGetDiscountInfosSalesRule($collection, $productId);
                    if (count($result) > 0) {
                        return $result;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * check and get discount infos salesrule
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $collection
     * @pram int $productId
     * @return array
     */
    private function checkAndGetDiscountInfosSalesRule($collection, $productId)
    {

        $result = [];
        foreach ($collection as $rule) {
            try {
               $stock = $this->stockRegistryRepository->getStockItem($productId);
               if ($stock->getIsInStock() && $stock->getQty() > 0) {
                    $product = $this->productRepository->getById($productId);

                    // we can only check salesrule condition using quote
                    $item = $this->quoteFactory->create();
                    $item->addProduct($product, 1);
                //    $logger->info('qty ' . $stock->getQty());

                    //Return True if Sales Rule validate product
                    $validate = $rule->getConditions()->validate($item);
                    if ($validate) {
                        $result['label'] = $rule->getDiscountLabel();
                        $result['imgPath'] = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $rule->getImgPath();
                        $result['main_class'] = $rule->getMainCssClass();
                        $result['sub_class'] = $rule->getSubCssClass();
                        $style = '';
                        if ($rule->getWidth() > 0 && $rule->getHeight() > 0) {
                            $style = 'width: ' . $rule->getWidth() . 'px !important; height: ' . $rule->getHeight() . 'px !important;';
                        } else if ($rule->getWidth() > 0) {
                            $style = 'width: ' . $rule->getWidth() . 'px !important;';
                        } else if ($rule->getHeight() > 0) {
                            $style = 'height: ' . $rule->getHeight() . 'px !important;';
                        }
                        $result['style'] = $style;
                        $result['sort_order'] = $rule->getSortOrder();
                        return $result;
                    }
               }
            } catch (NoSuchEntityException $e) {
	        //$logger->info($e->getMessage());
	    }
        }
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

