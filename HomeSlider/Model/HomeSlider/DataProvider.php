<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\HomeSlider\Model\HomeSlider;

use MIT\HomeSlider\Model\ResourceModel\HomeSlider\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{

    /**
     * @inheritDoc
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;


    public $_storeManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritDoc
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

            if ($temp['home_slider_image']) {
                unset($temp['home_slider_image']);
                $temp['home_slider_image'][0]['name'] = $data['home_slider_image'];
                $temp['home_slider_image'][0]['url'] = $this->getMediaUrl() . $data['home_slider_image'];
                $temp['home_slider_image'][0]['type'] = 'image/png';
                $temp['home_slider_image'][0]['size'] = '111';
            }
            if ($temp['home_slider_image_mobile']) {
                unset($temp['home_slider_image_mobile']);
                $temp['home_slider_image_mobile'][0]['name'] = $data['home_slider_image_mobile'];
                $temp['home_slider_image_mobile'][0]['url'] = $this->getMediaUrl() . $data['home_slider_image_mobile'];
                $temp['home_slider_image_mobile'][0]['type'] = 'image/png';
                $temp['home_slider_image_mobile'][0]['size'] = '111';
            }

            $this->loadedData[$model->getId()] = $temp;
            // $this->loadedData[$model->getId()] = $model->getData();
        }
        $data = $this->dataPersistor->get('mit_homeslider_homeslider');

        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('mit_homeslider_homeslider');
        }

        return $this->loadedData;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'homeslider/images/image/';
        return $mediaUrl;
    }
}
