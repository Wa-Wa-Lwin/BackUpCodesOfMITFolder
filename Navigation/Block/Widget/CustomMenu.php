<?php

namespace MIT\Navigation\Block\Widget;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryManagement;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class CustomMenu extends Template implements BlockInterface
{

    protected $_template = "widget/navigation.phtml";
    protected $categoryManagement;
    private $membershipHelper;
    private $categoryFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CategoryManagement $categoryManagement,
        \Mageplaza\Membership\Helper\Data $membershipHelper,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryManagement = $categoryManagement;
        $this->membershipHelper = $membershipHelper;
        $this->categoryFactory = $categoryFactory;
    }

    public function getMenuList()
    {
        $result = $this->categoryManagement->getTree();
        $menuList = $result->getChildrenData();
        if ($this->membershipHelper->isShowPageLinkOn()) {
            $categoryMenu = $this->categoryFactory->create();
            $categoryMenu->setProductCount(1)->setName('Membership')->setChildrenData([])->setUrl($this->membershipHelper->getPageRouteUrl());
            $menuList[] = $categoryMenu;
        }
        return $menuList;
    }

    public function getGridCls($dataCount)
    {
        if ($dataCount == 1) {
            return 'auto';
        } else if ($dataCount >= 2 && $dataCount <= 4) {
            return 'auto auto';
        } else if ($dataCount >= 5) {
            return 'auto auto auto auto';
        }
    }

    public function getGridClsForChild($dataCount)
    {
        if ($dataCount == 1) {
            return 'grid-template-columns: 200px; top: 0%;';
        } else if ($dataCount > 1 && $dataCount <= 5) {
            return 'grid-template-columns: 200px; top: -70%;';
        } else if ($dataCount > 5 && $dataCount <= 10) {
            return 'grid-template-columns: 200px; top: -100%;';
        } else if ($dataCount > 10) {
            return 'grid-template-columns: 175px 175px; top: -100%;';
        }
    }
}

