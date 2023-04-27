<?php

namespace MIT\PayNow\Api\Data;

interface KPayCredentialInterface
{
    const PREPAY_ID = 'prepay_id';
    const MERCHANT_CODE = 'merchant_id';
    const APP_ID = 'app_id';
    const URL = 'url';
    const APP_KEY = 'app_key';
    const TOKEN = 'token';

    /**
     * get prepay id
     * @return string
     */
    public function getPrePayId();

    /**
     * set prepay id
     * @param string $prepayId
     * @return $this
     */
    public function setPrePayId($prepayId);

    /**
     * get merchant code
     * @return string
     */
    public function getMerchantCode();

    /**
     * set merchant code
     * @param string $merchantCode
     * @return $this
     */
    public function setMerchantCode($merchantCode);

    /**
     * get url
     * @return string
     */
    public function getUrl();

    /**
     * set url
     * @param string $url
     * @return $this
     */
    public function setUrl($url);

    /**
     * get app key
     * @return string
     */
    public function getAppKey();

    /**
     * set app id
     * @param string $appKey
     * @return $this
     */
    public function setAppKey($appKey);

    /**
     * get app id
     * @return string
     */
    public function getAppId();

    /**
     * set app id
     * @param string $appId
     * @return $this
     */
    public function setAppId($appId);

    /**
     * get token
     * @return string
     */
    public function getToken();

    /**
     * set token
     * @param string $token
     * @return $this
     */
    public function setToken($token);
}
