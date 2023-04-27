<?php

namespace MIT\Delivery\Controller\Adminhtml\Index;

use MIT\Delivery\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Framework\File\Csv;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\ManagerInterface;

class Upload extends Action
{
    const SHIPPING_TABLE = 'mit_delivery_customdelivery';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    protected $resultPageFactory;

    private $csvProccesor;

    private $directoryList;

    private $dataHelper;

    protected $messageManager;

    protected $_code = 'customshipping';

    protected $_type;

    const UPLOADED_FILE = [
        '0' => 'carriers/customshipping/delivery_file_upload',
        '1' => 'carriers/customshippingone/delivery_file_upload',
        '2' => 'carriers/customshippingtwo/delivery_file_upload',
        '3' => 'carriers/customshippingthree/delivery_file_upload',
        '4' => 'carriers/customshippingfour/delivery_file_upload',
    ];

    public function __construct(
        Context $context,
        Csv $csvProccesor,
        Reader $moduleReader,
        PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        \MIT\Delivery\Model\CustomDeliveryFactory $collectionFactory,
        ResourceConnection $resourceConnection,
        ResultFactory $resultRedirect,
        Data $dataHelper,
        ManagerInterface $messageManager
    ) {
        $this->resultRedirect = $resultRedirect;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->directoryList = $directoryList;
        $this->moduleReader = $moduleReader;
        $this->csvProccesor = $csvProccesor;
        $this->resultPageFactory = $resultPageFactory;
        $this->resourceConnection = $resourceConnection;
        $this->messageManager = $messageManager;
        parent::__construct($context);
        $this->_request = $context->getRequest();
    }

    /**
     * read file and import to db.
     */
    public function execute()
    {
        $this->_type = $this->_request->getParam('type');
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect->setUrl($url);

        $this->readCsv();

        return $resultRedirect;
    }

    /**
     * read csv file and import to db
     */
    public function readCsv()
    {
        $pubMediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        $fileName = $this->dataHelper->getScopeConfig(self::UPLOADED_FILE[$this->_type]);
        // $fileName = 'sample_delivery.csv';
        $ds = DIRECTORY_SEPARATOR;
        $dirTest = '/custom_delivery';

        $file = $pubMediaDir . $dirTest . $ds . $fileName;

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Your text message');
        $logger->info('directory ' . $file);

        $countData = 0;

        if (!empty($file)) {
            $csvData = $this->csvProccesor->getData($file);

            $csvDataProcessed = [];
            unset($csvData[0]);
            $csvDataProcessed = $this->csvProcessValues($csvData);
            //$logger->info('csv data count ' . $csvDataProcessed);

            $connection  = $this->resourceConnection->getConnection();
            $tableName = $connection->getTableName(self::SHIPPING_TABLE);
            $countData = $connection->insertMultiple($tableName, $csvDataProcessed);
            $this->messageManager->addSuccessMessage(__('Importing was successful!'));
        }

        $logger->info('added count ' . $countData);
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * @param $csvData
     * @param $csvDataProcessed
     * @return array
     */
    private function csvProcessValues($csvData)
    {
        $csvDataProcessed = [];

        foreach ($csvData as $csvValue) {
            $csvValueProcessed = [];
            foreach ($csvValue as $key => $value) {

                switch ($key) {
                    case 0:
                        $csvValueProcessed['region'] = is_numeric($value) ? $value : '0';
                        break;
                    case 1:
                        $csvValueProcessed['city'] = is_numeric($value) ? $value : '0';
                        break;
                    case 2:
                        $csvValueProcessed['weight'] = is_numeric($value) ? $value : '0';
                        break;
                    case 3:
                        $csvValueProcessed['items'] = is_numeric($value) ? $value : '0';
                        break;
                    case 4:
                        $csvValueProcessed['total'] = is_numeric($value) ? $value : '0';
                        break;
                    case 5:
                        $csvValueProcessed['shipping'] = is_numeric($value) ? $value : '0';
                        break;
                }
            }
            $csvValueProcessed['custom_delivery_type'] = $this->_type;
            $csvDataProcessed[] = $csvValueProcessed;
        }
        return $csvDataProcessed;
    }
}
