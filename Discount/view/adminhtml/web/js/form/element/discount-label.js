define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function($, _, uiRegistry, abstract, modal, url) {
    'use strict';
    return abstract.extend({


        initialize: function() {
            var discountLabel = this._super().initialValue;
            $('input[name="discount_label"').val(discountLabel).change();
        },

        /**
         * On value change handler.
         *
         * @param {String} discountLabel
         */
        onUpdate: function(discountLabel) {
            if (discountLabel) {
                $('.ds-main .discount-txt').text(discountLabel);
            }
            return this._super();
        }
    });
});