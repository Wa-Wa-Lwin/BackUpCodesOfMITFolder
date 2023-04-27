<?php


namespace MIT\HomePagePopup\Controller\Adminhtml\PopupImage;

use MIT\HomePagePopup\Api\PopupImageRepositoryInterface;
use MIT\HomePagePopup\Controller\Adminhtml\PopupImage;
use MIT\HomePagePopup\Model\PopupImageFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Backend\App\Action\Context;

class Edit extends PopupImage
{
    private $popupImageFactory;

    public function __construct(
        Registry $registry,
        PopupImageRepositoryInterface $imageRepository,
        PopupImageFactory $popupImageFactory,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context
    ) {
        $this->coreRegistry         = $registry;
        $this->imageRepository      = $imageRepository;
        $this->resultPageFactory    = $resultPageFactory;
        $this->dateFilter = $dateFilter;
        $this->popupImageFactory = $popupImageFactory;
        parent::__construct($registry, $imageRepository, $resultPageFactory, $dateFilter, $context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $imageId = $this->getRequest()->getParam('image_id');
        $customPopupImage = $this->popupImageFactory->create();
        $model = $this->_objectManager->create(\MIT\HomePagePopup\Model\PopupImage::class);
        if ($imageId) {
            $customPopupImage = $customPopupImage->load($imageId);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MIT_HomePagePopup::popupimage')
            ->addBreadcrumb(__('PopupImages'), __('PopupImages'))
            ->addBreadcrumb(__('Manage PopupImages'), __('Manage PopupImages'));

        if ($imageId === null) {
            $resultPage->addBreadcrumb(__('New PopupImage'), __('New PopupImage'));
            $resultPage->getConfig()->getTitle()->prepend(__('New PopupImage'));
        } else {
            $resultPage->addBreadcrumb(__('Edit PopupImage'), __('Edit PopupImage'));
            $resultPage->getConfig()->getTitle()->prepend(__('Edit PopupImage'));
        }
        return $resultPage;
    }
}
