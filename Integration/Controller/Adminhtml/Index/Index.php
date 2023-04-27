<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\Integration\Controller\Adminhtml\Index;

use Exception;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory;
use Magento\Catalog\Api\SpecialPriceInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;

class Index extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $productExtensionFactory;

    protected $productRepository;

    protected $productFactory;

    protected $categoryLinkInterfaceFactory;

    protected $_categoryProcessor;

    protected $stockItemInterfaceFactory;

    private $categoryCollectionFactory;

    private $categoryFactory;

    private $categoryRepository;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        ProductRepository $productRepository,
        ProductExtensionFactory $productExtensionFactory,
        CategoryLinkInterfaceFactory $categoryLinkInterfaceFactory,
        \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor,
        StockItemInterfaceFactory $stockItemInterfaceFactory,
        SpecialPriceInterface $specialPriceInterface,
        SpecialPriceInterfaceFactory $specialPriceInterfaceFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        CategoryFactory $categoryFactory,
        CategoryRepository $categoryRepository
    ) {
        parent::__construct($context);
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->_categoryProcessor = $categoryProcessor;
        $this->categoryLinkInterfaceFactory = $categoryLinkInterfaceFactory;
        $this->stockItemInterfaceFactory = $stockItemInterfaceFactory;
        $this->specialPriceInterface = $specialPriceInterface;
        $this->specialPriceInterfaceFactory = $specialPriceInterfaceFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $productType = 'simpleeee';

        if ($productType == 'simple') {
            $resultRedirect = $this->resultRedirectFactory->create();
            $product = $this->productFactory->create();
            $product->setSku('mj429');
            $product->setName('Men Jean 29');
            $product->setAttributeSetId(11);
            $product->setPrice(35);
            $product->setStatus(1);
            $product->setVisibility(1);
            $product->setTypeId('simple');
            $product->setWeight(0.5);
            $product->setSpecialPrice(30);
            $product->setSpecialFromDate(date('Y-m-d H:i:s',  strtotime(' - 1 days')));


            $categories = 'Men,Lowest,Jeans,Pants';

            $categoryIds = $this->checkAndUpsertCategories($categories);
            $product->setCategoryIds($categoryIds);
            $categoryLinks = [];
            foreach ($categoryIds as $key => $id) {
                $categoryLinks[] = $this->categoryLinkInterfaceFactory->create()->setPosition($key)->setCategoryId($id);
            }

            $stock = $this->stockItemInterfaceFactory->create()->setQty(400)->setIsInStock(true);


            $productExtension = $this->productExtensionFactory->create()->setCategoryLinks($categoryLinks)->setStockItem($stock);

            $product->setExtensionAttributes($productExtension);
            $this->productRepository->save($product);
        } else {

            $children = [array('sku' => 1019980104, 'name' => 'aa 10009900104', 'size' => 166, 'color' => 49), array('sku' => 100088109105, 'name' => 'aa 1000018899105', 'size' => 167, 'color' => 50)];
            $savedIds = [];

            // foreach ($children as $child) {
            //     $resultRedirect = $this->resultRedirectFactory->create();
            //     $product = $this->productFactory->create();
            //     $product->setSku($child['sku']);
            //     $product->setName($child['name']);
            //     $product->setAttributeSetId(4);
            //     $product->setPrice(35);
            //     $product->setStatus(1);
            //     $product->setVisibility(1);
            //     $product->setTypeId('simple');
            //     $product->setWeight(0.5);
            //     $product->setSpecialPrice(30);
            //     $product->setSpecialFromDate(date('Y-m-d H:i:s',  strtotime(' - 1 days')));

            //     $categories = 'Men,Upper,Jeans';

            //     $categoryIds = $this->checkAndUpsertCategories($categories);
            //     $product->setCategoryIds($categoryIds);
            //     $categoryLinks = [];
            //     foreach ($categoryIds as $key => $id) {
            //         $categoryLinks[] = $this->categoryLinkInterfaceFactory->create()->setPosition($key)->setCategoryId($id);
            //     }

            //     $stock = $this->stockItemInterfaceFactory->create()->setQty(400)->setIsInStock(true);
            //     $productExtension = $this->productExtensionFactory->create()->setCategoryLinks($categoryLinks)->setStockItem($stock);
            //     $product->setCustomAttribute('color', $child['color']);
            //     $product->setCustomAttribute('size', $child['size']);
            //     $product->setCustomAttribute('my_sku', $child['sku']);

            //     $product->setExtensionAttributes($productExtension);
            //     $savedProduct = $this->productRepository->save($product);
            //     $savedIds[] = strval($savedProduct->getId());
            // }
            $savedIds = ["2235", "2236"];

            $resultRedirect = $this->resultRedirectFactory->create();

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $this->productFactory->create();
            $product->setSku('num109acd002');
            $product->setName('aa num11dve592302');
            $product->setAttributeSetId(4);
            $product->setWebsiteIds(array(1));
            // $product->setPrice(35);
            $product->setStatus(1);
            $product->setVisibility(4);
            $product->setTypeId('configurable');
            $product->setWeight(0.5);
            $product->setSpecialPrice(30);
            $product->setSpecialFromDate(date('Y-m-d H:i:s',  strtotime(' - 1 days')));
            $stock = $this->stockItemInterfaceFactory->create()->setIsInStock(true);
            $productExtension = $this->productExtensionFactory->create()->setStockItem($stock);
            $product->setExtensionAttributes($productExtension);

            $sizeAttrId = $product->getResource()->getAttribute('size')->getId();
            $colorAttrId = $product->getResource()->getAttribute('color')->getId();

            $categories = 'Men,Upper,Jeans';

            $categoryIds = $this->checkAndUpsertCategories($categories);
            $product->setCategoryIds($categoryIds);
            $categoryLinks = [];
            foreach ($categoryIds as $key => $id) {
                $categoryLinks[] = $this->categoryLinkInterfaceFactory->create()->setPosition($key)->setCategoryId($id);
            }

            $product->getTypeInstance()->setUsedProductAttributeIds(array($colorAttrId, $sizeAttrId), $product); //attribute ID of attribute 'size_general' in my store

            $configurableAttributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
            $product->setCanSaveConfigurableAttributes(true);
            $product->setConfigurableAttributesData($configurableAttributesData);
            $configurableProductsData = array();
            $product->setConfigurableProductsData($configurableProductsData);
            $productId = $this->productRepository->save($product)->getId();

            // assign simple product ids
            $associatedProductIds = $savedIds;

            try {
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId); // Load Configurable Product
                $product->setAssociatedProductIds($associatedProductIds); // Setting Associated Products
                $product->setCanSaveConfigurableAttributes(true);
                $product->save();
            } catch (Exception $e) {
                echo "<pre>";
                print_r($e->getMessage());
                exit;
            }
        }

        $url = $this->_redirect->getRefererUrl();
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }


    protected function checkAndUpsertCategories($categories)
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

    protected function saveCustomCategory($parentId, $categoryName)
    {

        if ($parentId && $categoryName) {
            $parentCategory = $this->categoryRepository->get($parentId);

            $category = $this->categoryFactory->create();

            $category->setPath($parentCategory->getPath());
            $category->setParentId($parentId);
            $category->setName(($categoryName));
            $category->setIsActive(true);
            $category->setIncludeInMenu(true);
            $category->setAttributeSetId($category->getDefaultAttributeSetId());
            $category->save();
            return $category->getId();
        }
    }
}
