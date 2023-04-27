define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function($, _, uiRegistry, select, modal, url) {
    'use strict';
    return select.extend({

        initialize: function() {
            var imageId = this._super().initialValue;
            if (imageId) {
                url.setBaseUrl(BASE_URL);
                if (imageId > 0) {
                    var custom_controller_url = url.build('index?id=' + imageId);
                    custom_controller_url = custom_controller_url.replace('sales_rule/index/index', 'mit_discount/index/index');
                    $.post(custom_controller_url, 'json')
                        .done(function(response) {
                            if (response && response.res) {
                                setTimeout(function() {
                                    $('.ds-main').remove();
                                    let styles = '';
                                    let width = $('input[name="width"').val();
                                    let height = $('input[name="height"').val();
                                    if (!(width > 0 || height > 0)) {
                                        width = response.res.width;
                                        height = response.res.height;
                                    }
                                    if (width > 0 && height > 0) {
                                        styles = 'style="width: ' + width + 'px !important; height: ' + height + 'px !important;"';
                                    } else if (width > 0) {
                                        styles = 'style="width: ' + width + 'px !important;"';
                                    } else if (height > 0) {
                                        styles = 'style="height: ' + height + 'px !important;"';
                                    }
                                    let body = ' <div class="ds-main"><div class="custom-ds-lbl ' + response.res.main_class + '"> \
                                                <img class="discount-img" src="' + response.res.imgPath + '" ' + styles + '> \
                                                <span class="discount-txt ' + response.res.sub_class + '"> ' + $('input[name="discount_label"').val() + ' </span> \
                                            </div></div> \
                                    ';
                                    $('select[name="discount_image_id"').parent().append(body);
                                    $('input[name="width"').val(width);
                                    $('input[name="height"').val(height);
                                }, 2000);
                            }
                        });
                } else {
                    $('.ds-main').remove();
                    $('input[name="width"').val('').change();
                    $('input[name="height"').val('').change();
                }

            }
            return this._super();
        },

        /**
         * On value change handler.
         *
         * @param {String} imageId
         */
        onUpdate: function(imageId) {
            if (imageId) {
                url.setBaseUrl(BASE_URL);
                if (imageId > 0) {
                    var custom_controller_url = url.build('index?id=' + imageId);
                    custom_controller_url = custom_controller_url.replace('sales_rule/index/index', 'mit_discount/index/index');
                    $.post(custom_controller_url, 'json')
                        .done(function(response) {
                            if (response && response.res) {
                                $('.ds-main').remove();
                                let styles = '';
                                if (response.res.width > 0 && response.res.height > 0) {
                                    styles = 'style="width: ' + response.res.width + 'px !important; height: ' + response.res.height + 'px !important;"';
                                } else if (response.res.width > 0) {
                                    styles = 'style="width: ' + response.res.width + 'px !important;"';
                                } else if (response.res.height > 0) {
                                    styles = 'style="height: ' + response.res.height + 'px !important;"';
                                }
                                let body = ' <div class="ds-main"><div class="custom-ds-lbl ' + response.res.main_class + '"> \
                                        <img class="discount-img" src="' + response.res.imgPath + '" ' + styles + '> \
                                        <span class="discount-txt ' + response.res.sub_class + '"> ' + $('input[name="discount_label"').val() + ' </span> \
                                    </div></div> \
                            ';
                                $('select[name="discount_image_id"').parent().append(body);
                                $('input[name="width"').val(response.res.width);
                                $('input[name="height"').val(response.res.height);
                            }
                        });
                } else {
                    $('.ds-main').remove();
                    $('input[name="width"').val('').change();
                    $('input[name="height"').val('').change();
                }
            }
            return this._super();
        }
    });
});