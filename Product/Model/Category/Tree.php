<?php

namespace MIT\Product\Model\Category;

use Magento\Catalog\Model\Category\Tree as CategoryTree;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Api\Data\CategoryTreeInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Category\TreeFactory;

class Tree extends CategoryTree
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param StoreManagerInterface $storeManager
     * @param Collection $categoryCollection
     * @param CategoryTreeInterfaceFactory $treeFactory
     * @param TreeFactory|null $treeResourceFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        StoreManagerInterface $storeManager,
        Collection $categoryCollection,
        CategoryTreeInterfaceFactory $treeFactory,
        TreeFactory $treeResourceFactory = null
    ) {
        parent::__construct($categoryTree, $storeManager, $categoryCollection, $treeFactory, $treeResourceFactory);
        $this->categoryCollection = $categoryCollection;
    }

    /**
     * Prepare category collection.
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function prepareCollection()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->categoryCollection->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'is_active'
        )->setProductStoreId(
            $storeId
        )->setLoadProductCount(
            true
        )->setStoreId(
            $storeId
        )->addAttributeToFilter(
            'include_in_menu',
            true
        )->addAttributeToFilter('is_active', true);
    }
}
