<?php

namespace MIT\Product\Model\Api;

use DateTime;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Framework\UrlInterface;
use MIT\Product\Api\CustomerDownloadableProductInterface;
use MIT\Product\Api\Data\DownloadableProductSearchResultInterfaceFactory;
use MIT\Product\Model\CustomerDownloadableProductManagementFactory;

class CustomerDownloadableProduct implements CustomerDownloadableProductInterface
{
    /**
     * @var DownloadableProductSearchResultInterfaceFactory
     */
    private $downloadableProductSearchResultInterfaceFactory;

    /**
     * @var CustomerDownloadableProductManagementFactory
     */
    private $customerDownloadableProductManagementFactory;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     */
    private $itemCollectionFactory;

    public function __construct(
        DownloadableProductSearchResultInterfaceFactory $downloadableProductSearchResultInterfaceFactory,
        CustomerDownloadableProductManagementFactory $customerDownloadableProductManagementFactory,
        UrlInterface $urlInterface,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemCollectionFactory
    ) {
        $this->downloadableProductSearchResultInterfaceFactory = $downloadableProductSearchResultInterfaceFactory;
        $this->customerDownloadableProductManagementFactory = $customerDownloadableProductManagementFactory;
        $this->urlInterface = $urlInterface;
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getDownloadableProductList($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $purchased = $this->itemCollectionFactory->create()
            ->addFieldToSelect(['status', 'link_hash', 'number_of_downloads_bought', 'number_of_downloads_used']);

        $curPage = 0;
        if ($searchCriteria->getCurrentPage() >  0) {
            $curPage = ($searchCriteria->getCurrentPage() - 1) * $searchCriteria->getPageSize();
        }

        $purchased->getSelect()->join('downloadable_link_purchased as item', 'main_table.purchased_id = item.purchased_id', ['order_id', 'order_increment_id', 'created_at', 'updated_at', 'product_name']);
        $purchased->getSelect()
            ->where('item.customer_id = ?', $customerId)
            ->where("main_table.status not in ('" . Item::LINK_STATUS_PENDING_PAYMENT . "','" . Item::LINK_STATUS_PAYMENT_REVIEW . "')")
            ->order('main_table.item_id DESC')
            ->limit($searchCriteria->getPageSize(), $curPage);

        $downloadableList = [];
        foreach ($purchased as $_item) {
            $downloadable = $this->customerDownloadableProductManagementFactory->create();
            $downloadable->setOrderId($_item->getOrderId());
            $downloadable->setOrderIncrementId($_item->getOrderIncrementId());
            $downloadable->setDateOrdered($this->formatDate($_item->getCreatedAt()));
            $downloadable->setProductName($_item->getProductName());
            $downloadable->setDownloadLink($this->getDownloadUrl($_item));
            $downloadable->setStatus($_item->getStatus());
            $downloadable->setRemainingDownload($this->getRemainingDownloads($_item));
            $downloadableList[] = $downloadable;
        }
        $result = $this->downloadableProductSearchResultInterfaceFactory->create();
        $result->setItems($downloadableList);
        $result->setSearchCriteria($searchCriteria);
        $result->setTotalCount($this->getTotalCount($customerId));
        return $result;
    }

    /**
     * get total count
     * 
     * @param int $customerId
     * @return int
     */
    private function getTotalCount($customerId)
    {
        $purchased = $this->itemCollectionFactory->create()
            ->addFieldToSelect('status');

        $purchased->getSelect()->join('downloadable_link_purchased as item', 'main_table.purchased_id = item.purchased_id', 'order_id');
        $purchased->getSelect()
        ->where('item.customer_id = ?', $customerId)
        ->where("main_table.status not in ('" . Item::LINK_STATUS_PENDING_PAYMENT . "','" . Item::LINK_STATUS_PAYMENT_REVIEW . "')")
        ->order('main_table.item_id DESC');
        return $purchased->count();
    }

    /**
     * Return url to download link
     *
     * @param Item $item
     * @return string
     */
    public function getDownloadUrl($item)
    {
        if ($item->getStatus() == ITEM::LINK_STATUS_AVAILABLE) {
            return $this->urlInterface->getUrl('downloadable/download/link', ['id' => $item->getLinkHash(), '_secure' => true]);
        } else {
            return '';
        }
    }

    /**
     * Return number of left downloads or unlimited
     *
     * @param Item $item
     * @return \Magento\Framework\Phrase|int
     */
    public function getRemainingDownloads($item)
    {
        if ($item->getNumberOfDownloadsBought()) {
            $downloads = $item->getNumberOfDownloadsBought() - $item->getNumberOfDownloadsUsed();
            return $downloads;
        }
        return __('Unlimited');
    }

    /**
     * format date to d/m/Y format
     * @param string $date
     */
    private function formatDate($date)
    {
        if ($this->verifyDate($date)) {
            $date = new DateTime($date);
            return $date->format('d/m/Y');
        }
        return $date;
    }

    /**
     * verify date
     * @param string $date
     * @param bool $strict
     * @return bool
     */
    private function verifyDate($date, $strict = true)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        if ($strict) {
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warning_count'])) {
                return false;
            }
        }
        return $dateTime !== false;
    }
}
