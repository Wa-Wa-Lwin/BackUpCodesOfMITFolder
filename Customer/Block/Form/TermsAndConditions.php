<?php

namespace MIT\Customer\Block\Form;

class TermsAndConditions extends \Magento\Framework\View\Element\Template
{
        protected $_storeManager;
        protected $_urlInterface;
 
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface,    
        array $data = []
    )
    {        
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        parent::__construct($context, $data);
    }
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    /**
     * Prining URLs using StoreManagerInterface
     */
    public function getTermsAndConditionsUrl()
    {    
        return $this->_storeManager->getStore()->getBaseUrl() . 'term-and-condition';        
           
    }
    
}