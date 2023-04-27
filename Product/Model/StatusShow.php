<?php

namespace MIT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\Product\Api\Data\StatusShowInterface;

class StatusShow extends AbstractExtensibleModel implements StatusShowInterface
{
    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setSuccessMessage($message)
    {
        return $this->setData(self::SUCCESS_MESSAGE, $message);
    }

    /**
     * @inheritdoc
     */
    public function getSuccessMessage()
    {
        return $this->getData(self::SUCCESS_MESSAGE);
    }

    /**
     * @inheritdoc
     */
    public function setErrorMessage($message)
    {
        return $this->setData(self::ERROR_MESSAGE, $message);
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessage()
    {
        return $this->getData(self::ERROR_MESSAGE);
    }

}
