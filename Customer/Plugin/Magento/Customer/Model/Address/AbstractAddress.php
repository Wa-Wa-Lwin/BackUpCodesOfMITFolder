<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Customer\Plugin\Magento\Customer\Model\Address;

class AbstractAddress
{
    public function afterValidate(
        \Magento\Customer\Model\Address\AbstractAddress $subject,
        $result
    ) {
        if (is_array($result) && count($result) == 1) {
            if ($result[0] == '"lastname" is required. Enter and try again.') {
                return true;
            }
        }
        return $result;
    }
}