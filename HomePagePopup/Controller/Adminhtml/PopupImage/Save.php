<?php


namespace MIT\HomePagePopup\Controller\Adminhtml\PopupImage;

use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Message\Manager;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use MIT\HomePagePopup\Api\PopupImageRepositoryInterface;
use MIT\HomePagePopup\Api\Data\PopupImageInterface;
use MIT\HomePagePopup\Api\Data\PopupImageInterfaceFactory;
use MIT\HomePagePopup\Controller\Adminhtml\PopupImage;
use MIT\HomePagePopup\Model\Uploader;
use MIT\HomePagePopup\Model\UploaderPool;

class Save extends PopupImage
{

    /**
     * @var Manager
     */
    protected $messageManager;

    /**
     * @var PopupImageRepositoryInterface
     */
    protected $imageRepository;

    /**
     * @var PopupImageInterfaceFactory
     */
    protected $imageFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var UploaderPool
     */
    protected $uploaderPool;

    /**
     * Save constructor.
     *
     * @param Registry $registry
     * @param PopupImageRepositoryInterface $imageRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Manager $messageManager
     * @param PopupImageInterfaceFactory $imageFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param UploaderPool $uploaderPool
     * @param Context $context
     */
    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Registry $registry,
        PopupImageRepositoryInterface $imageRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Manager $messageManager,
        PopupImageInterfaceFactory $imageFactory,
        DataObjectHelper $dataObjectHelper,
        UploaderPool $uploaderPool,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($registry, $imageRepository, $resultPageFactory, $dateFilter, $context);
        $this->dataPersistor = $dataPersistor;
        $this->messageManager   = $messageManager;
        $this->imageFactory      = $imageFactory;
        $this->imageRepository   = $imageRepository;
        $this->dataObjectHelper  = $dataObjectHelper;
        $this->uploaderPool = $uploaderPool;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {

            $id = $this->getRequest()->getParam('image_id');
            if ($id) {
                $model = $this->imageRepository->getById($id);
            } else {
                unset($data['image_id']);
                $model = $this->imageFactory->create();
            }

            try {

                $image1 = $this->getUploader('image')->uploadFileAndGetName('image', $data);
                $data['image'] = $image1;

                // $this->dataObjectHelper->populateWithArray($model, $data, ImageCollectionInterface::class);

                $model->setData($data);
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the New PopupImage.'));
                $this->_getSession()->setFormData(false);
                $this->dataPersistor->clear('mit_homepagepopup_images');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['image_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the PopupImage.' . $e->getMessage()));
            }

            $this->_getSession()->setFormData($data);
            $this->dataPersistor->set('mit_homepagepopup_images', $data);
            return $resultRedirect->setPath('*/*/edit', ['image_id' => $this->getRequest()->getParam('image_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $type
     * @return Uploader
     * @throws \Exception
     */
    protected function getUploader($type)
    {
        return $this->uploaderPool->getUploader($type);
    }
}
