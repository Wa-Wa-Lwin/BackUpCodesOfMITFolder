<?php

namespace MIT\Customer\Helper;

use Magento\Framework\App\Helper\Context;

class AttributeChecker extends \Magento\Framework\App\Helper\AbstractHelper
{
    const EMAIL_TYPE = 'email';
    const PHONE_TYPE = 'phone';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function checkEmailOrPhone($data)
    {
        if ($data) {
            if (filter_var($data, FILTER_VALIDATE_EMAIL)) {
                return self::EMAIL_TYPE;
            } else if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $data)) {
                return self::PHONE_TYPE;
            }
        }

        return '';
    }
}
