<?php

namespace MIT\HomePagePopup\Ui\DataProvider\PopupImage\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use MIT\HomePagePopup\Model\ResourceModel\PopupImage\CollectionFactory;

class ImageData implements ModifierInterface
{
    /**
     * @var \MIT\HomePagePopup\Model\ResourceModel\PopupImage\Collection
     */
    protected $collection;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collection = $collectionFactory->create();
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        $items = $this->collection->getItems();
        /** @var $image \MIT\HomePagePopup\Model\PopupImage */
        foreach ($items as $image) {
            $_data = $image->getData();
            if (isset($_data['image'])) {
                $imageArr = [];
                $imageArr[0]['name'] = 'image';
                $imageArr[0]['url'] = $image->getImageUrl();
                $_data['image'] = $imageArr;
            }
            $image->setData($_data);
            $data[$image->getPopupImageId()] = $_data;
        }
        return $data;
    }
}
