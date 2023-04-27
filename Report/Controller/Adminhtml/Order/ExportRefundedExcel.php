<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MIT\Report\Controller\Adminhtml\Order;

use Magento\Backend\Block\Widget\Grid\ExportInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportRefundedExcel extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MIT_Report::order_refund';

    /**
     * Export Sold Products report to XML format action
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'products_refunded.xml';
        /** @var ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
