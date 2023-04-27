<?php

namespace MIT\Customer\Model\Api;

use MIT\Customer\Api\CustomerCaptchaInterface;
use Magento\Newsletter\Model\Config;
use Magento\LoginAsCustomer\Model\Config as CustomerModelConfig;

class CustomerCaptcha implements CustomerCaptchaInterface
{
    protected $_customCaptchaFactory;
    protected $_config;
    protected $_customerConfig;

    public function __construct(
        \MIT\Customer\Model\CustomCaptchaFactory $customCaptchaFactory,
        Config $config,
        CustomerModelConfig $customerConfig
    ) {
        $this->_customCaptchaFactory = $customCaptchaFactory;
        $this->_config = $config;
        $this->_customerConfig = $customerConfig;
    }

    /**
     * get captcha flag data
     * @param string $type
     * @return \MIT\Customer\Api\Data\CustomCaptchaInterface
     */
    public function getCaptchaFlag($type)
    {
        try {
            $model = $this->_customCaptchaFactory->create();
            $model->setPathUrl($type);

            if (!$model->getPathUrl()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('no data found')
                );
            }

            return $model;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            return json_decode(json_encode($returnArray), true);
        } catch (\Exception $e) {
            $returnArray['error'] = __('unable to process request');
            return json_decode(json_encode($returnArray), true);
        }
    }

    /**
     * get NewsLetter config
     * @return bool
     */
    public function getNewsLetterFlag()
    {
        return $this->_config->isActive();
    }

    /**
     * get AllowRemoteShoppingAssistance config
     * @return bool
     */
    public function getAllowRemoteShoppingAssistance()
    {
        return $this->_customerConfig->isEnabled();
    }
}
