<?php

namespace MIT\Customer\Api\Data;

use MIT\Customer\Api\CustomerCaptchaInterface;

interface CustomCaptchaInterface
{

    const CAPTCHA = 'captcha';
    const CAPTCHA_TYPE = 'captcha_type';
    const PATH_URL = 'path_url';

    /**
     * get captcha
     * @return bool
     */
    public function getCaptcha();

    /**
     * set captcha
     * @param $val
     * @return string|null
     */
    public function setCaptcha($val);

    /**
     * get captcha type
     * @return string|null
     */
    public function getCaptchaType();

    /**
     * set captcha type
     * @param $type
     * @return string|null
     */
    public function setCaptchaType($type);

    /**
     * get path url
     * @return string|null
     */
    public function getPathUrl();

    /**
     * set path url
     * @param $path
     * @return string|null
     */
    public function setPathUrl($path);
}
