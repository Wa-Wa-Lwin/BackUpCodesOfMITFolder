<?php

namespace MIT\Customer\Block\Form;

use Magento\Framework\Message\Session;

class AccountConfirmPopup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    private $session;
 
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Session $session,
        array $data = []
    )
    {        
        $this->session = $session;
        parent::__construct($context, $data);
    }
    
    /**
     * check account confirmation pop show or not
     * @return bool
     */
    public function isAccountConfirmPopup()
    {    
        $customerEmail = $this->session->getCustomerConfirmedPopup();
        $this->session->unsCustomerConfirmedPopup();
        return $customerEmail ? true : false ;
    }
    
}