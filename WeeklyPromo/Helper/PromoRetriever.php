<?php

namespace MIT\WeeklyPromo\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Expr;

class PromoRetriever extends AbstractHelper
{
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory
     */
    private $catalogRuleCollectionFactory;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    private $salesRuleCollectionFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializerInterface;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $catalogRuleCollectionFactory,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $salesRuleCollectionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface,
        ProductCollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->catalogRuleCollectionFactory = $catalogRuleCollectionFactory;
        $this->salesRuleCollectionFactory = $salesRuleCollectionFactory;
        $this->serializerInterface = $serializerInterface;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * get weekly prmotion sku list and sort according to sort order
     * @return string
     */
    public function getWeeklyPromoList()
    {
        $catalogRuleList = $this->getCatalogRuleWeeklyPromo();
        $salesRuleList = $this->getSalesRuleWeeklyPromo();
        $mergedRuleList = array_merge($catalogRuleList, $salesRuleList);
        usort($mergedRuleList, function ($a, $b) {
            return $b["sort_order"] - $a["sort_order"];
        });
        $skuList = [];
        foreach ($mergedRuleList as $skus) {
            $skuList = array_merge($skuList, explode(',', $skus['skus']));
        }
        return implode(',' ,$this->getExistedProductSkuList(array_unique($skuList)));
    }

    /**
     * get weekly promo's catalog price rule
     * @return array
     */
    private function getCatalogRuleWeeklyPromo()
    {
        $catalogRuleList = [];
        $collection = $this->catalogRuleCollectionFactory->create();
        $collection->addFieldToFilter('is_weekly_promotion', ['eq' => 1]);
        $collection->addFieldToFilter('is_active', ['eq' => 1]);
        $collection->getSelect()->order('sort_order', \Magento\Framework\DB\Select::SQL_DESC);
        foreach ($collection as $rule) {
            /** @var \MIT\CatalogRule\Model\Rule */
            $catalogRule = $rule->getData();
            $skus = $this->getCatalogRuleSkus($catalogRule);
            if ($this->checkValidRuleDateRange($catalogRule['from_date'], $catalogRule['to_date']) && $skus) {
                $catalogRuleList[] = array(
                    'sort_order' => $catalogRule['sort_order'],
                    'skus' => $skus
                );
            }
        }
        return $catalogRuleList;
    }

    /**
     * get sku from catalog rule
     * @param mixed $catalogRule
     * @return string
     */
    private function getCatalogRuleSkus($catalogRule)
    {
        $conditions = $this->getRuleConditionUnserialized($catalogRule['conditions_serialized']);
        if (null !== $conditions && count($conditions) > 0) {
            if (
                $conditions[0]['type'] == 'Magento\\CatalogRule\\Model\\Rule\\Condition\\Product' &&
                $conditions[0]['attribute'] == 'sku' && $conditions[0]['operator'] == '()'
            ) {
                return $conditions[0]['value'];
            }
        }
        return '';
    }

    /**
     * get weekly promo's cart price rule
     * @return array
     */
    private function getSalesRuleWeeklyPromo()
    {
        $salesRuleList = [];
        $collection = $this->salesRuleCollectionFactory->create();
        $collection->addFieldToFilter('is_weekly_promotion', ['eq' => 1]);
        $collection->addFieldToFilter('is_active', ['eq' => 1]);
        $collection->getSelect()->order('sort_order', \Magento\Framework\DB\Select::SQL_DESC);
        foreach ($collection as $rule) {
            /** @var \Magento\SalesRule\Model\Rule */
            $salesRule = $rule->getData();
            $skus = $this->getSaleRuleSkus($salesRule);
            if ($this->checkValidRuleDateRange($salesRule['from_date'], $salesRule['to_date']) && $skus) {
                $salesRuleList[] = array(
                    'sort_order' => $salesRule['sort_order'],
                    'skus' => $skus
                );
            }
        }
        return $salesRuleList;
    }

