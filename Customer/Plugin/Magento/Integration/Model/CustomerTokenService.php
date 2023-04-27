<?php


namespace MIT\Customer\Plugin\Magento\Integration\Model;

class CustomerTokenService
{

    public function beforeCreateCustomerAccessToken(
        \Magento\Integration\Model\CustomerTokenService $subject,
        $username,
        $password
    ) {
        if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $username)) {
            if (preg_match('/^09([0-9]{7,15})$/i', $username)) {
                $username = substr_replace($username, '+959', 0, 2);
            } else if (preg_match('/^959([0-9]{7,15})$/i', $username)) {
                $username = '+' . $username;
            }
        }
        return [$username, $password];
    }
}
