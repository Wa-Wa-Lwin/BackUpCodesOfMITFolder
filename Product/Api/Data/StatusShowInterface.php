<?php

namespace MIT\Product\Api\Data;

interface StatusShowInterface
{
    const STATUS = 'status';
    const SUCCESS_MESSAGE = 'success_message';
    const ERROR_MESSAGE = 'error_message';

    /**
     * set status
     * @param bool $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * get status
     * @return bool
     */
    public function getStatus();

    /**
     * set success message
     * @param array $message
     * @return $this
     */
    public function setSuccessMessage(array $message);

    /**
     * get success message
     * @return array
     */
    public function getSuccessMessage();

    /**
     * set error message
     * @param array $message
     * @return $this
     */
    public function setErrorMessage(array $message);

    /**
     * get error message
     * @return array
     */
    public function getErrorMessage();
}
