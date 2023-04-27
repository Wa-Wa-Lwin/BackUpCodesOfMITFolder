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
            var height = this._super().initialValue;
            $('input[name="height"').val(height).change();
        },
        /**
         * On value change handler.
         *
         * @param {String} height
         */
        onUpdate: function(height) {
            if (height) {
                if (Number(height) > 0) {
                    $('.ds-main .discount-img').removeAttr("style");
                    let width = $('input[name="width"').val();
                    if (width > 0) {
                        $('.ds-main .discount-img').attr('style', 'width: ' + width + 'px !important; height: ' + height + 'px !important');
                    } else {
                        $('.ds-main .discount-img').attr('style', 'height: ' + height + 'px !important');
                    }
                } else {
                    let width = $('input[name="width"').val();
                    $('.ds-main .discount-img').removeAttr("style");
                    if (width > 0) {
                        $('.ds-main .discount-img').attr('style', 'width: ' + width + 'px !important;');
                    }
                }
            } else {
                let width = $('input[name="width"').val();
                $('.ds-main .discount-img').removeAttr("style");
                if (width > 0) {
                    $('.ds-main .discount-img').attr('style', 'width: ' + width + 'px !important;');
                }
            }
            return this._super();
        }
    });
});