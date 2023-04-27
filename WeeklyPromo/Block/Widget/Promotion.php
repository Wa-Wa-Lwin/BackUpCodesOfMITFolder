<?php

namespace MIT\WeeklyPromo\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\Widget\Html\Pager;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use Magento\Widget\Helper\Conditions;
use MIT\WeeklyPromo\Helper\Data;
use MIT\WeeklyPromo\Helper\PromoRetriever;

class Promotion extends AbstractProduct implements BlockInterface, IdentityInterface
{

    protected $_template = "widget/promotion.phtml";
    const DEFAULT_WEEKLY_PROMO_URL = '/promotions/weekly-promotions.html';

    /**
     * Default value for products count that will be shown
     */
    const DEFAULT_PRODUCTS_COUNT = 10;

    /**
     * Name of request parameter for page number value
     *
     * @deprecated @see $this->getData('page_var_name')
     */
    const PAGE_VAR_NAME = 'np';

    /**
     * Default value for products per page
     */
    const DEFAULT_PRODUCTS_PER_PAGE = 5;

    /**
     * Default value whether show pager or not
     */
    const DEFAULT_SHOW_PAGER = false;

    /**
     * Instance of pager block
     *
     * @var Pager
     */
    protected $pager;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    protected $catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var SqlBuilder
     */
    protected $sqlBuilder;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var Conditions
     */
    protected $conditionsHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var EncoderInterface|null
     */
    private $urlEncoder;

    /**
     * @var RendererList
     */
    private $rendererListBlock;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var PromoRetriever
     */
    private $promoRetriever;

    /**
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param HttpContext $httpContext
     * @param SqlBuilder $sqlBuilder
     * @param Rule $rule
     * @param Conditions $conditionsHelper
     * @param array $data
     * @param Json|null $json
     * @param LayoutFactory|null $layoutFactory
     * @param EncoderInterface|null $urlEncoder
     * @param CategoryRepositoryInterface|null $categoryRepository
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        HttpContext $httpContext,
        SqlBuilder $sqlBuilder,
        Rule $rule,
        Conditions $conditionsHelper,
        Data $helper,
        PromoRetriever $promoRetriever,
        array $data = [],
        Json $json = null,
        LayoutFactory $layoutFactory = null,
        EncoderInterface $urlEncoder = null,
        CategoryRepositoryInterface $categoryRepository = null
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->httpContext = $httpContext;
        $this->sqlBuilder = $sqlBuilder;
        $this->rule = $rule;
        $this->conditionsHelper = $conditionsHelper;
        $this->helper = $helper;
        $this->promoRetriever = $promoRetriever;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
        $this->layoutFactory = $layoutFactory ?: ObjectManager::getInstance()->get(LayoutFactory::class);
        $this->urlEncoder = $urlEncoder ?: ObjectManager::getInstance()->get(EncoderInterface::class);
        $this->categoryRepository = $categoryRepository ?? ObjectManager::getInstance()
            ->get(CategoryRepositoryInterface::class);
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addColumnCountLayoutDepend('empty', 6)
            ->addColumnCountLayoutDepend('1column', 5)
            ->addColumnCountLayoutDepend('2columns-left', 4)
            ->addColumnCountLayoutDepend('2columns-right', 4)
            ->addColumnCountLayoutDepend('3columns', 3);

        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [
                Product::CACHE_TAG,
            ],
        ]);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = $priceRender->render(
            FinalPrice::PRICE_CODE,
            $product,
            $arguments
        );

        return $price;
    }

    /**
     * @inheritdoc
     */
    protected function getDetailsRendererList()
    {
        if (empty($this->rendererListBlock)) {
            /** @var $layout LayoutInterface */
            $layout = $this->layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->addHandle('catalog_widget_product_list')->load();
            $layout->generateXml();
            $layout->generateElements();

            $this->rendererListBlock = $layout->getBlock('category.product.type.widget.details.renderers');
        }
        return $this->rendererListBlock;
    }

