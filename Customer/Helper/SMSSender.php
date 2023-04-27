<?php

namespace MIT\Customer\Helper;

use Magento\Framework\App\Helper\Context;

class SMSSender extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SMS_URL = 'http://api.vmgmyanmar.com/api/SMSBrandname/SendSMS';
    const TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c24iOiJnYW1vbmUiLCJzaWQiOiI2MDlhMDZmMy0wNGUxLTQxODYtYmMzNC0yN2I5ZjVlOWJiOTgiLCJvYnQiOiIiLCJvYmoiOiIiLCJuYmYiOjE2NjAyOTk2OTcsImV4cCI6MTY2MDMwMzI5NywiaWF0IjoxNjYwMjk5Njk3fQ.a19lKLmT0W_QB8jW_zW9mYzQeoQzs0zbuU11e7z7Tak';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }


    public function sendSMS($email, $message)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/sms.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('sender builder called');
        $logger->info($email);
        $logger->info($message);
        $params = [
            'to' => str_replace('+', '', $email),
            'type' => 1,
            'from' => 'GaMonePwint',
            'requestId' => '',
            'scheduled' => '',
            'useUnicode' => 1,
            'exit' => '',
            'message' => $message,
        ];



        $logger->info(self::SMS_URL);

        $customCurl = curl_init(self::SMS_URL);
        curl_setopt_array($customCurl, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'token: ' . self::TOKEN
            ),
            CURLOPT_POSTFIELDS => json_encode($params)
        ));

        $response = curl_exec($customCurl);

        // Check for errors
        if ($response === FALSE) {
            die(curl_error($customCurl));
        }

        // Decode the response
        $responseData = json_decode($response, TRUE);

        if ($responseData['errorCode']) {
            $logger->info($response);
        }

        // Close the cURL handler
        curl_close($customCurl);
    }


   

    // public function sendSMS($phoneNumber, $message)
    // {
    //     $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/sms.log');
    //     $logger = new \Zend_Log();
    //     $logger->addWriter($writer);
    //     $logger->info('sender builder called');
    //     $logger->info($phoneNumber);
    //     $logger->info($message);
    //     $params = [
    //         'to' => str_replace('+', '', $phoneNumber),
    //         'type' => 1,
    //         'from' => 'GaMonePwint',
    //         'requestId' => '',
    //         'scheduled' => '',
    //         'useUnicode' => 1,
    //         'exit' => '',
    //         'message' => $message,
    //     ];



    //     $logger->info(self::SMS_URL);

    //     $customCurl = curl_init(self::SMS_URL);
    //     curl_setopt_array($customCurl, array(
    //         CURLOPT_POST => TRUE,
    //         CURLOPT_RETURNTRANSFER => TRUE,
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: application/json',
    //             'token: ' . self::TOKEN
    //         ),
    //         CURLOPT_POSTFIELDS => json_encode($params)
    //     ));

    //     $response = curl_exec($customCurl);

    //     // Check for errors
    //     if ($response === FALSE) {
    //         die(curl_error($customCurl));
    //     }

    //     // Decode the response
    //     $responseData = json_decode($response, TRUE);

    //     if ($responseData['errorCode']) {
    //         $logger->info($response);
    //     }

    //     // Close the cURL handler
    //     curl_close($customCurl);
    // }


    public function sendSMSEmail($email, $message)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/sms.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('sender builder called');
        $logger->info($email);
        $logger->info($message);
        $params = [
            'to' => $email,
            'type' => 1,
            'from' => 'GaMonePwint',
            'requestId' => '',
            'scheduled' => '',
            'useUnicode' => 1,
            'exit' => '',
            'message' => $message,
        ];



        $logger->info(self::SMS_URL);

        $customCurl = curl_init(self::SMS_URL);
        curl_setopt_array($customCurl, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'token: ' . self::TOKEN
            ),
            CURLOPT_POSTFIELDS => json_encode($params)
        ));

        $response = curl_exec($customCurl);

        // Check for errors
        if ($response === FALSE) {
            die(curl_error($customCurl));
        }

        // Decode the response
        $responseData = json_decode($response, TRUE);

        if ($responseData['errorCode']) {
            $logger->info($response);
        }

        // Close the cURL handler
        curl_close($customCurl);
    }
}
