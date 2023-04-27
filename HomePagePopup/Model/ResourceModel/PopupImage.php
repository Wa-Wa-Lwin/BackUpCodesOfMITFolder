<?php


namespace MIT\HomePagePopup\Model\ResourceModel;

class PopupImage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mit_homepagepopup_images', 'image_id');
    }
}