    /**
     * Get post parameters.
     *
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($url),
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $this->setProductCollection($this->createCollection());
        return parent::_beforeToHtml();
    }

    /**
     * Prepare and return product collection
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     * @throws LocalizedException
     */
    public function createCollection()
    {
        /** @var $collection Collection */
        $collection = $this->productCollectionFactory->create();

        if ($this->getData('store_id') !== null) {
            $collection->setStoreId($this->getData('store_id'));
        }

        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        /**
         * Change sorting attribute to entity_id because created_at can be the same for products fastly created
         * one by one and sorting by created_at is indeterministic in this case.
         */
        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->addAttributeToSort('entity_id', 'desc')
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1));

        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect product attribute matches
         * several allowed values from condition simultaneously
         */
        $collection->distinct(true);

        return $collection;
    }

    /**
     * Get conditions
     *
     * @return Combine
     */
    protected function getConditions()
    {
        $skus = $this->promoRetriever->getWeeklyPromoList();
        $conditions = [];
        $conditions['1'] = array(
            "type" => "Magento\\CatalogWidget\\Model\\Rule\\Condition\\Combine",
            "aggregator" => "all",
            "value" => "1",
            "new_child" => ""
        );
        $conditions['1--1'] = array(
            "type" => "Magento\\CatalogWidget\\Model\\Rule\\Condition\\Product",
            "attribute" => "sku",
            "operator" => "()",
            "value" => $skus
        );

        $this->rule->loadPost(['conditions' => $conditions]);
        $this->helper->updateProductCategory($skus);
        return $this->rule->getConditions();
    }

    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if ($this->hasData('no_of_products')) {
            return $this->getData('no_of_products');
        }

        if (null === $this->getData('no_of_products')) {
            $this->setData('no_of_products', self::DEFAULT_PRODUCTS_COUNT);
        }

        return $this->getData('no_of_products');
    }

    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsPerPage()
    {
        if (!$this->hasData('no_of_products')) {
            $this->setData('no_of_products', self::DEFAULT_PRODUCTS_PER_PAGE);
        }
        return $this->getData('no_of_products');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if (!$this->hasData('show_pager')) {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('show_pager');
    }

    /**
     * Retrieve how many products should be displayed on page
     *
     * @return int
     */
    protected function getPageSize()
    {
        return $this->showPager() ? $this->getProductsPerPage() : $this->getProductsCount();
    }

    /**
     * Render pagination HTML
     *
     * @return string
     * @throws LocalizedException
     */
    public function getPagerHtml()
    {
        if ($this->showPager() && $this->getProductCollection()->getSize() > $this->getProductsPerPage()) {
            if (!$this->pager) {
                $this->pager = $this->getLayout()->createBlock(
                    Pager::class,
                    $this->getWidgetPagerBlockName()
                );

                $this->pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName($this->getData('page_var_name'))
                    ->setLimit($this->getProductsPerPage())
                    ->setTotalLimit($this->getProductsCount())
                    ->setCollection($this->getProductCollection());
            }
            if ($this->pager instanceof \Magento\Framework\View\Element\AbstractBlock) {
                return $this->pager->toHtml();
            }
        }
        return '';
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getProductCollection()) {
            foreach ($this->getProductCollection() as $product) {
                if ($product instanceof IdentityInterface) {
                    $identities[] = $product->getIdentities();
                }
            }
        }
        $identities = array_merge([], ...$identities);

        return $identities ?: [Product::CACHE_TAG];
    }

    /**
     * Get value of widgets' title parameter
     *
     * @return mixed|string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getDetailUrl()
    {
        return self::DEFAULT_WEEKLY_PROMO_URL;
    }

    /**
     * Get value of widgets' button title parameter
     *
     * @return mixed|string
     */
    public function getButtonTitle()
    {
        return $this->getData('button_title');
    }

    /**
     * @inheritdoc
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        $requestingPageUrl = $this->getRequest()->getParam('requesting_page_url');

        if (!empty($requestingPageUrl)) {
            $additional['useUencPlaceholder'] = true;
            $url = parent::getAddToCartUrl($product, $additional);
            return str_replace('%25uenc%25', $this->urlEncoder->encode($requestingPageUrl), $url);
        }

        return parent::getAddToCartUrl($product, $additional);
    }

    /**
     * Get widget block name
     *
     * @return string
     */
    private function getWidgetPagerBlockName()
    {
        $pageName = $this->getData('page_var_name');
        $pagerBlockName = 'widget.products.list.pager';

        if (!$pageName) {
            return $pagerBlockName;
        }

        return $pagerBlockName . '.' . $pageName;
    }
}
