<?php

namespace MIT\Customer\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class UrlHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManagerInterface
    )
    {
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($context);
    }

    /**
     * get term and conditon url
     * @return string
     */
    public function getTermAndConditonUrl()
    {
        return $this->storeManagerInterface->getStore()->getBaseUrl() . 'term-and-condition';
    }
}
