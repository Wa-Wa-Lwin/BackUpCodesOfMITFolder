<?php

namespace MIT\CatalogRule\Model\Rule;

use Magento\CatalogRule\Model\Rule\DataProvider as RuleDataProvider;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends RuleDataProvider
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

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
        array $meta = [],
        array $data = [],
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $collectionFactory, $dataPersistor, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Rule $rule */
        foreach ($items as $rule) {
            $rule->load($rule->getId());
            $data = $rule->getData();
            $temp = $rule->getData();
            if ($temp['home_slider_img']) {
                unset($temp['home_slider_img']);
                $temp['home_slider_img'][0]['name'] = $data['home_slider_img'];
                $temp['home_slider_img'][0]['url'] = $this->getMediaUrl() . $data['home_slider_img'];
                $temp['home_slider_img'][0]['type'] = 'image/png';
                $temp['home_slider_img'][0]['size'] = '111';
            }

            if ($temp['promo_slider_img']) {
                unset($temp['promo_slider_img']);
                $temp['promo_slider_img'][0]['name'] = $data['promo_slider_img'];
                $temp['promo_slider_img'][0]['url'] = $this->getMediaUrl() . $data['promo_slider_img'];
                $temp['promo_slider_img'][0]['type'] = 'image/png';
                $temp['promo_slider_img'][0]['size'] = '111';
            }

            if ($temp['home_slider_img_mobile']) {
                unset($temp['home_slider_img_mobile']);
                $temp['home_slider_img_mobile'][0]['name'] = $data['home_slider_img_mobile'];
                $temp['home_slider_img_mobile'][0]['url'] = $this->getMediaUrl() . $data['home_slider_img_mobile'];
                $temp['home_slider_img_mobile'][0]['type'] = 'image/png';
                $temp['home_slider_img_mobile'][0]['size'] = '111';
            }
            // $this->loadedData[$rule->getId()] = $rule->getData();
            $this->loadedData[$rule->getId()] = $temp;
        }

        $data = $this->dataPersistor->get('catalog_rule');
        if (!empty($data)) {
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->loadedData[$rule->getId()] = $rule->getData();
            $this->dataPersistor->clear('catalog_rule');
        }
        return $this->loadedData;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManagerInterface->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog_promo/';
        return $mediaUrl;
    }
}
