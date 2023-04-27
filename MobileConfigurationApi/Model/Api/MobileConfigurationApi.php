<?php
namespace MIT\MobileConfigurationApi\Model\Api;

use Psr\Log\LoggerInterface;

use MIT\MobileConfigurationApi\Model\Api\MobileConfigurationApiFactory;
use MIT\MobileConfigurationApi\Api\MobileConfigurationApiInterface;
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

        //$response = ['status' => "1"];
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

















// namespace MIT\MobileConfigurationApi\Model\Api;

// use Psr\Log\LoggerInterface;

// use MIT\MobileConfigurationApi\Model\Api\MobileConfigurationApiFactory;
// use MIT\MobileConfigurationApi\Api\MobileConfigurationApiInterface;
// use MIT\MobileConfiguration\Helper\Data; 

// // Create an instance of Data
// $dataHelper = new Data($scopeConfig);

// // Call getNotificationVersion function
// $notificationVersion = $dataHelper->getNotificationVersion();

// // Call getForceUpdateVersion function
// $forceUpdateVersion = $dataHelper->getForceUpdateVersion();



// class MobileConfigurationApi implements MobileConfigurationApiInterface 
// { 
//     protected $logger;
//     protected $mobileConfigurationApiFactory;
//     protected $dataHelper;

//     public function __construct(
//         LoggerInterface $logger,
//         MobileConfigurationApiFactory $mobileConfigurationApiFactory,
//         Data $dataHelper
//     )
//     {
//         $this->logger = $logger;
//         $this->mobileConfigurationApiFactory = $mobileConfigurationApiFactory;
//         $this->dataHelper = $dataHelper;
//     }
    
//     /**
//      * @inheritdoc
//      */
//     public function checkMobileVersion($version_number)
//     {
//         $force_update_version = "1.1.1";

//         //$force_update_version = $this->dataHelper->getForceUpdateVersion(); 

//         $notification_version = $this->dataHelper->getNotificationVersion(); 

//         //$response = ['status' => false];

//         $response = ['status' => "1"];

//         if ($version_number < $force_update_version ) {
//             $response = ['status' => "1", 'message' => 'Version Need to be updated.'];
//             $this->logger->info('Version Need to be updated.');
//         } else if ($version_number <= $notification_version ) {
//             $response = ['status' => "2", 'message' => 'Notification will be sent.'];
//             $this->logger->info('Notification will be sent.');
//         } else {
//             $response = ['status' => "3", 'message' => 'It is up to date.'];
//             $this->logger->info('It is up to date.');
//         }

//         //$returnArray = json_encode($response);
//         return array($response); 

//     }
// } 



















// <?php
// namespace MIT\MobileConfigurationApi\Model\Api;

// use MIT\MobileConfigurationApi\Api\CustomInterface;
// use Psr\Log\LoggerInterface;

// use MIT\MobileConfigurationApi\Model\Api\MobileConfigurationApiFactory;
// use MIT\MobileConfigurationApi\Api\MobileConfigurationApiInterface;

// class MobileConfigurationApi implements MobileConfigurationApiInterface 
// { 
//     protected $logger;
//     protected $mobileConfigurationApiFactory; 

//     public function __construct(
//         LoggerInterface $logger,
//         mobileConfigurationApiFactory $mobileConfigurationApiFactory
//     )
//     {
//         $this->logger = $logger;
//         $this->mobileConfigurationApiFactory = $mobileConfigurationApiFactory;
//     }
    
//     /**
//      * @inheritdoc
//      */
//     public function checkMobileVersion($version_number)
//     {
//         $force_update_version = "1.1.1";

//         $notification_version = "2.2.2" ; 

//         $response = ['status' => false];

//         if ($version_number < $force_update_version ) {
//             $response = ['status' => true, 'message' => 'Version Need to be updated.'];
//             $this->logger->info('Version Need to be updated.');
//         } else if ($version_number <= $notification_version ) {
//             $response = ['status' => true, 'message' => 'Notification will be sent.'];
//             $this->logger->info('Notification will be sent.');
//         } else {
//             $response = ['status' => true, 'message' => 'It is up to date.'];
//             $this->logger->info('It is up to date.');
//         }

//         //$returnArray = json_encode($response);
//         return array($response); 

//     }
// } 