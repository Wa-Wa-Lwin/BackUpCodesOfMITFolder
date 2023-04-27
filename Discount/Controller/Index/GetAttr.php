<?php

namespace MIT\Discount\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;


use Magento\Framework\Exception\NoSuchEntityException;
use MIT\Discount\Helper\DynamicAttributeShowHelper;

class GetAttr extends Action
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    
    /**
     * @var DynamicAttributeShowHelper
     */
    private $dynamicAttributeShowHelper;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        DynamicAttributeShowHelper $dynamicAttributeShowHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dynamicAttributeShowHelper = $dynamicAttributeShowHelper;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id && $id > 0) {
            try {
                return $resultJson->setData(['data' => $this->dynamicAttributeShowHelper->getAdditionalData($id)]);
            } catch(NoSuchEntityException $e) {
                return $resultJson->setData(['data' => ""]);
            }
        } else {
            return $resultJson->setData(['data' => ""]);
        }
    }
}
