define(
    [
        'ko',
        'jquery',
        'uiComponent'
    ],
    function(ko, $, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'MIT_Coupon/checkout/terms-and-conditions'
            },
            isCustomerNotLoggedIn: ko.observable(window.checkoutConfig.isCustomerLoggedIn === false),
            firstTermCondTxt: ko.observable(window.checkoutConfig.storeCode !== 'mm' ? 'By clicking “Place Order”, I agree to GamonePwint\'s' :
                '“အော်ဒါတင်ရန်” ကိုနှိပ်ခြင်းဖြင့် GamonePwint ၏ '),
            secondTermCondTxt: ko.observable(window.checkoutConfig.storeCode !== 'mm' ? 'Terms & Conditions .' : 'မူဝါဒနှင့် စည်းမျဉ်းစည်းကမ်းများကို သဘောတူပါသည်။ '),
            Link: ko.observable(window.checkoutConfig.checkoutUrl.replace('checkout/#payment', 'term-and-condition')),
            Title: ko.observable('Terms & Conditons'),
        });
    }

);