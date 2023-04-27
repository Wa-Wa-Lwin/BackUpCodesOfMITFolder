<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Report\Block\Adminhtml\Order;

class Index extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_Reports';

    /**
     * Initialize container block settings
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_order_index';
        $this->_headerText = __('Products Ordered Custom');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}

