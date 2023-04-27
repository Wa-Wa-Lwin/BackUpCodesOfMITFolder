<?php

namespace MIT\PayNow\Model\Api;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\RemoteServiceUnavailableException;
use Magento\Sales\Model\OrderFactory;
use MIT\PayNow\Api\PayNowInterface;
use MIT\PayNow\Model\KPayCredentialFactory;
use \Payment\Kbz\Helper\Data as Helper;
use Payment\Kbz\Helper\RSAUtil;

class PayNow implements PayNowInterface
{
    const PAYNOW_URL = 'https://paymentgateway.mitcloud.com/api/applink';
    const API_KEY = 'AVQBiBFjhoWFlYfuN6x22CD3rUYqk2Yp';

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Payment\Kbz\Helper\Data
     */
    private $helper;

    /**
     * @var KPayCredentialFactory
     */
    private $kPayCredentialFactory;

    /**
     * @var RSAUtil
     */
    private $rSAUtil;

    public function __construct(
        OrderFactory $orderFactory,
        Helper $helper,
        KPayCredentialFactory $kPayCredentialFactory,
        RSAUtil $rSAUtil
    ) {
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->kPayCredentialFactory = $kPayCredentialFactory;
        $this->rSAUtil = $rSAUtil;
    }

    /**
     * @inheritdoc
     */
    public function getKPayCredentialForMobile($id)
    {
        if (isset($id)) {
            $order = $this->orderFactory->create()->load($id);
            $gatewayId = $this->helper->getConfig("payment/kbzpayment/gateway_id");
            $gatewayName = $this->helper->getConfig("payment/kbzpayment/gateway_name");
            if ($order->getEntityId()) {
                if ($order->getPayment()->getMethod() === 'kbzpayment' && $order->getState() == 'new' && $order->getStatus() == 'pending_payment') {
                    $sign = $this->rSAUtil->createHashedData(
                        sprintf(
                            'appname=%s&refno=%s&amount=%s&currency=%s',
                            $gatewayName,
                            $order->getIncrementId(),
                            strval($order->getGrandTotal()),
                            $order->getOrderCurrencyCode()
                        )
                    );
                    $body = [
                        'order' => array(
                            'cid' => $order->getCustomerId() ? $order->getCustomerId() : $order->getIncrementId(),
                            'cname' => $order->getCustomerName(),
                            'amount' => $order->getGrandTotal(),
                            'pname' => '',
                            'pcode' => $order->getIncrementId(),
                            'currency' => 'MMK',
                            'phone' => $order->getBillingAddress()->getTelephone(),
                            'address' => $order->getBillingAddress()->getCity(),
                            'remark' => '',
                            'appid' => $gatewayId,
                            'refno' => $order->getIncrementId(),
                            'appname' => $gatewayName,
                            'email' => $order->getBillingAddress()->getEmail(),
                            'desc' => '', 
                            'resultpageskip' => 'false',
                            'cancelurl' => '',
                            'sign' => $sign
                        ),
                        'paymentinfo' => array(
                            'payment_remark' => '',
                            'payment_method' => 'KBZQR',
                            'device_status' => 'app'
                        )
                    ];

                    $result = $this->getResponse($body);

                    if (
                        isset($result['prepay_id']) && isset($result['merch_code']) && isset($result['app_id']) &&
                        isset($result['url']) && isset($result['app_key']) && isset($result['token'])
                    ) {
                        $kpayCredential = $this->kPayCredentialFactory->create();
                        $kpayCredential->setPrePayId($result['prepay_id']);
                        $kpayCredential->setMerchantCode($result['merch_code']);
                        $kpayCredential->setAppId($result['app_id']);
                        $kpayCredential->setUrl($result['url']);
                        $kpayCredential->setAppKey($result['app_key']);
                        $kpayCredential->setToken($result['token']);
                        return $kpayCredential;
                    }
                }
                throw new NoSuchEntityException(__("Invalid Order " . $id));
            }
        }
        throw new NoSuchEntityException(__("Invalid Order " . $id));
    }

    /**
     * call paynow server and retrieve credential to open kpay app
     * @param array $body
     * @return array $responseData
     * @throws RemoteServiceUnavailableException
     */
    public function getResponse($body)
    {
        $customCurl = curl_init(self::PAYNOW_URL);
        curl_setopt_array($customCurl, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'apikey:' . self::API_KEY
            ),
            CURLOPT_POSTFIELDS => json_encode($body)
        ));

        $response = curl_exec($customCurl);

        // Check for errors
        if ($response === FALSE) {
            die(curl_error($customCurl));
        }

        $info = curl_getinfo($customCurl);

        // Decode the response
        $responseData = json_decode($response, TRUE);

        // Close the cURL handler
        curl_close($customCurl);

        if (!($info['http_code'] == 200)) {
            if (isset($responseData['serviceError']) && isset($responseData['serviceError']['error_desc'])) {
                throw new RemoteServiceUnavailableException(__($responseData['serviceError']['error_desc']));
            } else {
                throw new RemoteServiceUnavailableException(__($response));
            }
        }
        return $responseData;
    }
}
