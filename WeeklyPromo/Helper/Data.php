<?php

namespace MIT\WeeklyPromo\Helper;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $categoryLinkManagementInterface;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        CategoryRepository $categoryRepository,
        CategoryFactory $categoryFactory,
        ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        CategoryLinkManagementInterface $categoryLinkManagementInterface
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryLinkManagementInterface = $categoryLinkManagementInterface;
    }

    /**
     * update product category and assign to weekly promotion category
     * @param string $skus
     */
    public function updateProductCategory($skus)
    {
        $categoryArr = $this->checkAndUpsertCategories();
        $promoCategoryId = end($categoryArr);

        $this->removeNotUsedProductFromPromotion($promoCategoryId, explode(',', $skus));
        if ($skus) {
            foreach (explode(',', $skus) as $sku) {
                $product = $this->productRepository->get($sku);
                if ($product) {
                    $categoryIds = [];
                    $categoryIds = $product->getCategoryIds();
                    $categoryIds[] = $promoCategoryId;
                    $categoryIds = array_unique($categoryIds);

                    if ($sku && count($categoryIds) > 0) {
                        $this->categoryLinkManagementInterface->assignProductToCategories($sku, $categoryIds);
                    }
                }
            }
            $this->orderProductInCategoryByPriority($promoCategoryId, explode(',', $skus));
        }
    }

    /**
     * update product position according to priority
     * @param int $promoCategoryId
     * @param array $skus
     */
    private function orderProductInCategoryByPriority($promoCategoryId, $skus)
    {
        $category = $this->categoryFactory->create()->load($promoCategoryId);
        $productArr = $category->getProductsPosition();
        foreach ($productArr as $id => $value) {
            $productSku = $this->getProductSkuById($id);
            $position = array_search($productSku, $skus);
            if ($position !== false) {
                $productArr[$id] = $position;
            }
        }
        $category->setPostedProducts($productArr);
        $category->save();
    }

    /**
     * get product sku by id
     * @param int $id
     * @return string
     */
    private function getProductSkuById($id)
    {
        $product = $this->productRepository->getById($id);
        if ($product) {
            return $product->getSku();
        }
        return '';
    }

    /**
     * remove product from category if not included in promotion
     * @param int $categoryId
     * @param array $skus
     */
    private function removeNotUsedProductFromPromotion($categoryId, $skus)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryLinkRepository = $objectManager->get('\Magento\Catalog\Model\CategoryLinkRepository');

        $existedSkus = $this->getProductSkusFromCategory($categoryId);
        $removableSkus = array_diff($existedSkus, $skus);
        foreach ($removableSkus as $sku) {
            $categoryLinkRepository->deleteByIds($categoryId, $sku);
        }
    }

    /**
     * get sku list from specific category
     * @param int $categoryId
     * @return array
     */
    private function getProductSkusFromCategory($categoryId)
    {
        $skus = [];
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('sku');
        $collection->addCategoriesFilter(['in' => [$categoryId]]);
        foreach ($collection->getItems() as $item) {
            $skus[] = $item->getSku();
        }
        return $skus;
    }

    /**
     * check and upsert categoreis
     * @param string $categories
     * @return array
     */
    private function checkAndUpsertCategories($categories = 'Default Category,Promotions,Weekly Promotions')
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('parent_id')
            ->addAttributeToSelect('name');

        $categoryIdArr = [];
        foreach (explode(',', $categories) as $customCat) {
            $isExist = false;
            foreach ($collection as $category) {
                if (end($categoryIdArr)) {
                    if (end($categoryIdArr) == $category->getParentId() && $customCat == $category->getName()) {
                        $categoryIdArr[] = $category->getId();
                        $isExist = true;
                        break;
                    }
                } else if ($customCat == $category->getName()) {
                    $categoryIdArr[] = $category->getId();
                    $isExist = true;
                    break;
                }
            }

            if (!$isExist) {
                break;
            }
        }

        if (count($categoryIdArr) != count(explode(',', $categories))) {
            $startIdx = count($categoryIdArr);
            foreach (explode(',', $categories) as $key => $value) {
                if ($key >= $startIdx) {
                    $categoryIdArr[] = $this->saveCustomCategory(end($categoryIdArr), $value);
                }
            }
        }

        return $categoryIdArr;
    }

    /**
     * save category
     * @param int $parentId
     * @param string $categoryName
     * @return int
     */
    private function saveCustomCategory($parentId, $categoryName)
    {

        if ($parentId && $categoryName) {
            $parentCategory = $this->categoryRepository->get($parentId);

            $category = $this->categoryFactory->create();

            $category->setPath($parentCategory->getPath());
            $category->setParentId($parentId);
            $category->setName(($categoryName));
            if ($categoryName == 'Promotions') {
                $category->setIsActive(false);
            } else {
                $category->setIsActive(true);
            }
            $category->setIncludeInMenu(false);
            $category->setAttributeSetId($category->getDefaultAttributeSetId());
            $category->save();
            return $category->getId();
        }
    }
}

