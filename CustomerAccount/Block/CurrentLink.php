<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MIT\CustomerAccount\Block;

class CurrentLink extends \Magento\Customer\Block\Account\SortLink
{

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        $highlight = '';

        if ($this->getIsHighlighted()) {
            $highlight = ' current';
        }

        $className = strtolower($this->escapeHtml(__($this->getLabel())));

        switch ($className) {
            case 'ကျွန်ုပ်၏အကောင့်':
                $className = 'my account';
                break;
            case 'ကျွန်ုပ်၏‌အော်ဒါများ':
                $className = 'my orders';
                break;
            case 'ကျွန်ုပ်၏ စိတ်ကြိုက်စာရင်း':
                $className = 'my wish list';
                break;
            case 'ကျွန်ုပ်၏ကုန်ပစ္စည်းပေါ်တွင်ပေးထားသော မှတ်ချက်များ':
                $className = 'my product reviews';
                break;
        }


        if ($this->isCurrent()) {
            $html = '<li class="nav item current"><a href="' . $this->escapeHtml($this->getHref()) . '" class="' . $className . ' icon"';
            $html .= '<strong>'
                . $this->escapeHtml(__($this->getLabel()))
                . '</strong>';
            $html .= '</li>';
        } else {
            $html = '<li class="nav item' . $highlight . '"><a href="' . $this->escapeHtml($this->getHref()) . '" class="' . $className . ' icon"';
            $html .= $this->getTitle()
                ? ' title="' . $this->escapeHtml(__($this->getTitle())) . '"'
                : '';
            $html .= $this->getAttributesHtml() . '>';

            if ($this->getIsHighlighted()) {
                $html .= '<strong>';
            }

            $html .= $this->escapeHtml(__($this->getLabel()));

            if ($this->getIsHighlighted()) {
                $html .= '</strong>';
            }

            $html .= '</a></li>';
        }

        return $html;
    }
}
