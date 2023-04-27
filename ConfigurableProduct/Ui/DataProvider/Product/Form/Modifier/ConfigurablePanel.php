<?php

namespace MIT\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\Sku;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel as ModifierConfigurablePanel;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;

class ConfigurablePanel extends ModifierConfigurablePanel
{
    /**
     * @var string
     */
    private static $groupContent = 'content';

    /**
     * @var int
     */
    private static $sortOrder = 30;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var string
     */
    private $formName;

    /**
     * @var string
     */
    private $dataScopeName;

    /**
     * @var string
     */
    private $dataSourceName;

    /**
     * @var string
     */
    private $associatedListingPrefix;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param string $formName
     * @param string $dataScopeName
     * @param string $dataSourceName
     * @param string $associatedListingPrefix
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        $formName,
        $dataScopeName,
        $dataSourceName,
        $associatedListingPrefix = ''
    ) {
        parent::__construct($locator, $urlBuilder, $formName, $dataScopeName, $dataSourceName, $associatedListingPrefix);
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->formName = $formName;
        $this->dataScopeName = $dataScopeName;
        $this->dataSourceName = $dataSourceName;
        $this->associatedListingPrefix = $associatedListingPrefix;
    }

    /**
     * Returns Dynamic rows records configuration
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getRows()
    {
        return [
            'record' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'isTemplate' => true,
                            'is_collection' => true,
                            'component' => 'Magento_Ui/js/dynamic-rows/record',
                            'dataScope' => '',
                        ],
                    ],
                ],
                'children' => [
                    'thumbnail_image_container' => $this->getColumn(
                        'thumbnail_image',
                        __('Image'),
                        [
                            'fit' => true,
                            'formElement' => 'fileUploader',
                            'componentType' => 'fileUploader',
                            'component' => 'Magento_ConfigurableProduct/js/components/file-uploader',
                            'elementTmpl' => 'Magento_ConfigurableProduct/components/file-uploader',
                            'fileInputName' => 'image',
                            'isMultipleFiles' => false,
                            'links' => [
                                'thumbnailUrl' => '${$.provider}:${$.parentScope}.thumbnail_image',
                                'thumbnail' => '${$.provider}:${$.parentScope}.thumbnail',
                                'smallImage' => '${$.provider}:${$.parentScope}.small_image',
                                '__disableTmpl' => [
                                    'thumbnailUrl' => false,
                                    'thumbnail' => false,
                                    'smallImage' => false
                                ],
                            ],
                            'uploaderConfig' => [
                                'url' => $this->urlBuilder->getUrl(
                                    'catalog/product_gallery/upload'
                                ),
                            ],
                            'dataScope' => 'image',
                        ],
                        [
                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                            'fit' => true,
                            'sortOrder' => 0
                        ]
                    ),
                    'name_container' => $this->getColumn(
                        'name',
                        __('Name'),
                        [],
                        ['dataScope' => 'product_link']
                    ),
                    'sku_container' => $this->getColumn(
                        'sku',
                        __('SKU'),
                        [
                            'validation' => [
                                'required-entry' => true,
                                'max_text_length' => Sku::SKU_MAX_LENGTH,
                            ],
                        ],
                        [
                            'elementTmpl' => 'Magento_ConfigurableProduct/components/cell-sku',
                        ]
                    ),
                    'sku_container_one' => $this->getColumn(
                        'my_sku',
                        __('Custom SKU'),
                        [
                            'validation' => [
                                'required-entry' => true,
                                'max_text_length' => Sku::SKU_MAX_LENGTH,
                            ],
                        ],
                        [
                            'elementTmpl' => 'Magento_ConfigurableProduct/components/cell-sku',
                        ]
                    ),
                    'price_container' => $this->getColumn(
                        'price',
                        __('Price'),
                        [
                            'imports' => [
                                'addbefore' => '${$.provider}:${$.parentScope}.price_currency',
                                '__disableTmpl' => ['addbefore' => false],
                            ],
                            'validation' => ['validate-zero-or-greater' => true]
                        ],
                        ['dataScope' => 'price_string']
                    ),
                    'quantity_container' => $this->getColumn(
                        'quantity',
                        __('Quantity'),
                        ['dataScope' => 'qty'],
                        ['dataScope' => 'qty']
                    ),
                    'price_weight' => $this->getColumn('weight', __('Weight')),
                    'status' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => 'text',
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'Magento_ConfigurableProduct/components/cell-status',
                                    'label' => __('Status'),
                                    'dataScope' => 'status',
                                ],
                            ],
                        ],
                    ],
                    'attributes' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'label' => __('Attributes'),
                                ],
                            ],
                        ],
                    ],
                    'actionsList' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'additionalClasses' => 'data-grid-actions-cell',
                                    'componentType' => 'text',
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'Magento_ConfigurableProduct/components/actions-list',
                                    'label' => __('Actions'),
                                    'fit' => true,
                                    'dataScope' => 'status',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getGrid()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'label' => __('Current Variations'),
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_ConfigurableProduct/js/components/dynamic-rows-configurable',
                        'addButton' => false,
                        'isEmpty' => true,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data',
                        'dataProviderFromGrid' => $this->associatedListingPrefix . static::ASSOCIATED_PRODUCT_LISTING,
                        'dataProviderChangeFromGrid' => 'change_product',
                        'dataProviderFromWizard' => 'variations',
                        'map' => [
                            'id' => 'entity_id',
                            'product_link' => 'product_link',
                            'name' => 'name',
                            'sku' => 'sku',
                            'my_sku' => 'my_sku',
                            'price' => 'price_number',
                            'price_string' => 'price',
                            'price_currency' => 'price_currency',
                            'qty' => 'qty',
                            'weight' => 'weight',
                            'thumbnail_image' => 'thumbnail_src',
                            'status' => 'status',
                            'attributes' => 'attributes',
                        ],
                        'links' => [
                            'insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}',
                            'insertDataFromWizard' => '${$.provider}:${$.dataProviderFromWizard}',
                            'changeDataFromGrid' => '${$.provider}:${$.dataProviderChangeFromGrid}',
                            '__disableTmpl' => [
                                'insertDataFromGrid' => false,
                                'insertDataFromWizard' => false,
                                'changeDataFromGrid' => false
                            ],
                        ],
                        'sortOrder' => 20,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'modalWithGrid' => 'ns=' . $this->formName . ', index='
                            . static::ASSOCIATED_PRODUCT_MODAL,
                        'gridWithProducts' => 'ns=' . $this->associatedListingPrefix
                            . static::ASSOCIATED_PRODUCT_LISTING
                            . ', index=' . static::ASSOCIATED_PRODUCT_LISTING,
                    ],
                ],
            ],
            'children' => $this->getRows(),
        ];
    }
}
