<?php
namespace MIT\MobileConfig\Controller\Adminhtml;

use Magento\Backend\App\Action;

class Index extends Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('MIT_MobileConfig::mobileconfig_menu_item');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Mobile Configuration'));
        $this->_view->renderLayout();
    }
}
