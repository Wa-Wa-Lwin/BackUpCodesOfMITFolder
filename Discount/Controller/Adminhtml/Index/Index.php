<?php

namespace MIT\Discount\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use MIT\Discount\Helper\DiscountHelper;

class Index extends Action
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    
    /**
     * @var DiscountHelper
     */
    private $discountHelper;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        DiscountHelper $discountHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->discountHelper = $discountHelper;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id && $id > 0) {
            try {
                return $resultJson->setData(['res' => $this->discountHelper->getLabelImageById($id)]);
            } catch(NoSuchEntityException $e) {
                return $resultJson->setData(['res' => ""]);
            }
        } else {
            return $resultJson->setData(['res' => ""]);
        }
    }
}
