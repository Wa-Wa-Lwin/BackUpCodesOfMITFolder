<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\HomeSlider\Controller\Adminhtml\HomeSlider;

use MIT\HomeSlider\Model\HomeSliderFactory;

class Edit extends \MIT\HomeSlider\Controller\Adminhtml\HomeSlider
{

    protected $resultPageFactory;

    private $homeSliderFactory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        HomeSliderFactory $homeSliderFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->homeSliderFactory = $homeSliderFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model


        $id = $this->getRequest()->getParam('homeslider_id');
        $customCollection = $this->homeSliderFactory->create();
        $model = $this->_objectManager->create(\MIT\HomeSlider\Model\HomeSlider::class);
        if ($id) {
            $customCollection = $customCollection->load($id);
        }


        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Homeslider') : __('New Homeslider'),
            $id ? __('Edit Homeslider') : __('New Homeslider')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Homesliders'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Homeslider %1', $model->getId()) : __('New Homeslider'));
        return $resultPage;
    }
}
