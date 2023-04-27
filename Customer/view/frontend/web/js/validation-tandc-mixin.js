define(['jquery'], function($) {
    'use strict';

    return function() {
        $.validator.addMethod(
            'validate-terms-conditions',
            function(value) {
                if (value === '' || value == null || value.length === 0) {
                    return false;
                } else {
                    return true;
                }
            },
            $.mage.__('Acceptance of Terms & Condtions is required')
        );
    }
});