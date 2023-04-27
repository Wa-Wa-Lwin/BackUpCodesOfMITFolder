<?php

namespace MIT\Coupon\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class CouponChecker extends AbstractHelper
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    private $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializerInterface;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory
     */
    private $couponCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManagerInterface;

    public function __construct(
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Pricing\Helper\Data $helper,
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface,
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface
    ) {
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
        $this->helper = $helper;
        $this->serializerInterface = $serializerInterface;
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->localeDate = $localeDate;
        $this->cart = $cart;
        $this->objectManagerInterface = $objectManagerInterface;
    }

    /**
     * check to show custom coupon error for required amt
     * @param string $coupon
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    public function checkToShowCustomMessage($couponCode, $quote)
    {
        $coupon = $this->couponFactory->create()->load($couponCode, 'code');
        if ($coupon->getId()) {
            $rules = $this->ruleFactory->create();
            $rules->load($coupon->getRuleId());
            return $this->validateCondition($rules, $quote);
        }
        return '';
    }

    /**
     * validate condition
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    public function validateCondition($rule, $quote)
    {
        if ($rule->getIsActive() == 1) {
            $currentDate = date("Y-m-d");
            if ($rule->getFromDate() && $rule->getToDate()) {
                if ($currentDate >= $rule->getFromDate() && $currentDate <= $rule->getToDate()) {
                    $conditions = $this->getConditions($rule);
                    return $this->processCondition($quote, $conditions);
                }
            } else if ($rule->getFromDate()) {
                if ($currentDate >= $rule->getFromDate()) {
                    $conditions = $this->getConditions($rule);
                    return $this->processCondition($quote, $conditions);
                }
            } else if ($rule->getToDate()) {
                if ($currentDate >= $rule->getToDate()) {
                    $conditions = $this->getConditions($rule);
                    return $this->processCondition($quote, $conditions);
                }
            }
        }
        return '';
    }

    /**
     * check coupon rule with quote
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $condition
     * @return string
     */
    protected function processCondition($quote, $condition = [])
    {
        if (count($condition) > 0) {
            if (
                isset($condition[0]['type']) && isset($condition[0]['attribute']) &&
                isset($condition[0]['operator']) && isset($condition[0]['value'])
            ) {
                if ($condition[0]['type'] == 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address') {
                    $attribute = $condition[0]['attribute'];
                    $operator = $condition[0]['operator'];
                    $value = $condition[0]['value'];
                    $requireAmt = 0;
                    if ($operator == '>=' && $attribute == 'base_subtotal') {
                        $requireAmt = (int) $value - $quote->getBaseSubtotal();
                    } else if ($operator == '>' && $attribute == 'base_subtotal') {
                        $requireAmt = ((int) $value + 1) - $quote->getBaseSubtotal();
                    }
                    if ($requireAmt > 0) {
                        $formattedPrice = $this->helper->currency($requireAmt, true, false);
                        $pos = strpos($formattedPrice, '.');
                        if ($pos === false) {
                            return $formattedPrice;
                        } else {
                            return rtrim(rtrim($formattedPrice, '0'), '.');
                        }
                    }
                }
            }
        }
        return '';
    }

    /**
     * unserialize coupon rule
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return array
     */
    protected function getConditions($rule)
    {

        if ($rule->getId()) {
            $data = $this->serializerInterface->unserialize($rule->getConditionsSerialized());
            //$this->_conditions = $data;
            if (isset($data['conditions'])) {
                return $data['conditions'];
            }
        }

        return [];
    }

    /**
     * get one active coupon rule
     * @return string
     */
    protected function getActiveCouponCode()
    {
        $couponCollection = $this->couponCollectionFactory->create();
        $couponCollection->addFieldToSelect('code');
        $couponCollection->getSelect()->joinInner(
            'salesrule',
            'salesrule.rule_id = main_table.rule_id',
            'name'
        );

        $couponCollection->getSelect()
            ->where('salesrule.is_active = ? ', 1)
            ->where('salesrule.from_date is null or salesrule.from_date <= ? ', $this->localeDate->date()->format('Y-m-d'))
            ->where('salesrule.to_date is null or salesrule.to_date >= ? ', $this->localeDate->date()->format('Y-m-d'));
        $couponCollection->setOrder('salesrule.sort_order', 'DESC');

        $data = $couponCollection->getFirstItem();
        if (null !== $data['code']) {
            return $data['code'];
        }
        return '';
    }

    /**
     * get coupon message
     * @return array
     */
    public function getCouponMessageMiniCart()
    {
        $result = [];
        $couponCode = $this->getActiveCouponCode();
        if ($couponCode) {
            $cartQuote = $this->cart->getQuote();
            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $escaper = $this->objectManagerInterface->get(\Magento\Framework\Escaper::class);
                $requireAmtStr = $this->checkToShowCustomMessage($couponCode, $cartQuote);
                if ($requireAmtStr) {
                    $result = ['"' . $escaper->escapeHtml($requireAmtStr) . '"', '"' . $escaper->escapeHtml($couponCode) . '"'];
                }
            }
        }

        return $result;
    }
}
