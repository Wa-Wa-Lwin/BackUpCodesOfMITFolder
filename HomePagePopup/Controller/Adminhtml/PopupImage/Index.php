<?php


namespace MIT\HomePagePopup\Controller\Adminhtml\PopupImage;
use MIT\HomePagePopup\Controller\Adminhtml\PopupImage;

class Index extends PopupImage
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MIT_HomePagePopup::popupimage');
        $resultPage->getConfig()->getTitle()->prepend(__('PopupImages'));
        $resultPage->addBreadcrumb(__('PopupImages'), __('PopupImages'));
        return $resultPage;
    }
}
