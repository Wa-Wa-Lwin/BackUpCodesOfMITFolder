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

        initialize: function () {
            var cityId = this._super().initialValue;
            if (cityId) {
                url.setBaseUrl(BASE_URL);
                var custom_controller_url = url.build('citylist?param=' + cityId + '&type=city');
                custom_controller_url = custom_controller_url.replace('custom_delivery/index/index', 'custom_delivery/index/citylist');
                $.post(custom_controller_url, 'json')
                    .done(function (response) {
                        if (response && response.res) {
                            console.log(response.res);
                            $('select[name="city"').html(response.res);
                            $('select[name="city"]').val(cityId).change();
                        }
                    });
            }
            return this._super();
        }
    });
});
