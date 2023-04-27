<?php
/**
 * A Magento 2 module named Test/Demo
 * Copyright (C) 2018  
 */

namespace MIT\Newsletter\Api;

interface NewsLetterSubscriptionInterface
{

    /**
     * POST for newsletter api
     * @param string email
     * @return boolean
     */

    public function postNewsLetter($email);


}