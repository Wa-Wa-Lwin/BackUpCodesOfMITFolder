<?php

namespace MIT\Product\Model\Layer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Category as ModelLayerCategory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class Category extends ModelLayerCategory
{

    public function __construct(
        \Magento\Catalog\Model\Layer\ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data
        );
    }

    /**
     * Retrieve current layer product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = $this->collectionProvider->getCollection($this->getCurrentCategory());
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }

        if ($this->registry->registry('custom_layer_navigation_category_id') !== null) {
            $collection->getSize();
        }

        return $collection;
    }

    /**
     * Retrieve current category model
     * If no category found in registry, the root will be taken
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        $category = $this->getData('current_category');
        if ($category === null) {
            $category = $this->registry->registry('current_category');
            if ($category) {
                $this->setData('current_category', $category);
            } else {
                $customCategoryId = $this->registry->registry('custom_layer_navigation_category_id');
                if ($customCategoryId !== null) {
                    $category = $this->categoryRepository->get($customCategoryId);
                } else {
                    $category = $this->categoryRepository->get($this->getCurrentStore()->getRootCategoryId());
                }
                $this->setData('current_category', $category);
            }
        }

        return $category;
    }
}
