/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function($, wrapper, quote) {
    'use strict';

    return function(setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function(originalAction) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            // var attribute = shippingAddress.customAttributes.find(
            //     function(element) {
            //         return element.attribute_code === 'pay_at_store_id';
            //     }
            // );
            var customvar = $('[name="custom_attributes[pay_at_store_id]"]').val();

            shippingAddress['extension_attributes']['pay_at_store_id'] = customvar;
            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction();
        });
    };
});