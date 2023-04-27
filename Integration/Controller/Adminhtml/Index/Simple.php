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
use Magento\Catalog\Model\Product\Attribute\OptionManagement;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Framework\Message\ManagerInterface;
use MIT\Integration\Helper\Data;

class Simple extends Action
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

    protected $helper;

    protected $messageManager;

    protected $optionManagement;

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
        CategoryRepository $categoryRepository,
        Data $helper,
        ManagerInterface $messageManager,
        OptionManagement $optionManagement
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
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->optionManagement = $optionManagement;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {

        $sku = $this->helper->getConfig("custom_integration/simple/simple_sku");
        $name = $this->helper->getConfig("custom_integration/simple/simple_name");
        $pstock = $this->helper->getConfig("custom_integration/simple/simple_stock");
        $price = $this->helper->getConfig("custom_integration/simple/simple_price");
        $category = $this->helper->getConfig("custom_integration/simple/simple_categories");
        $specialPrice = $this->helper->getConfig("custom_integration/simple/simple_special_price");
        $fromdate = $this->helper->getConfig("custom_integration/simple/simple_from_date");
        $todate = $this->helper->getConfig("custom_integration/simple/simple_to_date");
        $attribute = $this->helper->getConfig("custom_integration/simple/simple_attribute");

        $resultRedirect = $this->resultRedirectFactory->create();

        if (isset($sku) && isset($name) && isset($pstock) && isset($price) && isset($category)) {
            try {
                $product = $this->productRepository->get($sku);

                $this->messageManager->addErrorMessage(__("Product Already Exists with SKU : " . $sku));
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $product = false;
                $product = $this->productFactory->create();
                $product->setSku($sku);
                $product->setName($name);
                $product->setAttributeSetId(4);
                $product->setPrice($price);
                $product->setStatus(1);
                $product->setVisibility(4);
                $product->setTypeId('simple');

                if (isset($specialPrice)) {
                    $product->setSpecialPrice($specialPrice);
                }
                if (isset($fromdate)) {
                    $product->setSpecialFromDate($fromdate);
                }

                if (isset($todate)) {
                    $product->setSpecialToDate($todate);
                }
                $categoryIds = $this->checkAndUpsertCategories($category);
                $product->setCategoryIds($categoryIds);
                $categoryLinks = [];
                foreach ($categoryIds as $key => $id) {
                    $categoryLinks[] = $this->categoryLinkInterfaceFactory->create()->setPosition($key)->setCategoryId($id);
                }

                $stock = $this->stockItemInterfaceFactory->create()->setQty($pstock)->setIsInStock(true);


                $productExtension = $this->productExtensionFactory->create()->setCategoryLinks($categoryLinks)->setStockItem($stock);

                $product->setExtensionAttributes($productExtension);

                if (isset($attribute)) {
                    foreach (explode(",", $attribute) as $mainAttr) {
                        $attr = explode("=", $mainAttr);
                        if (count($attr) == 2) {
                            $code = $attr[0];
                            $val = $attr[1];
                            $attrArr = $this->optionManagement->getItems($code);
                            foreach ($attrArr as $attr) {
                                if (strtolower($attr->getLabel()) == strtolower($val)) {
                                    $product->setCustomAttribute($code, $attr->getValue());
                                }
                            }
                        }
                    }
                }

                $this->productRepository->save($product);
                $this->messageManager->addSuccessMessage(__("Save Product Successfully with Sku : " . $sku));
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
