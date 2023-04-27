<?php

namespace MIT\HomePagePopup\Block\All;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use MIT\HomePagePopup\Model\ResourceModel\PopupImage\CollectionFactory;

class PopupImage extends \Magento\Framework\View\Element\Template
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    private $popupCollectionFactory;

    /**
     * Constructor
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     * @param StoreManagerInterface $storeManager
     * @param MIT\HomePagePopup\Model\ResourceModel\PopupImage\CollectionFactory $popupCollectionFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        StoreManagerInterface $storeManager,
        \MIT\HomePagePopup\Model\ResourceModel\PopupImage\CollectionFactory $popupCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->popupCollectionFactory = $popupCollectionFactory;
    }
    /**
     * get popup image data
     */
    public function getPopupImage()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('MIT\HomePagePopup\Model\PopupImage');
        return $model->getCollection();
    }

    /**
     * get popup image based url
     * @return null|string
     */
    public function getImageBasedURL()
    {
        $url = $this->getBaseUrl();
        return $url . 'homepagepopup/images/image/';
    }

    /**
     * get popup image sitebased url
     * @return null|string
     */
    public function getSiteBasedURL()
    {
        $url = $this->getBaseUrl();
        $trim_text = str_replace("media/", "", $url);
        return $trim_text . 'popupimages/';
    }

    /**
     * get popup image based url
     * @return null|string
     */

    public function getBaseUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            );
    }

    /**
     * get first popup image
     * @return string
     */
    public function getFirstPopupImage()
    {
        $collection = $this->popupCollectionFactory->create()
            ->addFieldToSelect('image')
            ->addFieldToFilter('is_homepage', 1)
            ->setPageSize(1)
            ->setCurPage(1)
            ->setOrder('image_id', 'ASC');

        $url = $this->getImageBasedURL();
        if ($collection->getSize() > 0) {
            $image = $collection->getFirstItem()->getImage();
            $result = $url . $image;
            return $result;
        }
    }
}
