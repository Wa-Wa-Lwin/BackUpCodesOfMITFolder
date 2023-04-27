var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'MIT_Payatstore/js/action/set-shipping-information-mixin': true
            }
        }
    },
    "map": {
        "*": {
            "Magento_Checkout/js/model/shipping-save-processor/default": "MIT_Payatstore/js/shipping-save-processor"
        }
    }
};