    /**
     * get sku from cart price rule
     * @param mixed $salesRule
     * @return string
     */
    public function getSaleRuleSkus($salesRule)
    {
        $conditions = $this->getConditions($salesRule);
        if (count($conditions) > 0 && isset($conditions[0]['conditions']) && count($conditions[0]['conditions']) > 0) {
            if (
                isset($conditions[0]['conditions'][0]['type']) && isset($conditions[0]['conditions'][0]['attribute']) &&
                isset($conditions[0]['conditions'][0]['operator']) && isset($conditions[0]['conditions'][0]['value'])
            ) {
                if (
                    $conditions[0]['conditions'][0]['type'] == 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product' && $conditions[0]['conditions'][0]['attribute'] == 'sku' &&
                    $conditions[0]['conditions'][0]['operator'] == '()'
                ) {
                    return $conditions[0]['conditions'][0]['value'];
                }
            }
        }
        return '';
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

    /**
     * retrieve only existed product sku
     * @param array $skuList
     * @return array
     */
    private function getExistedProductSkuList(array $skuList) {
        $existedSkuList = [];
        $collection = $this->productCollectionFactory->create()->addFieldToSelect('sku');
        $collection->addFieldToFilter('sku', ['in' => $skuList]);

        $collection->getSelect()
        ->order(new Zend_Db_Expr('FIELD(e.sku,' . "'" .  implode(',', $skuList) . "'" . ')'));
        foreach($collection as $item) {
            $existedSkuList[] = $item['sku'];
        }
        return $existedSkuList;
    }

    /**
     * retrieve only existed product sku
     * @param array $skuList
     * @return array
     */
    private function getExistedProductIdList(array $skuList) {
        $existedIdList = [];
        $collection = $this->productCollectionFactory->create()->addFieldToSelect('entity_id');
        $collection->addFieldToFilter('sku', ['in' => $skuList]);

        $collection->getSelect()
        ->order(new Zend_Db_Expr('FIELD(e.sku,' . "'" .  implode(',', $skuList) . "'" . ')'));
        foreach($collection as $item) {
            $existedIdList[] = $item['entity_id'];
        }
        return $existedIdList;
    }

    /**
     * unserialize sales rule action
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return array
     */
    protected function getActions($rule)
    {
        if ($rule['rule_id']) {
            return $this->getRuleConditionUnserialized($rule['actions_serialized']);
        }
        return [];
    }

    /**
     * get free shipping product skus
     * @return array
     */
    private function getFreeShippingProductIds() {
        $freeShippingSkuList = [];
        $collection = $this->salesRuleCollectionFactory->create();
        $collection->addFieldToFilter('apply_to_shipping', ['eq' => 1]);
        $collection->addFieldToFilter('simple_free_shipping', ['gt' => 0]);
        $collection->addFieldToFilter('is_active', ['eq' => 1]);
        $collection->getSelect()->order('sort_order', \Magento\Framework\DB\Select::SQL_DESC);
        foreach ($collection as $rule) {
            /** @var \Magento\SalesRule\Model\Rule */
            $salesRule = $rule->getData();
            $skus = $this->getSaleRuleActionSkus($salesRule);
            if ($this->checkValidRuleDateRange($salesRule['from_date'], $salesRule['to_date']) && $skus) {
                $freeShippingSkuList = array_merge($freeShippingSkuList, explode(',', $skus));
            }
        }
        return $this->getExistedProductIdList($freeShippingSkuList);
    }

    /**
     * get sku from cart price rule action
     * @param mixed $salesRule
     * @return string
     */
    public function getSaleRuleActionSkus($salesRule)
    {
        $conditions =  $this->getActions($salesRule);

        if (count($conditions) > 0 && isset($conditions[0]['type']) && isset($conditions[0]['attribute']) && isset($conditions[0]['value'])) {
                if (
                    $conditions[0]['type'] == 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product' && $conditions[0]['attribute'] == 'sku' &&
                    $conditions[0]['operator'] == '()'
                ) {
                    return $conditions[0]['value'];
                }
        }
        return '';
    }

    /**
     * check product id was in free shipping rule
     * @param array $productIdList
     * @return bool
     */
    public function isFreeShipping($productIdList) {
        return count(array_intersect($productIdList, $this->getFreeShippingProductIds())) > 0;
    }

    /**
     * get free shipping img path
     * @return string
     */
    public function getFreeShippingImgPath() {
        return $this->storeManagerInterface->getStore()->getBaseUrl() . 'media' . '/ribbon/free-one.png';
    }

}
