<?php

namespace MIT\Customer\Model\Quote\Address;

class Validator extends \Magento\Quote\Model\Quote\Address\Validator
{

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  \Magento\Quote\Model\Quote\Address $value
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value)
    {
        $messages = [];
        $email = $value->getEmail();
        if (
            !empty($email) && !\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class) &&
            !preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $email)
        ) {
            $messages['invalid_email_format'] = 'Invalid email or Phone Number format';
        }

        $countryId = $value->getCountryId();
        if (!empty($countryId)) {
            $country = $this->countryFactory->create();
            $country->load($countryId);
            if (!$country->getId()) {
                $messages['invalid_country_code'] = 'Invalid country code';
            }
        }

        $this->_addMessages($messages);

        return empty($messages);
    }
}
