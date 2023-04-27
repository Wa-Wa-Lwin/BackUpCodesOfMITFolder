<?php

namespace MIT\Discount\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Other\Rule\Model\ResourceModel\ImageCollection\CollectionFactory as ImageCollectionFactory;

class OtherRuleHelper extends AbstractHelper
{
    const CATALOG_RULE_TYPE = '1';
    const CART_PRICE_RULE_TYPE = '2';
    const CATALOG_RULE_FILED_NAME = 'catalog_rule_id';
    const CART_PRICE_RULE_FIELD_NAME = 'sale_rule_id';

    /**
     * @var ImageCollectionFactory
     */
    private $imageCollectionFactory;

    /**
     * @var PromotionPageHelper
     */
    private $promotionPageHelper;

    public function __construct(
        Context $context,
        ImageCollectionFactory $imageCollectionFactory,
        PromotionPageHelper $promotionPageHelper
    ) {
        $this->imageCollectionFactory = $imageCollectionFactory;
        $this->promotionPageHelper = $promotionPageHelper;
        parent::__construct($context);
    }

    /**
     * check rule was chosen or not in promotion page
     * @param int $ruleId
     * @param int $type
     */
    public function regeneratePromotionPage($ruleId, $type)
    {
        $fieldname = 'catalog_rule_id';
        if ($type == self::CATALOG_RULE_TYPE) {
            $fieldname = self::CATALOG_RULE_FILED_NAME;
        } else if ($type == self::CART_PRICE_RULE_TYPE) {
            $fieldname = self::CART_PRICE_RULE_FIELD_NAME;
        }

        $collection = $this->imageCollectionFactory->create();
        $collection->getSelect()
            ->where('is_active = ?', 1)
            ->where('(SELECT FIND_IN_SET(?, ' . $fieldname . ') = 1)', $ruleId);
        if ($collection->getSize() > 0) {
            $rule = $collection->getFirstItem();
            $this->promotionPageHelper->generatePromotionPage(
                $rule['imagecollection_id'],
                $rule['image1'],
                $rule['image2'],
                $rule['image3'],
                $rule['image4'],
                $rule['Name'],
                explode(',', $rule[self::CATALOG_RULE_FILED_NAME]),
                explode(',', $rule[self::CART_PRICE_RULE_FIELD_NAME]),
                $rule['type']
            );
        }
    }
}
