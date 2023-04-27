<?php

namespace MIT\Checkout\Api\Data;

interface CheckoutResultInterface
{

    const ENTITY_ID = 'entity_id';
    const INCREMENT_ID = 'increment_id';
    const EMAIL = 'email';

    /**
     * set Order entity id
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * get Order entity id
     * @return int
     */
    public function getEntityId();

    /**
     * set Order increment id
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

    /**
     * get Order increment id
     * @return string
     */
    public function getIncrementId();

    /**
     * set customer email
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * get customer email
     * @return string
     */
    public function getEmail();
}
