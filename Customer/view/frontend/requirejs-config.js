var config = {
    map: {
        '*': {
            'Magento_Customer/messages/customerAlreadyExistsErrorMessage.phtml': 'MIT_Customer/messages/customerAlreadyExistsErrorMessage.phtml',
            'Magento_Customer/js/view/authentication-popup': 'MIT_Customer/js/view/authentication-popup'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'MIT_Customer/js/validation-mixin': true,
                'MIT_Customer/js/validation-tandc-mixin': true
            }
        }
    }
}