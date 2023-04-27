/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/form',
    'MIT_Customer/js/action/login',
    'Magento_Customer/js/model/customer',
    'mage/validation',
    'Magento_Checkout/js/model/authentication-messages',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/url',
    'ko',
    'MIT_Coupon/js/view/provider'
], function($, Component, loginAction, customer, validation, messageContainer, fullScreenLoader,
    url, ko, socialProvider) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    /**
     * @type {{init: ko.bindingHandlers.socialButton.init}}
     */
    ko.bindingHandlers.socialButton = {
        init: function(element, valueAccessor, allBindings) {
            var config = {
                url: allBindings.get('url'),
                label: allBindings.get('label')
            };

            socialProvider(config, element);
        }
    };

    url.setBaseUrl(BASE_URL);
    var link = url.build('shipping/index/socialbtnconfig');
    $.ajax({
        url: link,
        success: function(result) {
            window.socialAuthenticationPopup = result;
        },
        async: false
    });

    return Component.extend({
        isGuestCheckoutAllowed: checkoutConfig.isGuestCheckoutAllowed,
        isCustomerLoginRequired: checkoutConfig.isCustomerLoginRequired,
        registerUrl: checkoutConfig.registerUrl,
        forgotPasswordUrl: checkoutConfig.forgotPasswordUrl,
        autocomplete: checkoutConfig.autocomplete,
        defaults: {
            template: 'MIT_Coupon/authentication'
        },

        /**
         * Is login form enabled for current customer.
         *
         * @return {Boolean}
         */
        isActive: function() {
            return !customer.isLoggedIn();
        },

        /**
         * Provide login action.
         *
         * @param {HTMLElement} loginForm
         */
        login: function(loginForm) {
            var loginData = {},
                formDataArray = $(loginForm).serializeArray();

            formDataArray.forEach(function(entry) {
                loginData[entry.name] = entry.value;
            });

            if ($(loginForm).validation() &&
                $(loginForm).validation('isValid')
            ) {
                fullScreenLoader.startLoader();
                loginAction(loginData, checkoutConfig.checkoutUrl, undefined, messageContainer).always(function() {
                    fullScreenLoader.stopLoader();
                });
            }
        },
        buttonLists: window.socialAuthenticationPopup,

        /**
         * @returns {Array}
         */
        socials: function() {
            return this.buttonLists;
        },

        /**
         * Is login form and social btn enabled for current customer.
         *
         * @return {Boolean}
         */
        isSocial: function() {
            return !customer.isLoggedIn() && (this.buttonLists && this.buttonLists.length > 0);
        },

        /**
         * dynamic css class for pop up form column
         *
         * @return {String}
         */
        customClass: function() {
            if (this.isSocial()) {
                return 'mp-7';
            } else {
                return 'mp-12';
            }
        }
    });
});