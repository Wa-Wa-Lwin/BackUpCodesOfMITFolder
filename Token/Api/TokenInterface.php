<?php

namespace MIT\Token\Api;

interface TokenInterface
{

    /**
     * get access token for integration by consumer id
     * @param string $consumerId
     * @return string
     */
    public function getAccessToken($consumerId);
}
