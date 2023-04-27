<?php

namespace MIT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\Product\Api\Data\FreeShippingInterface;

class FreeShipping extends AbstractExtensibleModel implements FreeShippingInterface
{
    /**
     * @inheritdoc
     */
    public function getIsFreeShipping()
    {
        return $this->getData(self::IS_FREE_SHIPPING);
    }

    /**
     * @inheritdoc
    */
    public function setIsFreeShipping($isFreeShipping)
    {
        return $this->setData(self::IS_FREE_SHIPPING,$isFreeShipping);
    }    

     /**
     * @inheritdoc
     */
    public function getFreeShippingImgPath()
    {
        return $this->getData(self::FREE_SHIPPING_IMG_URL);
    }

     /**
     * @inheritdoc
     */
    public function setFreeShippingImgPath($freeShippingImageUrl)
    {
        return $this->setData(self::FREE_SHIPPING_IMG_URL,$freeShippingImageUrl);
    }
    
}
