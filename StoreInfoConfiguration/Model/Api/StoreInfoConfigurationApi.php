<?php
namespace MIT\StoreInfoConfiguration\Model\Api;

use Psr\Log\LoggerInterface;

use MIT\StoreInfoConfiguration\Model\Api\StoreInfoConfigurationApiFactory;
use MIT\StoreInfoConfiguration\Api\StoreInfoConfigurationApiInterface;
use MIT\StoreInfoConfiguration\Helper\Data; 

class StoreInfoConfigurationApi implements StoreInfoConfigurationApiInterface 
{ 
    protected $logger;
    protected $storeInfoConfigurationApiFactory; 
    protected $dataHelper;

    public function __construct(
        LoggerInterface $logger,
        storeInfoConfigurationApiFactory $storeInfoConfigurationApiFactory,
        Data $dataHelper
    )
    {
        $this->logger = $logger;
        $this->storeInfoConfigurationApiFactory = $storeInfoConfigurationApiFactory;
        $this->dataHelper = $dataHelper;
    }
    
    /**
     * @inheritdoc
     */
    
    public function getStoreInfo()
    {
        $getStoreInfoPhoneNumber = $this->dataHelper->getStoreInfoPhoneNumber(); 

        $getStoreInfoMail = $this->dataHelper->getStoreInfoMail(); 

        $getStoreInfoAddress = $this->dataHelper->getStoreInfoAddress(); 

        $response = [
            'Phone Number' => $getStoreInfoPhoneNumber,
            'Mail' => $getStoreInfoMail,
            'Address' => $getStoreInfoAddress
        ];

        return array($response);     

    }
}

