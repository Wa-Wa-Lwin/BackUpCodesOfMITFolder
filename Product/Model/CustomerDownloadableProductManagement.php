<?php

namespace MIT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\Product\Api\Data\CustomerDownloadableProductManagementInterface;

class CustomerDownloadableProductManagement extends AbstractExtensibleModel implements CustomerDownloadableProductManagementInterface
{

    /**
     * @inheritdoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * @inheritdoc
     */
    public function getOrderIncrementId()
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setDateOrdered($orderDate)
    {
        return $this->setData(self::ORDER_DATE, $orderDate);
    }

    /**
     * @inheritdoc
     */
    public function getDateOrdered()
    {
        return $this->getData(self::ORDER_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    /**
     * @inheritdoc
     */
    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setDownloadLink($downloadLink)
    {
        return $this->setData(self::DOWNLOAD_LINK, $downloadLink);
    }

    /**
     * @inheritdoc
     */
    public function getDownloadLink()
    {
        return $this->getData(self::DOWNLOAD_LINK);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setRemainingDownload($remainingDownload)
    {
        return $this->setData(self::REMAINING_DOWNLOAD, $remainingDownload);
    }

    /**
     * @inheritdoc
     */
    public function getRemainingDownload()
    {
        return $this->getData(self::REMAINING_DOWNLOAD);
    }
}
