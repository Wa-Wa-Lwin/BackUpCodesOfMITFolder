<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\HomeSlider\Controller\Adminhtml\HomeSlider;

use MIT\HomeSlider\Helper\HomeSliderGenerate;

class Delete extends \MIT\HomeSlider\Controller\Adminhtml\HomeSlider
{


    /**
     * @var HomeSliderGenerate
     */
    protected $homeSliderGenerate;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        HomeSliderGenerate $homeSliderGenerate
    ) {
        $this->homeSliderGenerate = $homeSliderGenerate;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('homeslider_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\MIT\HomeSlider\Model\HomeSlider::class);
                $model->load($id);
                $model->delete();
                // delete generate slider at block
                $this->homeSliderGenerate->homeSliderGenerate($model->getHomesliderId(), 'delete', $model->getCategoryId());
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Homeslider.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['homeslider_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Homeslider to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
