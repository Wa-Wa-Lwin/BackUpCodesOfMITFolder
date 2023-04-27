<?php
namespace MIT\StoreInfoConfigurationApi\Model\Api;

use Psr\Log\LoggerInterface;

use MIT\StoreInfoConfigurationApi\Model\Api\StoreInfoConfigurationApiFactory;
use MIT\StoreInfoConfigurationApi\Api\StoreInfoConfigurationApiInterface;

class StoreInfoConfigurationApi implements StoreInfoConfigurationApiInterface 
{ 
    protected $logger;
    protected $storeInfoConfigurationApiFactory; 

    public function __construct(
        LoggerInterface $logger,
        storeInfoConfigurationApiFactory $storeInfoConfigurationApiFactory
    )
    {
        $this->logger = $logger;
        $this->storeInfoConfigurationApiFactory = $storeInfoConfigurationApiFactory;
    }
    
    /**
     * @inheritdoc
     */
    public function getStoreInfo($store_info_phone_number, $store_info_mail, $store_info_address)
    {

        $response = ['success' => false];

        if ($version_number < "1.0.0" ) {
            $response = ['success' => true, 'message' => 'Version Need to be updated.'];
            $this->logger->info('Version Need to be updated.');
        } else if ($version_number <= "1.1.2") {
            $response = ['success' => true, 'message' => 'Notification will be sent.'];
            $this->logger->info('Notification will be sent.');
        } else {
            $response = ['success' => true, 'message' => 'It is up to date.'];
            $this->logger->info('It is up to date.');
        }

        //$returnArray = json_encode($response);
        return array($response); 

    }
}

