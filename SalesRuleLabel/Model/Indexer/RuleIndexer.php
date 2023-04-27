<?php

namespace MIT\SalesRuleLabel\Model\Indexer;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Profiler;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use MIT\SalesRuleLabel\Model\ResourceModel\CustomCondition\Collection as DiscountLabelCollection;
use MIT\SalesRuleLabel\Model\ResourceModel\CustomCondition\CollectionFactory as DiscountLabelCollectionFactory;
use MIT\SalesRuleLabel\Model\CustomCondition;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class RuleIndexer
 * @package Mageplaza\AutoRelated\Model\Indexer
 */
class RuleIndexer extends IndexBuilder
{
    /**
     * @var DiscountLabelCollectionFactory
     */
    protected $discountLabelCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Product[]
     */
    protected $products;

    /**
     * @var TimezoneInterface|mixed
     */
    private $localeDate;

    /**
     * RuleIndexer constructor.
     *
     * @param CollectionFactory $DiscountLabelCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param Config $eavConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateFormat
     * @param DateTime $dateTime
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param DiscountLabelCollectionFactory $discountLabelCollectionFactory
     * @param int $batchCount
     */
    public function __construct(
        CollectionFactory $DiscountLabelCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Config $eavConfig,
        \Magento\Framework\Stdlib\DateTime $dateFormat,
        DateTime $dateTime,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        DiscountLabelCollectionFactory $discountLabelCollectionFactory,
        $batchCount = 1000,
        TimezoneInterface $localeDate = null
    ) {
        $this->connection               = $resource->getConnection();
        $this->productRepository        = $productRepository;
        $this->searchCriteriaBuilder    = $criteriaBuilder;
        $this->discountLabelCollectionFactory = $discountLabelCollectionFactory;
        $this->batchCount               = $batchCount;

        $this->localeDate = $localeDate ??
            ObjectManager::getInstance()->get(TimezoneInterface::class);

        parent::__construct(
            $DiscountLabelCollectionFactory,
            $priceCurrency,
            $resource,
            $storeManager,
            $logger,
            $eavConfig,
            $dateFormat,
            $dateTime,
            $productFactory,
            $batchCount
        );
    }

    /**
     * Full reindex
     *
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexFull()
    {
        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/reindex.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('sender builder full');
            $this->doReindexFull();
        } catch (Exception $e) {
            $this->critical($e);
            throw new LocalizedException(
                __('MIT SalesRuleLabel indexing failed. See details in exception log.')
            );
        }
    }

    /**
     * Full reindex Rule method
     *
     * @return void
     */
    protected function doReindexFull()
    {
        $this->connection->truncateTable(
            $this->getTable('mit_salesrule_label_product')
        );

        foreach ($this->getActiveRules() as $rule) {
            $this->execute($rule);
        }
    }

    /**
     * Get active rules
     *
     * @return DiscountLabelCollection
     */
    protected function getActiveRules()
    {
        return $this->getAllDiscountLabelRules()->addFieldToFilter('rule_status', 1);
    }

    /**
     * @return DiscountLabelCollection
     */
    protected function getAllDiscountLabelRules()
    {
        return $this->discountLabelCollectionFactory->create();
    }

    /**
     * Reindex data about rule relations with products.
     *
     * @param CustomCondition $rule
     *
     * @return bool
     */
    protected function execute(CustomCondition $rule)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/reindex.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('sender builder execute');
        if (!$rule->getData('rule_status')) {
            return false;
        }

        Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule->getMatchProductIds();
        Profiler::stop('__MATCH_PRODUCTS__');

        //$logger->info(json_encode($productIds));

        $indexTable = $this->resource->getTableName('mit_salesrule_label_product');
        $rows = [];

