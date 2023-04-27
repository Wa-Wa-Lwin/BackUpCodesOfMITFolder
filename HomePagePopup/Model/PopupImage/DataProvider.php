<?php


namespace MIT\HomePagePopup\Model\PopupImage;

use Magento\Framework\App\Request\DataPersistorInterface;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use MIT\HomePagePopup\Model\ResourceModel\PopupImage\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    protected $pool;

    protected $collection;

    protected $dataPersistor;

    protected $loadedData;

    public $_storeManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection   = $collectionFactory->create();
        $this->pool         = $pool;
        //$this->meta         = $this->prepareMeta($this->meta);
        $this->dataPersistor = $dataPersistor;
        $this->_storeManager = $storeManager;
    }


    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $data = $model->getData();
            $temp = $model->getData();

            if ($temp['image']) {
                unset($temp['image']);
                $temp['image'][0]['name'] = $data['image'];
                $temp['image'][0]['url'] = $this->getMediaUrl() . $data['image'];
                $temp['image'][0]['type'] = 'image/png';
                $temp['image'][0]['size'] = '111';
            }
        $this->loadedData[$model->getId()] = $temp;
        }
        $data = $this->dataPersistor->get('mit_homepagepopup_images');

        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('mit_homepagepopup_images');
        } else {
        }

        return $this->loadedData;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'homepagepopup/images/image/';
        return $mediaUrl;
    }
}
