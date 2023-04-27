<?php
namespace MIT\MobileConfiguration\Model\Api;

use Psr\Log\LoggerInterface;

use MIT\MobileConfiguration\Model\Api\MobileConfigurationApiFactory;
use MIT\MobileConfiguration\Api\MobileConfigurationApiInterface;
use MIT\MobileConfiguration\Helper\Data; 

class MobileConfigurationApi implements MobileConfigurationApiInterface 
{ 
    protected $logger;
    protected $mobileConfigurationApiFactory;
    protected $dataHelper;

    public function __construct(
        LoggerInterface $logger,
        MobileConfigurationApiFactory $mobileConfigurationApiFactory,
        Data $dataHelper
    )
    {
        $this->logger = $logger;
        $this->mobileConfigurationApiFactory = $mobileConfigurationApiFactory;
        $this->dataHelper = $dataHelper;
    }
    
    /**
     * @inheritdoc
     */
    public function checkMobileVersion($version_number)
    {
        $force_update_version = $this->dataHelper->getForceUpdateVersion(); 

        $notification_version = $this->dataHelper->getNotificationVersion(); 

        $response = ['status' => 1 ];

        if ($version_number <= $force_update_version ) {
            $response = ['status' => 1, 'message' => 'Version Need to be updated.'];
            $this->logger->info('Version Need to be updated.');
        } else if ($version_number <= $notification_version ) {
            $response = ['status' => 2, 'message' => 'Notification will be sent.'];
            $this->logger->info('Notification will be sent.');
        } else {
            $response = ['status' => 3, 'message' => 'It is up to date.'];
            $this->logger->info('It is up to date.');
        }

        return array($response); 

    }
} 









