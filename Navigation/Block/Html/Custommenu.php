<?php

namespace MIT\Navigation\Block\Html;

use Magento\Catalog\Model\CategoryManagement;

/**
 * Html page top menu block
 *
 * @api
 * @since 100.0.2
 */
class Custommenu extends \Magento\Framework\View\Element\Template
{
    const DEFAULT_PAGER = 10;

    protected $categoryManagement;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CategoryManagement $categoryManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryManagement = $categoryManagement;
    }

    public function getMenuList()
    {
        $result = $this->categoryManagement->getTree();
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(count($result->getChildrenData()));
        return $result->getChildrenData();
    }

    public function getGridCls($dataCount)
    {
        if ($dataCount == 1) {
            return 'auto';
        } else if ($dataCount >= 2 && $dataCount <= 4) {
            return 'auto auto';
        } else if ($dataCount >= 5 && $dataCount <= 6) {
            return 'auto auto auto';
        } else if ($dataCount >= 6 && $dataCount <= 8) {
            return 'auto auto auto auto';
        } else if ($dataCount >= 9 && $dataCount <= 10) {
            return 'auto auto auto auto auto';
        } else if ($dataCount >= 11 && $dataCount <= 12) {
            return 'auto auto auto auto auto auto';
        }
    }
}
