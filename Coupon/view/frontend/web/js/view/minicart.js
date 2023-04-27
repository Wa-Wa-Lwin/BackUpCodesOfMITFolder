define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function(Component, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MIT_Coupon/minicart'
        },
        initialize: function() {
            this._super();

            this.cart = customerData.get('cart');
        }
    });
});