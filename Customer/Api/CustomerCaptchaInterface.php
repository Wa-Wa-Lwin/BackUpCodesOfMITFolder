<?php

namespace MIT\Customer\Api;

interface CustomerCaptchaInterface
{

    /**
     * get captcha flag data
     * @param string $type
     * @return \MIT\Customer\Api\Data\CustomCaptchaInterface
     */
    public function getCaptchaFlag($type);

    /**
     * get newletters flag
     * @return bool
     */
    public function getNewsLetterFlag();

    /**
     * get allow remote shopping assistance flag
     * @return bool
     */
    public function getAllowRemoteShoppingAssistance();
}
