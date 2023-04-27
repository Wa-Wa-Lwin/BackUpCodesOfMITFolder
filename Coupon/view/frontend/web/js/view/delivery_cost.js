define(
    [
        "uiComponent",
        'ko',
        'Magento_Customer/js/model/customer',
        'mage/storage',
        'jquery'
    ],
    function(
        Component,
        ko,
        customer,
        storage,
        $
    ) {
        'use strict';
        var self;
        return Component.extend({
            defaults: {
                template: 'MIT_Coupon/delivery_cost'
            },

            isCustomerLoggedIn: customer.isLoggedIn,
            message: ko.observable(""),
            myClass: ko.observable(""),
            myAjaxCall: function() {

                $(window).on('hashchange', function() {
                    storage.get(
                        'shipping/index/cost',
                        false
                    ).done(
                        function(response) {
                            /** Do your code here */
                            if (response) {
                                if (response == 'Free Delivery') {
                                    self.myClass('success');
                                } else {
                                    self.myClass('warning');
                                }
                                self.message(response);
                            } else if (response == '') {
                                self.myClass('');
                                self.message('');
                            }
                        }
                    ).fail(
                        function(response) {
                            self.message('');
                            self.myClass('');
                        }
                    );
                }).trigger('hashchange');
            },
            initialize: function() {
                self = this;
                this._super(); //you must call super on components or they will not render
                this.myAjaxCall();
            }
        });
    }
);
