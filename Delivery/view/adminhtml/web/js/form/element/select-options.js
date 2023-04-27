define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function ($, _, uiRegistry, select, modal, url) {
    'use strict';
    return select.extend({

        /**
         * On value change handler.
         *
         * @param {String} regionId
         */
        onUpdate: function (regionId) {
            if (regionId) {
                console.log(regionId);
                url.setBaseUrl(BASE_URL);
                var custom_controller_url = url.build('citylist?param=' + regionId + '&type=region');
                custom_controller_url = custom_controller_url.replace('custom_delivery/index/index', 'custom_delivery/index/citylist');
                $.post(custom_controller_url, 'json')
                    .done(function (response) {
                        if (response && response.res) {
                            console.log(response.res);
                            $('select[name="city"').html(response.res);
                            $('select[name="city"').val('').change();
                        }
                    })
            }
            return this._super();
        },
    });
});