        foreach (explode(',', $rule->getWebsites()) as $websiteId) {
            $scopeTz = new \DateTimeZone(
                $this->localeDate->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $websiteId)
            );
            $fromTime = $rule->getFromDate()
                ? (new \DateTime($rule->getFromDate(), $scopeTz))->getTimestamp()
                : 0;
            $toTime = $rule->getToDate()
                ? (new \DateTime($rule->getToDate(), $scopeTz))->getTimestamp() + IndexBuilder::SECONDS_IN_DAY - 1
                : 0;
            foreach ($productIds as $productId => $validationByWebsite) {
                if (empty($validationByWebsite[$websiteId])) {
                    continue;
                }
                foreach (explode(',', $rule->getCustomerGroups()) as $customerGroupId) {
                    $rows[] = [
                        'rule_id'    => $rule->getRuleId(),
                        'product_id' => $productId,
                        'from_time' => $fromTime,
                        'to_time' => $toTime,
                        'website_id' => $websiteId,
                        'customer_group_id' => $customerGroupId,
                        'sort_order' => $rule->getSortOrder(),
                        'discount_image_id' => $rule->getDiscountImageId(),
                        'discount_label' => $rule->getDiscountLabel(),
                        'width' => $rule->getWidth(),
                        'height' => $rule->getHeight(),
                        'discount_label_color' => $rule->getDiscountLabelColor(),
                        'discount_label_style' => $rule->getDiscountLabelStyle()
                    ];

                    if (count($rows) == $this->batchCount) {
                        $this->connection->insertMultiple($indexTable, $rows);
                        $rows = [];
                    }
                }
            }
        }

        if ($rows) {
            $this->connection->insertMultiple($indexTable, $rows);
        }

        return true;
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     *
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexByIds(array $ids)
    {
        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/reindex1.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('sender builder called');
            $this->doReindexByIds($ids);
        } catch (Exception $e) {
            $this->critical($e);
            throw new LocalizedException(
                __('MIT SalesRuleLabel indexing failed. See details in exception log.')
            );
        }
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     *
     * @return void
     * @throws Exception
     */
    protected function doReindexByIds($ids)
    {
        $this->cleanByIds($ids);

        $products    = $this->getProducts($ids);
        $activeRules = $this->getActiveRules();
        foreach ($products as $product) {
            $this->applyDiscountLabelRules($activeRules, $product);
        }
    }

    /**
     * Clean by product ids
     *
     * @param array $productIds
     *
     * @return void
     */
    protected function cleanByIds($productIds)
    {
        $query = $this->connection->deleteFromSelect(
            $this->connection
                ->select()
                ->from($this->resource->getTableName('mit_salesrule_label_product'), 'product_id')
                ->distinct()
                ->where('product_id IN (?)', $productIds),
            $this->resource->getTableName('mit_salesrule_label_product')
        );
        $this->connection->query($query);
    }

    /**
     * Get products by ids
     *
     * @param array $productIds
     *
     * @return Product[]
     */
    public function getProducts($productIds)
    {
        if ($this->products === null) {
            $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in');
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $this->products = $this->productRepository->getList($searchCriteria)->getItems();
        }

        return $this->products;
    }

    /**
     * @param DiscountLabelCollection $ruleCollection
     * @param Product $product
     *
     * @throws Exception
     */
    protected function applyDiscountLabelRules(DiscountLabelCollection $ruleCollection, Product $product)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/indexer.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('sender builder called');

        /** @var CustomCondition $rule */
        foreach ($ruleCollection as $rule) {
            $logger->info($rule->getWebsites());
            $logger->info($rule->getCustomerGroups());
            $logger->info($rule->getRuleName());
            $logger->info($rule->getDiscountLabel());
            $websiteIds = array_intersect($product->getWebsiteIds(), explode(',', $rule->getWebsites()));
            $this->assignProductToDiscountLabelRules($rule, $product, $websiteIds);
        }
    }

    /**
     * @param CustomCondition $rule
     * @param Product $product
     * @param array $websiteids
     *
     * @throws Exception
     */
    public function assignProductToDiscountLabelRules(CustomCondition $rule, Product $product, array $websiteIds)
    {
        if (!$rule->validate($product)) {
            return;
        }

        $ruleId           = (int)$rule->getRuleId();
        $productEntityId  = (int)$product->getId();
        $ruleProductTable = $this->getTable('mit_salesrule_label_product');
        $this->connection->delete(
            $ruleProductTable,
            [
                'rule_id = ?'    => $ruleId,
                'product_id = ?' => $productEntityId,
            ]
        );

        $rows = [];

        foreach ($websiteIds as $websiteId) {
            $scopeTz = new \DateTimeZone(
                $this->localeDate->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $websiteId)
            );
            $fromTime = $rule->getFromDate()
                ? (new \DateTime($rule->getFromDate(), $scopeTz))->getTimestamp()
                : 0;
            $toTime = $rule->getToDate()
                ? (new \DateTime($rule->getToDate(), $scopeTz))->getTimestamp() + IndexBuilder::SECONDS_IN_DAY - 1
                : 0;

            foreach (explode(',', $rule->getCustomerGroups()) as $customerGroupId) {
                $rows[] = [
                    'rule_id'    => $ruleId,
                    'product_id' => $productEntityId,
                    'from_time' => $fromTime,
                    'to_time' => $toTime,
                    'website_id' => $websiteId,
                    'customer_group_id' => $customerGroupId,
                    'sort_order' => $rule->getSortOrder(),
                    'discount_image_id' => $rule->getDiscountImageId(),
                    'discount_label' => $rule->getDiscountLabel(),
                    'width' => $rule->getWidth(),
                    'height' => $rule->getHeight(),
                    'discount_label_color' => $rule->getDiscountLabelColor(),
                    'discount_label_style' => $rule->getDiscountLabelStyle()
                ];

                if (count($rows) == $this->batchCount) {
                    $this->connection->insertMultiple($ruleProductTable, $rows);
                    $rows = [];
                }
            }
        }

        if ($rows) {
            $this->connection->insertMultiple($ruleProductTable, $rows);
        }
    }

    /**
     * @param CustomCondition $rule
     * @param Product $product
     *
     * @return $this
     * @throws Exception
     */
    protected function applyARPRule(CustomCondition $rule, $product)
    {
        $websiteIds = array_intersect($product->getWebsiteIds(), explode(',', $rule->getWebsites()));
        $this->assignProductToDiscountLabelRules($rule, $product, $websiteIds);
        return $this;
    }
}
