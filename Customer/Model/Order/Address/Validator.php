<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MIT\Customer\Model\Order\Address;

use Magento\Sales\Model\Order\Address;
use Magento\TestFramework\Event\Magento;

/**
 * Class Validator
 */
class Validator extends \Magento\Sales\Model\Order\Address\Validator
{

    /**
     * Validate address.
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return array
     */
    public function validate(Address $address)
    {
        $warnings = [];

        if ($this->isTelephoneRequired()) {
            $this->required['telephone'] = 'Phone Number';
        }

        if ($this->isCompanyRequired()) {
            $this->required['company'] = 'Company';
        }

        if ($this->isFaxRequired()) {
            $this->required['fax'] = 'Fax';
        }

        foreach ($this->required as $code => $label) {
            if (!$address->hasData($code)) {
                $warnings[] = sprintf('"%s" is required. Enter and try again.', $label);
            }
        }
        if (!filter_var($address->getEmail(), FILTER_VALIDATE_EMAIL) && !preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $address->getEmail())) {
            $warnings[] = 'Email or Phone Number has a wrong format';
        }
        if (!filter_var(in_array($address->getAddressType(), [Address::TYPE_BILLING, Address::TYPE_SHIPPING]))) {
            $warnings[] = 'Address type doesn\'t match required options';
        }
        return $warnings;
    }
}
