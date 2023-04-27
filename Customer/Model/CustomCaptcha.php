<?php

namespace MIT\Customer\Model;


use MIT\Customer\Api\Data\CustomCaptchaInterface;
use \Payment\Kbz\Helper\Data as Helper;

class CustomCaptcha implements CustomCaptchaInterface
{

    protected $_helper;
    protected $_url;
    protected $_captcha;
    protected $_captchatype;

    const G_CAPTCHA_CONFIG = [
        'login' => 'recaptcha_frontend/type_for/customer_login',
        'signup' => 'recaptcha_frontend/type_for/customer_create',
        'edit' => 'recaptcha_frontend/type_for/customer_edit',
        'forgotpassword' => 'recaptcha_frontend/type_for/customer_forgot_password',
        'contactus' => 'recaptcha_frontend/type_for/contact',
        'review' => 'recaptcha_frontend/type_for/product_review',
        'newsletter' => 'recaptcha_frontend/type_for/newsletter',
        'sendtofriend' => 'recaptcha_frontend/type_for/sendfriend',
        'checkout' => 'recaptcha_frontend/type_for/place_order'
    ];

    public function __construct(
        Helper $helper,
        $url = '',
        $captcha = False,
        $captchatype = ''
    ) {
        $this->_helper = $helper;
        $this->_url = $url;
        $this->_captcha = $captcha;
        $this->_captchatype = $captchatype;
    }

    public function setPathUrl($path)
    {
        if (array_key_exists($path, self::G_CAPTCHA_CONFIG)) {
            $this->_url = $path;
            $result = $this->_helper->getConfig(self::G_CAPTCHA_CONFIG[$path]);
            if ($result) {
                $this->setCaptchaType($result);
                $this->setCaptcha(True);
            } else {
                $this->setCaptcha(False);
            }
        }
    }

    public function setCaptchaType($type)
    {
        $this->_captchatype = $type;
    }

    public function setCaptcha($val)
    {
        $this->_captcha = $val;
    }

    public function getCaptcha()
    {
        return $this->_captcha;
    }

    public function getCaptchaType()
    {
        return $this->_captchatype;
    }

    public function getPathUrl()
    {
        return $this->_url;
    }
}
