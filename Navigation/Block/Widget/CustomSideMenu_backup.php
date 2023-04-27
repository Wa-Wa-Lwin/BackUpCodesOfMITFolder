<?php

namespace MIT\Navigation\Block\Widget;

use Magento\Catalog\Model\CategoryManagement;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class CustomSideMenu extends Template implements BlockInterface
{

    protected $_template = "widget/navigation-side-menu.phtml";
    protected $categoryManagement;

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
        return $result->getChildrenData();
    }

    public function getRouteUrl($menu)
    {
        if ($menu && $menu->getUrl()) {
            return $menu->getUrl();
        } else {
            return '';
        }
    }
}

