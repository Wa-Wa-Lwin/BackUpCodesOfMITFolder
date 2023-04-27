<?php

namespace MIT\SalesRuleLabel\Model;

use DateTime;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use MIT\SalesRuleLabel\Model\ResourceModel\CustomCondition as CustomConditionResourceModel;
use Magento\Quote\Model\Quote\Address;
use Magento\Rule\Model\AbstractModel;
use Magento\Store\Model\ScopeInterface;
use MIT\SalesRuleLabel\Api\Data\CustomConditionInterface;

class CustomCondition extends AbstractModel implements CustomConditionInterface
{
    protected $_eventPrefix = 'mit_salesrulelabel';
    protected $_eventObject = 'rule';
    protected $condCombineFactory;
    protected $condProdCombineF;
    protected $validatedAddresses = [];
    protected $_selectProductIds;
    protected $_displayProductIds;
    private $localeDate;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;
        $this->localeDate = $localeDate;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init(CustomConditionResourceModel::class);
        $this->setIdFieldName('rule_id');
    }

    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    public function getActionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? true : false;
    }

    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->validatedAddresses[$addressId] = $validationResult;
        return $this;
    }

    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? $this->validatedAddresses[$addressId] : false;
    }

    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }
        return $address;
    }

    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    public function getActionFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }

    public function getMatchProductIds()
    {
        $productCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection'
        );
        $productFactory = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Catalog\Model\ProductFactory'
        );
        $this->_selectProductIds = [];
        $this->setCollectedAttributes([]);
        $this->getConditions()->collectValidatedAttributes($productCollection);
        \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Model\ResourceModel\Iterator'
        )->walk(
            $productCollection->getSelect(),
            [[$this, 'callbackValidateProductCondition']],
            [
                'attributes' => $this->getCollectedAttributes(),
                'product' => $productFactory->create(),
            ]
        );
        return $this->_selectProductIds;
    }

    public function callbackValidateProductCondition($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $websites = $this->_getWebsitesMap();
        $ruleId = $this->getRuleId();
        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = $this->getConditions()->validate($product);
            // $product->setStoreId($defaultStoreId);
            // if ($this->getConditions()->validate($product)) {
            //     $this->_selectProductIds[] = ['rule_id' => $ruleId, 'product_id' => $product->getId()];
            // }
        }
        $this->_selectProductIds[$product->getId()] = $results;
    }

    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Store\Model\StoreManagerInterface'
        )->getWebsites();
        foreach ($websites as $website) {
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }

    public function afterSave()
    {

        $this->reindex();


        return parent::afterSave();
    }

    public function afterDelete()
    {
        $this->getResource()->deleteActionIndex($this->getId());
        return parent::afterDelete();
    }

    public function reindex()
    {
        $this->getMatchProductIds();
        $this->getResource()->deleteActionIndex($this->getId());
        if (!empty($this->_selectProductIds) && is_array($this->_selectProductIds)) {
            foreach (explode(',', $this->getWebsites()) as $websiteId) {
                $scopeTz = new \DateTimeZone(
                    $this->localeDate->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $websiteId)
                );

                if ($this->getFromDate() instanceof DateTime) {
                    $this->setFromDate($this->getFromDate()->format('Y-m-d'));
                }

                if ($this->getToDate() instanceof DateTime) {
                    $this->setToDate($this->getToDate()->format('Y-m-d'));
                }
                $rows = [];

                $fromTime = $this->getFromDate()
                    ? (new \DateTime($this->getFromDate(), $scopeTz))->getTimestamp()
                    : 0;
                $toTime = $this->getToDate()
                    ? (new \DateTime($this->getToDate(), $scopeTz))->getTimestamp() + IndexBuilder::SECONDS_IN_DAY - 1
                    : 0;
                foreach ($this->_selectProductIds as $productId => $validationByWebsite) {
                    if (empty($validationByWebsite[$websiteId])) {
                        continue;
                    }
                    foreach (explode(',', $this->getCustomerGroups()) as $customerGroupId) {
                        $rows[] = [
                            'rule_id'    => $this->getRuleId(),
                            'product_id' => $productId,
                            'from_time' => $fromTime,
                            'to_time' => $toTime,
                            'website_id' => $websiteId,
                            'customer_group_id' => $customerGroupId,
                            'sort_order' => $this->getSortOrder(),
                            'discount_image_id' => $this->getDiscountImageId(),
                            'discount_label' => $this->getDiscountLabel(),
                            'width' => $this->getWidth(),
                            'height' => $this->getHeight(),
                            'discount_label_color' => $this->getDiscountLabelColor(),
                            'discount_label_style' => $this->getDiscountLabelStyle()
                        ];

                        if (count($rows) == 1000) {
                            $this->getResource()->insertActionIndex($rows);
                            $rows = [];
                        }
                    }
                }
            }

            if ($rows) {
                $this->getResource()->insertActionIndex($rows);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRuleId($ruleId)
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    /**
     * @inheritDoc
     */
    public function getRuleName()
    {
        return $this->getData(self::RULE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setRuleName($ruleName)
    {
        return $this->setData(self::RULE_NAME, $ruleName);
    }

    /**
     * @inheritDoc
     */
    public function getWebsites()
    {
        return $this->getData(self::WEBSITES);
    }

    /**
     * @inheritDoc
     */
    public function setWebsites($websites)
    {
        return $this->setData(self::WEBSITES, $websites);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerGroups()
    {
        return $this->getData(self::CUSTOMER_GROUPS);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerGroups($customerGroups)
    {
        return $this->setData(self::CUSTOMER_GROUPS, $customerGroups);
    }

    /**
     * @inheritDoc
     */
    public function getRuleStatus()
    {
        return $this->getData(self::RULE_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setRuleStatus($ruleStatus)
    {
        return $this->setData(self::RULE_STATUS, $ruleStatus);
    }

    /**
     * @inheritDoc
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getActionsSerialized()
    {
        return $this->getData(self::ACTIONS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setActionsSerialized($actionsSerialized)
    {
        return $this->setData(self::ACTIONS_SERIALIZED, $actionsSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getSaleRuleId()
    {
        return $this->getData(self::SALE_RULE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSaleRuleId($saleRuleId)
    {
        return $this->setData(self::SALE_RULE_ID, $saleRuleId);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountImageId()
    {
        return $this->getData(self::DISCOUNT_IMAGE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountImageId($discountImageId)
    {
        return $this->setData(self::DISCOUNT_IMAGE_ID, $discountImageId);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountLabel()
    {
        return $this->getData(self::DISCOUNT_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountLabel($discountLabel)
    {
        return $this->setData(self::DISCOUNT_LABEL, $discountLabel);
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        return $this->getData(self::WIDTH);
    }

    /**
     * @inheritDoc
     */
    public function setWidth($width)
    {
        return $this->setData(self::WIDTH, $width);
    }

    /**
     * @inheritDoc
     */
    public function getHeight()
    {
        return $this->getData(self::HEIGHT);
    }

    /**
     * @inheritDoc
     */
    public function setHeight($height)
    {
        return $this->setData(self::HEIGHT, $height);
    }

    /**
     * @inheritDoc
     */
    public function getFromDate()
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setFromDate($fromDate)
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * @inheritDoc
     */
    public function getToDate()
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setToDate($toDate)
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountLabelColor()
    {
        return $this->getData(self::DISCOUNT_LABEL_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountLabelColor($discountColor)
    {
        return $this->setData(self::DISCOUNT_LABEL_COLOR, $discountColor);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountLabelStyle()
    {
        return $this->getData(self::DISCOUNT_LABEL_STYLE);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountLabelStyle($discountStyle)
    {
        return $this->setData(self::DISCOUNT_LABEL_STYLE, $discountStyle);
    }
}
