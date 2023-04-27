<?php

namespace MIT\SalesRule\Model\Rule\Action\Discount;

use Magento\Catalog\Model\ProductRepository;
use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as SalesRuleCollectionFactory;

class BuyXGetYAmount extends AbstractDiscount
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var SerializerInterface
     */
    private $serializerInterface;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;
    
    /**
     * @var SalesRuleCollectionFactory
     */
    private $salesRuleCollectionFactory;

    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        ProductRepository $productRepository,
        SerializerInterface $serializerInterface,
        Configurable $configurable,
        ProductCollectionFactory $productCollectionFactory,
        SalesRuleCollectionFactory $salesRuleCollectionFactory
    ) {
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
        $this->productRepository = $productRepository;
        $this->serializerInterface = $serializerInterface;
        $this->configurable = $configurable;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->salesRuleCollectionFactory = $salesRuleCollectionFactory;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        if ($qty < $rule->getDiscountStep()) {
            return $discountData;
        }

       if ($this->canApplyRule($rule, $item->getProduct()->getSku())) {
            $discountStep = $rule->getDiscountStep() ? $rule->getDiscountStep() : 1;
            $maxDiscountProduct = $rule->getDiscountQty();
            $discountQty = $qty;

            if ($maxDiscountProduct > 0) {
                if ($qty >= $maxDiscountProduct) {
                    $discountQty = $maxDiscountProduct;
                }
            }
            $discount = $rule->getDiscountAmount() * ($discountQty / $discountStep);

            $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $item->getQuote()->getStore());
            $discountData->setAmount($quoteAmount * ($discountQty / $discountStep));
            $discountData->setBaseAmount($discount);
       }
        return $discountData;
    }

    /**
     * @param float $qty
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return float
     */
    public function fixQuantity($qty, $rule)
    {
        $step = $rule->getDiscountStep();
        if ($step) {
            $qty = floor($qty / $step) * $step;
        }

        return $qty;
    }

    /**
     * check rule can apply to current product or not
     * 
     * @param \Magento\SalesRule\Model\Rule $saleRule
     * @param string $sku
     * @return bool
     */
    private function canApplyRule($saleRule, $sku) {
        $rule = $this->salesRuleCollectionFactory->create()->addFieldToFilter('rule_id', ['eq' => $saleRule->getId()])->getFirstItem();

        $canApply = false;
        if ($rule['rule_id'] && isset($rule['conditions_serialized'])) {
            $data = $this->serializerInterface->unserialize($rule['conditions_serialized']);
            if (isset($data['conditions'])) {
                $conditions = $data['conditions'];
                if (count($conditions) > 0 && isset($conditions[0]['conditions']) && count($conditions[0]['conditions']) > 0) {
                    $skuList = $this->retrieveParentSku($sku);
           
                    if (
                        isset($conditions[0]['conditions'][0]['type']) && isset($conditions[0]['conditions'][0]['attribute']) &&
                        isset($conditions[0]['conditions'][0]['operator']) && isset($conditions[0]['conditions'][0]['value'])
                    ) {
                        if (
                            $conditions[0]['conditions'][0]['type'] == 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product' && $conditions[0]['conditions'][0]['attribute'] == 'sku' &&
                            $conditions[0]['conditions'][0]['operator'] == '()'
                        ) {
                            return $canApply = count(array_intersect($skuList, explode(',', $conditions[0]['conditions'][0]['value']))) > 0;
                        } else if ($conditions[0]['conditions'][0]['type'] == 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product' && $conditions[0]['conditions'][0]['attribute'] == 'category_ids') {
                            $product = $this->productRepository->get($sku);
                            return $canApply =  count(array_intersect($product->getCategoryIds(), explode(',', $conditions[0]['conditions'][0]['value']))) > 0;
                        }
                    }
                }
            }
        }
        return $canApply;
    }

    /**
     * get parent skus
     * @param string $childSku
     * @return array
     */
    private function retrieveParentSku($childSku) {
        $skuList = [];
        try {
            $product = $this->productRepository->get($childSku);
            $parentSku = $this->configurable->getParentIdsByChild($product->getId());
            if ($parentSku) {
                $collection = $this->productCollectionFactory->create()->addFieldToSelect('sku');
                $collection->addFieldToFilter('entity_id', ['in' => $parentSku]);
                foreach($collection as $item) {
                    $skuList[] = $item['sku'];
                }
            }
            $skuList[] = $childSku;
        } catch(NoSuchEntityException $e) {

        }
        return $skuList;
    }
}
