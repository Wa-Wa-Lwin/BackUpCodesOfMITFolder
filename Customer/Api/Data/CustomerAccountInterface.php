<?php

namespace MIT\Customer\Api\Data;

interface CustomerAccountInterface
{

    const OTP_EXPIRED_DATE = 'otp_expired_date';
    const OTP_WRONG_COUNT = 'otp_wrong_count';
    const CONFIRM_MAIL_SEND_COUNT = 'confirm_mail_send_count';

    /**
     * get otp expired date
     * @return string
     */
    public function getOtpExpiredDate();

    /**
     * set otp expired date
     * @param $date
     * @return $this
     */
    public function setOtpExpiredDate($date);

    /**
     * get otp round count
     * @return int|null
     */
    public function getOtpWrongCount();

    /**
     * set otp round count
     * @param int $value
     * @return $this
     */
    public function setOtpWrongCount($value);

    /**
     * get confirm mail send count
     * @return int|null
     */
    public function getConfirmMailSendCount();

    /**
     * set confirm mail send count
     * @param int $value
     * @return $this
     */
    public function setConfirmMailSendCount($value);

}
