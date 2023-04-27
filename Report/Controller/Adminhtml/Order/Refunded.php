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

class Refunded extends \Magento\Reports\Controller\Adminhtml\Report\Product implements HttpGetActionInterface
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MIT_Report::order_refund';

    /**
     * Sold Products Report Action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'MIT_Report::order_refund'
        )->_addBreadcrumb(
            __('Products Refunded Custom'),
            __('Products Refunded Custom')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Ordered Refund Report Custom'));
        $this->_view->renderLayout();
    }
}

