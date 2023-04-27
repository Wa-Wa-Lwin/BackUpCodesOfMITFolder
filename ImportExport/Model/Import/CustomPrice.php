<?php

namespace MIT\ImportExport\Model\Import;

use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;

/**
 * Class CustomPrice
 */
class CustomPrice extends AbstractEntity
{
    const ENTITY_CODE = 'price_update';
    const TABLE = 'catalog_product_entity_decimal';
    const ENTITY_ID_COLUMN = 'sku';

    /**
     * If we should check column names
     */
    protected $needColumnCheck = true;

    /**
     * Need to log in import history
     */
    protected $logInHistory = true;

    /**
     * Permanent entity columns.
     */
    protected $_permanentAttributes = [
        'sku',
        'price'
    ];

    protected $_specialAttributes = [
        'sku',
        'price'
    ];

    /**
     * Valid column names
     */
    protected $validColumnNames = [
        'sku',
        'price'
    ];

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * Courses constructor.
     *
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param ProductRepository $productRepository
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        ProductRepository $productRepository
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->productRepository = $productRepository;
        $this->initMessageTemplates();
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {

        $name = $rowData['sku'] ?? '';
        $duration = (int) $rowData['price'] ?? 0;

        if (!$name) {
            $this->addRowError('SkuIsRequired', $rowNum);
        }

        if (!$duration) {
            $this->addRowError('PriceIsRequired', $rowNum);
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import data
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function _importData(): bool
    {
        if ($this->getBehavior() == Import::BEHAVIOR_REPLACE) {
            $this->saveAndReplaceEntity();
        }
        return true;
    }

    /**
     * Save and replace entities
     *
     * @return void
     */
    private function saveAndReplaceEntity()
    {

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/priceimport.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('sender builder called');

        $behavior = $this->getBehavior();
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];

            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);

                    continue;
                }

                $rowId = $row[static::ENTITY_ID_COLUMN];
                $rows[] = $rowId;
                $columnValues = [];

                foreach ($this->getAvailableColumns() as $columnKey) {
                    $columnValues[$columnKey] = $row[$columnKey];
                }

                $columnValues['sku'] = [$row['sku']];
                $columnValues['price'] = $row['price'];

                $product = $this->productRepository->get($row[static::ENTITY_ID_COLUMN]);
                if ($product->getTypeId() == 'configurable') {
                    $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                    /** @var \Magento\Catalog\Model\Product $product */
                    foreach ($childProducts as $product) {
                        $columnValues['sku'][] = $product->getSku();
                        $this->countItemsUpdated += 1;
                    }
                }


                $entityList[$rowId][] = $columnValues;
                $this->countItemsCreated += (int) !isset($row[static::ENTITY_ID_COLUMN]);
                $this->countItemsUpdated += (int) isset($row[static::ENTITY_ID_COLUMN]);
            }

            if (Import::BEHAVIOR_REPLACE === $behavior) {
                $tableName = $this->connection->getTableName(static::TABLE);
                $rows = [];
                foreach ($entityList as $entityRows) {
                    foreach ($entityRows as $row) {
                        $rows[] = $row;
                        $logger->info(json_encode($row));
                        $logger->info($row['price']);
                        $this->connection->update(
                            $tableName,
                            [
                                'value' => __($row['price']),
                            ],
                            [
                                "entity_id IN(select entity_id from catalog_product_entity where sku IN(?)) " => $row['sku'],
                                "attribute_id = (select attribute_id from eav_attribute where attribute_code = 'price')"
                            ]
                        );
                    }
                }

                $logger->info(json_encode($rows));
            }
        }
    }

    /**
     * Get available columns
     *
     * @return array
     */
    private function getAvailableColumns(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Init Error Messages
     */
    private function initMessageTemplates()
    {
        $this->addMessageTemplate(
            'SkuIsRequired',
            __('The sku cannot be empty.')
        );
        $this->addMessageTemplate(
            'PriceIsRequired',
            __('Price should be greater than 0.')
        );
    }
}
