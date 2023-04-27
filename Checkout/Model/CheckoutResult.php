<?php

namespace MIT\Checkout\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\Checkout\Api\Data\CheckoutResultInterface;

class CheckoutResult extends AbstractExtensibleModel implements CheckoutResultInterface
{

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * @inheritdoc
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }
}
