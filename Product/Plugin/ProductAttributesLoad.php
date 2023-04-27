<?php

declare(strict_types=1);

namespace MIT\Product\Plugin;

use MIT\Product\Model\SizeChartDataFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\CatalogWidget\Model\Rule;
use Magento\CatalogWidget\Model\RuleFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magepow\Sizechart\Model\SizechartFactory;
use Magepow\Sizechart\Serialize\Serializer\Json;
use MIT\Product\Api\Data\SizeChartInterface;
use  MIT\WeeklyPromo\Helper\PromoRetriever;
use MIT\Product\Api\Data\FreeShippingInterface;
use MIT\Product\Model\FreeShippingFactory;
use MIT\Product\Model\ProductAttributesFactory;

class ProductAttributesLoad
{
    /**
     * @var SizeChartDataFactory
     */
    private $sizeChartDataFactory;

    /**
     * @var ProductExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var SizechartFactory
     */
    private $sizeChartFactory;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var FilterProvider
     */
    private $filterProvider;


    /**
     * @var PromoRetriever
     */
    protected $promoRetriever;

    /**
     * @var FreeShippingFactory
    */
    protected $freeShippingFactory;

    /**
     *@var ProductAttributesFactory
    */
    protected $productAttributesFactory;

    public function __construct(
        SizeChartDataFactory $sizeChartDataFactory,
        ProductExtensionFactory $extensionFactory,
        StoreManagerInterface $storeManagerInterface,
        SizechartFactory $sizeChartFactory,
        RuleFactory $ruleFactory,
        Json $json,
        FilterProvider $filterProvider,
        PromoRetriever $promoRetriever,
        FreeShippingFactory $freeShippingFactory,
        ProductAttributesFactory $productAttributesFactory
    ) {
        $this->sizeChartDataFactory = $sizeChartDataFactory;
        $this->extensionFactory = $extensionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->sizeChartFactory = $sizeChartFactory;
        $this->ruleFactory = $ruleFactory;
        $this->json = $json;
        $this->filterProvider = $filterProvider;
        $this->promoRetriever = $promoRetriever;
        $this->freeShippingFactory = $freeShippingFactory;
        $this->productAttributesFactory = $productAttributesFactory;
    }

    /**
     * Loads product entity extension attributes
     *
     * @param ProductInterface $entity
     * @param ProductExtensionInterface|null $extension
     * @return ProductExtensionInterface
     */
    public function afterGetExtensionAttributes(
        ProductInterface $entity,
        ProductExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }
        $extension->setSizeChart($this->checkAndGetSizeChart($entity));
        $extension->setFreeShipping($this->checkAndGetImageUrl($entity));
        $extension->setProductAttributes($this->getAdditionalData($entity));
        return $extension;
    }

    /**
     * check and get Size Chart
     * @param ProductInterface $product
     * @return SizeChartInterface
     */
    private function checkAndGetSizeChart($product)
    {
        $sizeChart = $this->sizeChartDataFactory->create();
        $sizeChart->setIsSizeChart(false);
        $storeId = $this->storeManagerInterface->getStore()->getStoreId();
        $collection = $this->sizeChartFactory->create()->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('stores', array(array('finset' => 0), array('finset' => $storeId)))
            ->setOrder('sort_order', 'ASC');

        foreach ($collection as $item) {
            $config = $item->getConditionsSerialized();
            $data = $this->json->unserialize($config);
            $parameters =  $data['parameters'];
            $rule = $this->getRule($parameters);
            $validate = $rule->getConditions()->validate($product);
            if ($validate) {
                $sizeChart->setIsSizeChart(true);
                $sizeChart->setLabel($item['name']);
                $sizeChart->setDescription($item['description']);
                if (preg_match('/"([^"]+)"/', $this->filterProvider->getBlockFilter()->filter($item['sizechart_info']), $result)) {
                    $sizeChart->setImgPath($result[1]);
                }
                break;
            }
        }
        return $sizeChart;
    }

    /**
     * get Rule
     * @param mixed $conditions
     * @return Rule
     */
    protected function getRule($conditions)
    {
        $rule = $this->ruleFactory->create();
        if (is_array($conditions)) $rule->loadPost($conditions);
        return $rule;
    }

      /**
     * check and get free shipping image url
     * @param ProductInterface $product
     * @return FreeShippingInterface
     */

    private function checkAndGetImageUrl($product){

        $freeShippingFactory = $this->freeShippingFactory->create();
        $freeShippingFactory->setIsFreeShipping(false);
        $check = $this->promoRetriever->isFreeShipping([$product->getId()]);
        if($check){
            $freeShippingFactory->setIsFreeShipping(true);
            $freeShippingFactory->setFreeShippingImgPath($this->promoRetriever->getFreeShippingImgPath());
        }
        return $freeShippingFactory;
     }

     /**
     * $excludeAttr is optional array of attribute codes to exclude them from additional data array
     *
     * @param array $excludeAttr
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdditionalData($product)
    {
        $data = [];
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($this->isVisibleOnFrontend($attribute, [])) {
                $value = $attribute->getFrontend()->getValue($product);
                if (is_string($value) && strlen(trim($value))) {
                    
                    $productAttributesFactory = $this->productAttributesFactory->create();
                    $productAttributesFactory->setLabel($attribute->getFrontendLabel());
                    $productAttributesFactory->setValue($value);
                    $data[] = $productAttributesFactory;
                    
                }

            }
        }  
        return $data;  
    }

      /**
     * Determine if we should display the attribute on the front-end
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param array $excludeAttr
     * @return bool
     * @since 103.0.0
     */
    protected function isVisibleOnFrontend(
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        array $excludeAttr
    ) {
        return ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr));
    }
}
