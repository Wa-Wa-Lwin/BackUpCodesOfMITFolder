<?php

namespace MIT\Discount\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\CatalogInventory\Api\StockRegistryInterface;


use Magento\Framework\Exception\NoSuchEntityException;
use MIT\Discount\Helper\DiscountHelper;

class Stock extends Action
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    
    /**
     * @var StockRegistryInterface
     */
    private $stockResitoryInterface;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        StockRegistryInterface $stockResitoryInterface
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->stockResitoryInterface = $stockResitoryInterface;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id && $id > 0) {
            try {
                return $resultJson->setData(['qty' => $this->stockResitoryInterface->getStockItem($id)->getQty()]);
            } catch(NoSuchEntityException $e) {
                return $resultJson->setData(['qty' => ""]);
            }
        } else {
            return $resultJson->setData(['qty' => ""]);
        }
    }
}
