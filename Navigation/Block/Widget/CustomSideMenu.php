<?php

namespace MIT\Navigation\Block\Widget;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryManagement;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Serialize\SerializerInterface;

class CustomSideMenu extends Template implements BlockInterface
{

    protected $_template = "widget/navigation-side-menu.phtml";
    protected $categoryManagement;
    private $membershipHelper;
    private $categoryFactory;

    /** @var SerializerInterface  */
    private $serializer;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CategoryManagement $categoryManagement,
        SerializerInterface $serializer,
        \Mageplaza\Membership\Helper\Data $membershipHelper,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryManagement = $categoryManagement;
        $this->serializer = $serializer;
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

    public function getDynamicMenuList()
    {
        $result = $this->categoryManagement->getTree();
        $menuData = $result->getChildrenData();
        $result = [];
        foreach ($menuData as $data) {
            $result[] = [
                'name' => $data->getName(), 'url' => $data->getUrl(), 'productCount' => $data->getProductCount(),
                'children' => $this->generateChildren($data)
            ];
        }

        if ($this->membershipHelper->isShowPageLinkOn()) {
            $result[] = [
                'name' => 'Membership', 'url' => $this->membershipHelper->getPageRouteUrl(), 'productCount' => 1,
                'children' => []
            ];
        }
        return $this->serializer->serialize($result);
    }

    private function generateChildren(\Magento\Catalog\Api\Data\CategoryTreeInterface $menu)
    {
        $children = [];
        if (count($menu->getChildrenData())) {
            foreach ($menu->getChildrenData() as $data) {
                $children[] = [
                    'name' => $data->getName(), 'url' => $data->getUrl(), 'productCount' => $data->getProductCount(),
                    'children' => $this->generateChildren($data)
                ];
            }
        }
        return $children;
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

