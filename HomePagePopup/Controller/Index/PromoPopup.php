<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare (strict_types = 1);

namespace MIT\HomePagePopup\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use MIT\HomePagePopup\Model\ResourceModel\PopupImage\CollectionFactory;

class PromoPopup extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionFactory
     */
    private $popupCollectionFactory;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        StoreManagerInterface $storeManager,
        CollectionFactory $popupCollectionFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->popupCollectionFactory = $popupCollectionFactory;

    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $url = $this->getBaseUrl();
        $imageUrl = $url . 'homepagepopup/images/image/';

        $collection = $this->popupCollectionFactory->create()
            ->addFieldToSelect('image')
            ->addFieldToFilter('is_promotion', 1)
            ->setPageSize(1)
            ->setCurPage(1)
            ->setOrder('image_id', 'ASC');

        if ($collection->getSize() > 0) {
            $image = $collection->getFirstItem()->getImage();
            $result = $imageUrl . $image;

            return $resultJson->setData(['image' => $result]);
        }

    }

    public function getBaseUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            );
    }
}
