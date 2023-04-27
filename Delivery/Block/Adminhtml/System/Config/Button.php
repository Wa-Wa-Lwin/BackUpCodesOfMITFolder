<?php

namespace MIT\Delivery\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use MIT\Delivery\Helper\Data;

class Button extends Field
{

    protected $_buttonId;
    protected $urlBuilder;
    private $_keyword;
    private $_helper;
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        \Magento\Framework\UrlInterface $urlBuilder,
        Data $helper
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $this->urlBuilder = $urlBuilder;
        $this->_helper = $helper;
    }
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('system/config/update.phtml');
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $this->_buttonId = $element->getId();
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getSendUrl()
    {
        $idArr = explode("_", $this->_buttonId);
        if (count($idArr) == 4) {
            $this->_keyword = $this->_helper->getScopeConfig('carriers/' . $idArr[1] . '/keyword');
        }
        $queryParams = [
            'type' => $this->_keyword
        ];
        return $this->urlBuilder->getUrl('custom_delivery/index/upload', ['_current' => true, '_use_rewrite' => true, '_query' => $queryParams]);
    }
}
