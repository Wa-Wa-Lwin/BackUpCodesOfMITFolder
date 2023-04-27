<?php

namespace MIT\Integration\Model\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\Store\Model\Store;

class CustomAddProduct
{

    // protected $_catalogData;

    // protected $_productUrl;

    // protected $_productRepository;

    // protected $_categoryProcessor;

    // protected $_storeResolver;

    // /**
    //  * @var array
    //  */
    // protected $websitesCache = [];

    // /**
    //  * @var array
    //  */
    // protected $categoriesCache = [];

    // /**
    //  * Array of supported product types as keys with appropriate model object as value.
    //  *
    //  * @var \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType[]
    //  */
    // protected $_productTypeModels = [];

    // public function __construct(
    //     \Magento\Catalog\Helper\Data $catalogData,
    //     \Magento\Catalog\Model\Product\Url $productUrl,
    //     \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    //     \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor,
    //     \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
    // ) {
    //     $this->_catalogData = $catalogData;
    //     $this->_productUrl = $productUrl;
    //     $this->_productRepository = $productRepository;
    //     $this->_categoryProcessor = $categoryProcessor;
    //     $this->_storeResolver = $storeResolver;
    // }

    // // sku	additional_attributes	name	weight	price	qty	related_skus	categories
    // // BBBBAA4553443ABCD4-WG085	has_options=1,quantity_and_stock_status=In Stock,required_options=0,my_sku=myaku	My product BBtest Testing	1.23	28000	800	24-WG087,24-WG086	Gear,Fitness Equipment


    // public function saveCustomProduct()
    // {
    //     $sku = '123';
    //     $additonal_attribute = array('has_options' => 1, 'quantity_and_stock_status' => 'In Stock', 'required_options' => 0, 'my_sku' = 'myaku');
    //     $name = 'ab Product One';
    //     $weight = 0;
    //     $price = '2800';
    //     $qty = 800;
    //     $related_skus = '24-WG087,24-WG086';
    //     $categories = 'Gear,Fitness Equipment';

    //     $entityRowsIn = [];
    //     $entityRowsUp = [];
    //     $attributes = [];
    //     $this->websitesCache = [];
    //     $this->categoriesCache = [];
    //     $tierPrices = [];
    //     $mediaGallery = [];
    //     $labelsForUpdate = [];
    //     $imagesForChangeVisibility = [];
    //     $uploadedImages = [];
    //     $previousType = null;
    //     $prevAttributeSet = null;

    //     $priceIsGlobal = $this->_catalogData->isPriceGlobal();
    //     $rowSku = $sku;
    //     $rowSkuNormalized = mb_strtolower($rowSku);
    //     $storeId = Store::DEFAULT_STORE_ID;

    //     $rowExistingImages = $existingImages[$storeId][$rowSkuNormalized] ?? [];
    //     $rowStoreMediaGalleryValues = $rowExistingImages;
    //     $rowExistingImages += $existingImages[Store::DEFAULT_STORE_ID][$rowSkuNormalized] ?? [];

    //     $entityRowsIn[strtolower($rowSku)] = [
    //         'attribute_set_id' => $this->skuProcessor->getNewSku($rowSku)['attr_set_id'],
    //         'type_id' => $this->skuProcessor->getNewSku($rowSku)['type_id'],
    //         'sku' => $rowSku,
    //         'has_options' => isset($rowData['has_options']) ? $rowData['has_options'] : 0,
    //         'created_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
    //         'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
    //     ];


    //     $websiteCodes = ['base'];
    //     $websiteId = $this->storeResolver->getWebsiteCodeToId($websiteCodes[0]);
    //     $this->websitesCache[$rowSku][$websiteId] = true;

    //     $categoryIds = $this->processRowCategories($categories, $sku);
    //     foreach ($categoryIds as $id) {
    //         $this->categoriesCache[$rowSku][$id] = true;
    //     }

    //     $productTypeModel = $this->_productTypeModels['simple'];
    //     // "name":"My product AABBtest Testing","price":"28000","weight":"1.23","color":"49","status":1,"visibility":"4","quantity_and_stock_status":1,"tax_class_id":"2","size":"91","my_sku":"myaku","url_key":"my-product-aabbtest-testing","msrp_display_actual_price_type":"0","options_container":"container2"}
    // }

    // /**
    //  * Resolve valid category ids from provided row data.
    //  *
    //  * @param array $rowData
    //  * @return array
    //  */
    // protected function processRowCategories($categories, $sku)
    // {
    //     $categoriesString = empty($categories) ? '' : $categories;
    //     $categoryIds = [];
    //     if (!empty($categoriesString)) {


    //         $categoryArr = preg_split("/\,/", $categoriesString);
    //         $result = '';
    //         $defaultCategory = 'Default Category';
    //         foreach ($categoryArr as $category) {
    //             $defaultCategory .= '/' . $category;
    //             $result .= $defaultCategory . ',';
    //         }
    //         $categoriesString = rtrim($result, ", ");



    //         $categoryIds = $this->_categoryProcessor->upsertCategories(
    //             $categoriesString,
    //             Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR
    //         );
    //     } else {
    //         $product = $this->retrieveProductBySku($sku);
    //         if ($product) {
    //             $categoryIds = $product->getCategoryIds();
    //         }
    //     }
    //     return $categoryIds;
    // }

    // /**
    //  * @param null|string $sku
    //  * @return array|null
    //  */
    // public function getNewSku($sku = null)
    // {
    //     if ($sku !== null) {
    //         $sku = strtolower($sku);
    //         return $this->newSkus[$sku] ?? null;
    //     }
    //     return $this->newSkus;
    // }

    // protected function getUrlKey($productName)
    // {
    //     return $this->_productUrl->formatUrlKey($productName);
    // }

    // /**
    //  * Retrieve product by sku.
    //  *
    //  * @param string $sku
    //  * @return \Magento\Catalog\Api\Data\ProductInterface|null
    //  */
    // private function retrieveProductBySku($sku)
    // {
    //     try {
    //         $product = $this->productRepository->get($sku);
    //     } catch (NoSuchEntityException $e) {
    //         return null;
    //     }
    //     return $product;
    // }
}
