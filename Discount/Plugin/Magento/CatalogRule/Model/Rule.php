<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Plugin\Magento\CatalogRule\Model;

use DateTime;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Rule
{
    const CATALOG_LABEL_TABLE = 'mit_discount_label';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    public function __construct(
        ResourceConnection $resourceConnection,
        TimezoneInterface $localeDate
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->localeDate = $localeDate;
    }

    public function afterAfterSave(
        \Magento\CatalogRule\Model\Rule $subject,
        $result
    ) {
        $productIds = $subject->getMatchingProductIds();
        $this->deleteCatalogRuleLabelIndex($subject->getRuleId());

        if (!$subject->getIsActive()) {
            return $result;
        }

        if (!empty($productIds) && is_array($productIds)) {
            foreach($subject->getWebsiteIds() as $websiteId) {
                $scopeTz = new \DateTimeZone(
                    $this->localeDate->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $websiteId)
                );

                if ($subject->getFromDate() instanceof DateTime) {
                    $subject->setFromDate($subject->getFromDate()->format('Y-m-d'));
                }

                if ($subject->getToDate() instanceof DateTime) {
                    $subject->setToDate($subject->getToDate()->format('Y-m-d'));
                }
                $rows = [];

                $fromTime = $subject->getFromDate()
                    ? (new \DateTime($subject->getFromDate(), $scopeTz))->getTimestamp()
                    : 0;
                $toTime = $subject->getToDate()
                    ? (new \DateTime($subject->getToDate(), $scopeTz))->getTimestamp() + IndexBuilder::SECONDS_IN_DAY - 1
                    : 0;
                foreach ($productIds as $productId => $validationByWebsite) {
                    if (empty($validationByWebsite[$websiteId])) {
                        continue;
                    }
                    foreach ($subject->getCustomerGroupIds() as $customerGroupId) {
                        $rows[] = [
                            'rule_id'    => $subject->getRuleId(),
                            'product_id' => $productId,
                            'from_time' => $fromTime,
                            'to_time' => $toTime,
                            'website_id' => $websiteId,
                            'customer_group_id' => $customerGroupId,
                            'sort_order' => $subject->getSortOrder(),
                            'discount_img_id' => $subject->getDiscountImageId(),
                            'discount_label' => $subject->getDiscountLabel(),
                            'width' => $subject->getWidth(),
                            'height' => $subject->getHeight(),
                            'discount_label_color' => $subject->getDiscountLabelColor(),
                            'discount_label_style' => $subject->getDiscountLabelStyle()
                        ];

                        if (count($rows) == 1000) {
                            $this->updateMultiplCatalogRuleLabel($rows);
                            $rows = [];
                        }
                    }
                }
            }
            if ($rows) {
                $this->updateMultiplCatalogRuleLabel($rows);
            }
        }
        return $result;
    }

    public function afterAfterDelete(
        \Magento\CatalogRule\Model\Rule $subject,
        $result
    ) {
        $this->deleteCatalogRuleLabelIndex($subject->getRuleId());
        return $result;
    }

    /**
     * delete existed rule label before product calculation
     * @param int $ruleId
     * @return void
     */
    private function deleteCatalogRuleLabelIndex($ruleId) {
        $connection = $this->resourceConnection->getConnection();
        $tableName =  $this->resourceConnection->getTableName(self::CATALOG_LABEL_TABLE);
        $connection->delete($tableName, ['rule_id = ?' => $ruleId]);
    }

    /**
     * insert rule label
     * @param array $data
     * @return void
     */
    private function updateMultiplCatalogRuleLabel($data = []) {
        $connection = $this->resourceConnection->getConnection();
        $tableName =  $this->resourceConnection->getTableName(self::CATALOG_LABEL_TABLE);
        $connection->insertMultiple($tableName, $data);
    }
}
