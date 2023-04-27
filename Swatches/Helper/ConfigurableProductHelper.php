<?php

namespace MIT\Swatches\Helper;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ResourceConfigurable;

class ConfigurableProductHelper extends AbstractHelper {

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistryInterface;

    /**
     * @var ResourceConfigurable
     */
    private $resourceConfigurable;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepositoryInterface;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(
        Configurable $configurable,
        StockRegistryInterface $stockRegistryInterface,
        ResourceConfigurable $resourceConfigurable,
        ProductAttributeRepositoryInterface $productAttributeRepositoryInterface,
        ProductRepository $productRepository
    )
    {
        $this->configurable = $configurable;
        $this->stockRegistryInterface = $stockRegistryInterface;
        $this->resourceConfigurable = $resourceConfigurable;
        $this->productAttributeRepositoryInterface = $productAttributeRepositoryInterface;
        $this->productRepository = $productRepository;
    }

    /**
     * get child product list by parent id for both instock and out of stock
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getChildProductsByParent($product) {

        $collection = $this->configurable->getUsedProductCollection($product);
        $collection->setFlag('has_stock_status_filter', false);
        $collection
            ->addAttributeToSelect(['sku', 'entity_id'])
            ->addFilterByRequiredOptions()
            ->setStoreId($product->getStoreId());

        $collection->addMediaGalleryData();
        $collection->addTierPriceData();

        return $collection->getItems();
    }

    /**
     * get out of stock product list
     * @param Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getOutofStockProductIdsByParent($product) {
        $childProductArr = [];
        foreach($this->getChildProductsByParent($product) as $childProduct) {
            $stock = $this->stockRegistryInterface->getStockItem($childProduct->getId());
            if ($stock->getQty() == 0) {
                $childProductArr[] = $this->getChildAttribute($childProduct->getId(), $product);
            }
        }
        return $childProductArr;
    }


    /**
     * get child attribute data
     * @param int $childId
     * @param \Magento\Catalog\Model\Product $parentProduct
     * @return array
     */
    public function getChildAttribute($childId, $parentProduct) {
        $_attributes = $parentProduct->getTypeInstance(true)->getConfigurableAttributes($parentProduct);
        $childProduct = $this->productRepository->getById($childId);
        $attributesPair = [];
        foreach ($_attributes as $_attribute) {
            $attributeId = (int) $_attribute->getAttributeId();
            $attributeCode = $this->productAttributeRepositoryInterface->get($attributeId)->getAttributeCode();
            $attributesPair[$attributeId] = array('id' => $childProduct->getData($attributeCode), 'label' => $childProduct->getAttributeText($attributeCode),
        'products' => []);
        }
        return $attributesPair;
    }


}