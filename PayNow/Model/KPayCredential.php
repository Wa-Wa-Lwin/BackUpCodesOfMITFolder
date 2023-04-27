<?php

namespace MIT\PayNow\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\PayNow\Api\Data\KPayCredentialInterface;

class KPayCredential extends AbstractExtensibleModel implements KPayCredentialInterface
{

    /**
     * get prepay id
     * @return string
     */
    public function getPrePayId()
    {
        return $this->getData(self::PREPAY_ID);
    }

    /**
     * set prepay id
     * @param string $prepayId
     * @return $this
     */
    public function setPrePayId($prepayId)
    {
        return $this->setData(self::PREPAY_ID, $prepayId);
    }

    /**
     * get merchant code
     * @return string
     */
    public function getMerchantCode()
    {
        return $this->getData(self::MERCHANT_CODE);
    }

    /**
     * set merchant code
     * @param string $merchantCode
     * @return $this
     */
    public function setMerchantCode($merchantCode)
    {
        return $this->setData(self::MERCHANT_CODE, $merchantCode);
    }

    /**
     * get url
     * @return string
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * set url
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * get app key
     * @return string
     */
    public function getAppKey()
    {
        return $this->getData(self::APP_KEY);
    }

    /**
     * set app id
     * @param string $appKey
     * @return $this
     */
    public function setAppKey($appKey)
    {
        return $this->setData(self::APP_KEY, $appKey);
    }

    /**
     * get app id
     * @return string
     */
    public function getAppId()
    {
        return $this->getData(self::APP_ID);
    }

    /**
     * set app id
     * @param string $appId
     * @return $this
     */
    public function setAppId($appId)
    {
        return $this->setData(self::APP_ID, $appId);
    }

    /**
     * get token
     * @return string
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * set token
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }
}
