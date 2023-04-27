<?php

namespace MIT\Delivery\Controller\Adminhtml\Index;

use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    public function execute()
    {
        $name = 'customdelivery';
        $filepath = 'export/custom' . $name . '.csv';
        $this->directory->create('export');
        /* Open file */
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $columns = $this->getColumnHeader();
        foreach ($columns as $column) {
            $header[] = $column;
        }
        /* Write Header */
        $stream->writeCsv($header);

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'Delivery.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }

    /* Header Columns */
    public function getColumnHeader()
    {
        $headers = [
            'Region Id', 'City Id', 'Weight (and above)',
            'Total Items (and above)', 'Total Price (and above)', 'Shipping Price'
        ];
        return $headers;
    }
}
