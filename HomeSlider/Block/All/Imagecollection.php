<?php


namespace MIT\HomeSlider\Block\All;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Imagecollection extends \Magento\Framework\View\Element\Template
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
    }


    public function getImageCollections()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('MIT\HomeSlider\Model\HomeSlider');
        return $model->getCollection();
    }


    /**
     *
     * @return null|string
     */
    public function getImageBasedURL()
    {
        $url = $this->getBaseUrl();
        return  $url . 'homeslider/images/image';
    }


    /**
     *
     * @return null|string
     */
    public function getSiteBasedURL()
    {
        $url = $this->getBaseUrl();
        $trim_text = str_replace("media/", "", $url);
        return $trim_text . 'collections/';
    }

    /**
     *
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
}
