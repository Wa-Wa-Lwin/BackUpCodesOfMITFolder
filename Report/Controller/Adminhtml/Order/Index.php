<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Report\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Reports\Controller\Adminhtml\Report\Product implements HttpGetActionInterface
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MIT_Report::order_index';

    /**
     * Sold Products Report Action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'MIT_Report::order_index'
        )->_addBreadcrumb(
            __('Products Ordered Custom'),
            __('Products Ordered Custom')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Ordered Products Report Custom'));
        $this->_view->renderLayout();
    }
}

