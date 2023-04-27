<?php

namespace MIT\Integration\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ButtonConfigurable extends Field
{
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('system/config/button_configurable.phtml');
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl(
            'mit_integration/index/configurable/'
        );
    }
}
