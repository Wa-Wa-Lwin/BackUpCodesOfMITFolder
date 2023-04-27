<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\HomeSlider\Controller\Adminhtml\HomeSlider;

use Magento\Framework\Exception\LocalizedException;
use MIT\HomeSlider\Model\Uploader;
use MIT\HomeSlider\Model\UploaderPool;
use MIT\HomeSlider\Helper\HomeSliderGenerate;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @var UploaderPool
     */
    protected $uploaderPool;

    /**
     * @var HomeSliderGenerate
     */
    protected $homeSliderGenerate;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        UploaderPool $uploaderPool,
        HomeSliderGenerate $homeSliderGenerate
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->uploaderPool = $uploaderPool;
        $this->homeSliderGenerate = $homeSliderGenerate;
        parent::__construct($context);
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
            $id = $this->getRequest()->getParam('homeslider_id');

            $model = $this->_objectManager->create(\MIT\HomeSlider\Model\HomeSlider::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Homeslider no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $home_slider_image = $this->getUploader('home_slider_image')->uploadFileAndGetName('home_slider_image', $data);
            $data['home_slider_image'] = $home_slider_image;
            $home_slider_image_mobile = $this->getUploader('home_slider_image_mobile')->uploadFileAndGetName('home_slider_image_mobile', $data);
            $data['home_slider_image_mobile'] = $home_slider_image_mobile;

            $model->setData($data);
            try {
                $model->save();
                //generate HomeSlider at block
                $this->homeSliderGenerate->homeSliderGenerate($model->getHomesliderId(), 'save', $model->getCategoryId());
                $this->messageManager->addSuccessMessage(__('You saved the Homeslider.'));
                $this->dataPersistor->clear('mit_homeslider_homeslider');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['homeslider_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Homeslider.'));
            }

            $this->dataPersistor->set('mit_homeslider_homeslider', $data);
            return $resultRedirect->setPath('*/*/edit', ['homeslider_id' => $this->getRequest()->getParam('homeslider_id')]);
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
