<?php


namespace MIT\HomePagePopup\Model\ResourceModel\PopupImage;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \MIT\HomePagePopup\Model\PopupImage::class,
            \MIT\HomePagePopup\Model\ResourceModel\PopupImage::class
        );
    }
}
