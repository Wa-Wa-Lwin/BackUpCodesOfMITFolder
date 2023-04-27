<?php

namespace MIT\Customer\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\Customer\Api\Data\CustomerAccountInterface;

class CustomerAccount extends AbstractExtensibleModel implements CustomerAccountInterface
{
    /**
     * get otp expired date
     * @return string
     */
    public function getOtpExpiredDate()
    {
        return $this->getData(self::OTP_EXPIRED_DATE);
    }

    /**
     * set otp expired date
     * @param string $date
     * @return $this
     */
    public function setOtpExpiredDate($date)
    {
        return $this->setData(self::OTP_EXPIRED_DATE, $date);
    }

    /**
     * get otp round count
     * @return int|null
     */
    public function getOtpWrongCount()
    {
        return $this->getData(self::OTP_WRONG_COUNT);
    }

    /**
     * set otp round count
     * @param int $value
     * @return $this
     */
    public function setOtpWrongCount($value)
    {
        return $this->setData(self::OTP_WRONG_COUNT, $value);
    }

    /**
     * get confirm mail send count
     * @return int|null
     */
    public function getConfirmMailSendCount()
    {
        return $this->getData(self::CONFIRM_MAIL_SEND_COUNT);
    }

    /**
     * set confirm mail send count
     * @param int $value
     * @return $this
     */
    public function setConfirmMailSendCount($value)
    {
        return $this->setData(self::CONFIRM_MAIL_SEND_COUNT, $value);
    }
}
