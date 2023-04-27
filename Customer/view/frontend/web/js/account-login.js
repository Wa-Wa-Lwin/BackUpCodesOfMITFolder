define([
    'jquery'
], function($) {
    'use strict';
    return function() {
        $(document).ready(function() {
            if ('.block.social-login-authentication-channel.account-social-login') {
                $('.block.social-login-authentication-channel.account-social-login').insertAfter('.block-customer-login .block-title');
                $('.block.social-login-authentication-channel.account-social-login').css('display', 'block');
            }
        });

    }

});