<?php

namespace MIT\Product\Api\Data;

interface CustomerDownloadableProductManagementInterface
{
    const ORDER_ID = 'order_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const ORDER_DATE = 'order_date';
    const PRODUCT_NAME = 'product_name';
    const DOWNLOAD_LINK = 'download_link';
    const STATUS = 'status';
    const REMAINING_DOWNLOAD = 'remaining_download';

    /**
     * set order id
     * @param string $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * get order id
     * @return string
     */
    public function getOrderId();

    /**
     * set order increment id
     * @param string $orderId
     * @return $this
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * get order increment id
     * @return string
     */
    public function getOrderIncrementId();

    /**
     * set ordering date
     * @param string $orderDate
     * @return $this
     */
    public function setDateOrdered($orderDate);

    /**
     * get ordering date
     * @return string
     */
    public function getDateOrdered();

    /**
     * set product name
     * @param string $productName
     * @return $this
     */
    public function setProductName($productName);

    /**
     * get product name
     * @return string
     */
    public function getProductName();

    /**
     * set product's download link
     * @param string $downloadLink
     * @return $this
     */
    public function setDownloadLink($downloadLink);

    /**
     * get product's download link
     * @return string
     */
    public function getDownloadLink();

    /**
     * set downloadable status of product
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * get downloadable status of product
     * @return string
     */
    public function getStatus();

    /**
     * set remaining download
     * @param string $remainingDownload
     * @return $this
     */
    public function setRemainingDownload($remainingDownload);

    /**
     * get remaining download
     * @return string
     */
    public function getRemainingDownload();
}
