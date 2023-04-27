<?php

namespace MIT\Customer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as Date;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;
use Magento\Integration\Model\Oauth\TokenFactory;

class CustomerHelper extends AbstractHelper
{

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var OauthHelper
     */
    private $oauthHelper;

    public function __construct(
        TokenFactory $tokenFactory,
        DateTime $dateTime,
        Date $date,
        OauthHelper $oauthHelper
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * get customerId from token
     * @return int
     */
    public function getCustomerIdByToken()
    {
        if (isset($_SERVER['HTTP_TOKEN'])) {
            $result = $this->tokenFactory->create()->loadByToken($_SERVER['HTTP_TOKEN']);
            if (!$this->checkTokenExpired($result->getCreatedAt())) {
                return $result->getCustomerId();
            }
        }
        return 0;
    }

    /**
     * check token expired or not
     * @param string $tokenCreatedAt
     * @return bool
     */
    private function checkTokenExpired($tokenCreatedAt)
    {
        return $tokenCreatedAt <= $this->dateTime->formatDate($this->date->gmtTimestamp() - $this->oauthHelper->getCustomerTokenLifetime() * 60 * 60);
    }

    /**
     * Generate random OTP code
     *
     * @return int
     */
    public function generateRandomOtpCode($keyLength)
    {
        $key = "";
        for ($x = 1; $x <= $keyLength; $x++) {
            // Set each digit
            $key .= random_int(0, 9);
        }
        return $key;
    }

    /**
     * Validate Email And Phone
     * @param string $email
     * @return bool
     */
    public function validateEmailAndPhone($email)
    {
        if (preg_match("/^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i", $email)) {
            return true;
        } elseif (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $email)) {
            return true;
        }
        return false;
    }

    /**
     * Check normalize phone number and replace 09 to +95
     *  @param string $mail
     *  @return string
     */
    public function normalizePhoneNumber($mail)
    {
        if (preg_match('/^(09|959|\+)([0-9]{7,15})$/i', $mail)) {
            if (preg_match('/^09([0-9]{7,15})$/i', $mail)) {
                $mail = substr_replace($mail, '+959', 0, 2);
            } else if (preg_match('/^959([0-9]{7,15})$/i', $mail)) {
                $mail = '+' . $mail;
            }
        }
        return $mail;
    }
}
