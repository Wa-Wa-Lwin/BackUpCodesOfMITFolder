/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['uiComponent', 'Magento_Customer/js/customer-data', 'jquery', 'mage/url'], function(Component, customerData, $, url) {
    'use strict';

    var imageData = window.checkoutConfig.imageData;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details/thumbnail'
        },
        displayArea: 'before_details',
        imageData: imageData,

        /**
         * @param {Object} item
         * @return {Array}
         */
        getImageItem: function(item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']];
            }

            return [];
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getSrc: function(item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].src;
            }

            url.setBaseUrl(BASE_URL);
            var link = url.build('shipping/index/imageinfo?item_id=' + item['item_id']);
            var result = null;
            $.ajax({
                url: link,
                success: function(data) {
                    console.log(data[item['item_id']]);
                    if (data) {
                        result = data[item['item_id']].src;
                    } else {
                        result = null;
                    }
                },
                async: false
            });
            return result;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getWidth: function(item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].width;
            }
            url.setBaseUrl(BASE_URL);
            var link = url.build('shipping/index/imageinfo?item_id=' + item['item_id']);
            var result = null;
            $.ajax({
                url: link,
                success: function(data) {
                    console.log(data[item['item_id']]);
                    if (data) {
                        result = data[item['item_id']].width;
                    } else {
                        result = null;
                    }
                },
                async: false
            });
            return result;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getHeight: function(item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].height;
            }
            url.setBaseUrl(BASE_URL);
            var link = url.build('shipping/index/imageinfo?item_id=' + item['item_id']);
            var result = null;
            $.ajax({
                url: link,
                success: function(data) {
                    console.log(data[item['item_id']]);
                    if (data) {
                        result = data[item['item_id']].height;
                    } else {
                        result = null;
                    }
                },
                async: false
            });
            return result;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getAlt: function(item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].alt;
            }
            url.setBaseUrl(BASE_URL);
            var link = url.build('shipping/index/imageinfo?item_id=' + item['item_id']);
            var result = null;
            $.ajax({
                url: link,
                success: function(data) {
                    console.log(data[item['item_id']]);
                    if (data) {
                        result = data[item['item_id']].alt;
                    } else {
                        result = null;
                    }
                },
                async: false
            });
            return result;
        }
    });
});