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
            var width = this._super().initialValue;
            $('input[name="width"').val(width).change();
        },
        /**
         * On value change handler.
         *
         * @param {String} width
         */
        onUpdate: function(width) {
            if (width) {
                if (Number(width) > 0) {
                    $('.ds-main .discount-img').removeAttr("style");
                    let height = $('input[name="height"').val();
                    if (height > 0) {
                        $('.ds-main .discount-img').attr('style', 'width: ' + width + 'px !important; height: ' + height + 'px !important');
                    } else {
                        $('.ds-main .discount-img').attr('style', 'width: ' + width + 'px !important');
                    }
                } else {
                    let height = $('input[name="height"').val();
                    $('.ds-main .discount-img').removeAttr("style");
                    if (height > 0) {
                        $('.ds-main .discount-img').attr('style', 'height: ' + height + 'px !important;');
                    }
                }
            } else {
                let height = $('input[name="height"').val();
                $('.ds-main .discount-img').removeAttr("style");
                if (height > 0) {
                    $('.ds-main .discount-img').attr('style', 'height: ' + height + 'px !important;');
                }
            }
            return this._super();
        }
    });
});