<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MIT\Customer\DataProvider;

/**
 * @api
 * @since 100.0.2
 */
class EavValidationRules extends \Magento\Ui\DataProvider\EavValidationRules
{
    /**
     * @var array
     * @since 100.0.6
     */
    protected $validationRules = [
        'email' => ['validate-email' => true],
        'date' => ['validate-date' => true],
        'email_or_phone' => ['validate-phone-number-custom' => true]
    ];
}
