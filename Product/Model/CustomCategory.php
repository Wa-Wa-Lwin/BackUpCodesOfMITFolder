<?php

namespace MIT\Product\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use MIT\Product\Api\Data\CustomCategoryManagementInterface;

class CustomCategory extends Category implements CustomCategoryManagementInterface
{
    protected $customCategoryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryAttributeRepositoryInterface $metadataService,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTreeResource,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Framework\Filter\FilterManager $filter,
        State $flatState,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        UrlFinderInterface $urlFinder,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        CategoryRepositoryInterface $categoryRepository,
        CustomCategoryFactory $customCategoryFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $metadataService,
            $categoryTreeResource,
            $categoryTreeFactory,
            $storeCollectionFactory,
            $url,
            $productCollectionFactory,
            $catalogConfig,
            $filter,
            $flatState,
            $categoryUrlPathGenerator,
            $urlFinder,
            $indexerRegistry,
            $categoryRepository,
            $resource,
            $resourceCollection,
            $data
        );
        $this->customCategoryFactory = $customCategoryFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function setCustomChildren(?array $categoryList = null)
    {
    }

    /**
     * set catalog child data
     * @return \MIT\Product\Api\Data\CustomCategoryManagementInterface[]|[]
     */
    public function getCustomChildren()
    {
        if ($this->getCustomAttributes()) {
            $currentStore = $this->storeManager->getStore();
            $baseUrl = $currentStore->getBaseUrl();
            foreach ($this->getCustomAttributes() as $customAttribute) {
                if (in_array($customAttribute->getAttributeCode(), ['image', 'magepow_thumbnail'])) {
                    $customAttribute->setValue($baseUrl . $customAttribute->getValue());
                }
            }
        }
        $data = $this->generateChildData($this->getChildren());
        return $data;
    }

    /**
     * generate child data
     * @param string $childData
     * @return \MIT\Product\Api\Data\CustomCategoryManagementInterface[]|[]
     */
    public function generateChildData($childData)
    {
        $customChildArr = [];
        if ($childData) {
            $childArr = preg_split("/\,/", $childData);
            foreach ($childArr as $child) {
                $catalog = $this->customCategoryFactory->create()->load($child);
                if ($catalog->getIsActive() && $catalog->getIncludeInMenu()) {
                    $childData = $catalog->getChildren();
                    $this->generateChildData($childData);
                    $customChildArr[] = $catalog;
                }
            }
        }
        return $customChildArr;
    }
}

