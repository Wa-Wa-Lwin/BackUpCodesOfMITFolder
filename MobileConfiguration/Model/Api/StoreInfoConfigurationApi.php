<?php
namespace MIT\MobileConfiguration\Model\Api;

use MIT\MobileConfiguration\Api\StoreInfoConfigurationApiInterface;
use MIT\MobileConfiguration\Helper\Data; 

class StoreInfoConfigurationApi implements StoreInfoConfigurationApiInterface 
{ 
    
    protected $dataHelper;

    public function __construct(
        Data $dataHelper
    )
    {
        $this->dataHelper = $dataHelper;
    }
    
    /**
     * @inheritdoc
    **/    
    public function getStoreInfo()
    {
        $getStoreInfoPhoneNumber = $this->dataHelper->getStoreInfoPhoneNumber(); 

        $getStoreInfoAddress = $this->dataHelper->getStoreInfoAddress(); 

        $getStoreInfoMail = $this->dataHelper->getStoreInfoMail(); 

        $response = [
            'Phone Number' => $getStoreInfoPhoneNumber,
            'Address' => $getStoreInfoAddress,
            'Mail' => $getStoreInfoMail
        ];

        return array($response);     

    }
}

