/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'jquery'
], function(Abstract, $) {
    'use strict';

    return Abstract.extend({
        defaults: {
            cols: 15,
            rows: 2,
            elementTmpl: 'ui/form/element/textarea'
        },

        initialize: function() {
            var discountLabel = this._super().initialValue;
            // $('input[name="discount_label_style"').val(discountLabel).change();
        },

        /**
         * On value change handler.
         *
         * @param {String} discountLabelStyle
         */
        onUpdate: function(discountLabelStyle) {
            let color = $('input[name="discount_label_color"').val();
            if (discountLabelStyle) {
                console.log(discountLabelStyle);
                let style = discountLabelStyle;
                if (color) {
                    style += 'color:' + color;
                }
                $('.ds-main .discount-txt').removeAttr('style');
                $('.ds-main .discount-txt').attr('style', style);
            } else {
                $('.ds-main .discount-txt').removeAttr('style');
                if (color) {
                    $('.ds-main .discount-txt').attr('style', 'color:' + color + ';');
                }
            }
            return this._super();
        }
    });
});
