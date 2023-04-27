define([
    'jquery',
], function($) {
    'use strict';
    return function() {
        $(document).ready(function() {

            $('body').on('click', '.product.photo', function(event) {
                event.preventDefault();
                var url = $(this).attr('href');

                if ($(this).parent().parent().find('.product-item-details [class^=swatch-opt]')) {
                    var code = $(this).parent().parent().find('.product-item-details [class^=swatch-opt] [class^=swatch-attribute]').attr('data-attribute-code');
                    var val = $(this).parent().parent().find('.product-item-details [class^=swatch-opt] .swatch-option.color.selected').attr('data-option-id');

                    if (code && val) {
                        $(location).attr('href', url + '#' + code + '=' + val);
                        return;
                    }
                }

                $(location).attr('href', url);
                return;
            });

            $('body').on('click', '.product-item-name > a', function(event) {
                event.preventDefault();
                var url = $(this).attr('href');

                if ($(this).parent().parent().find('[class^=swatch-opt]')) {
                    var code = $(this).parent().parent().find('[class^=swatch-opt] [class^=swatch-attribute]').attr('data-attribute-code');
                    var val = $(this).parent().parent().find('[class^=swatch-opt] .swatch-option.color.selected').attr('data-option-id');

                    if (code && val) {
                        $(location).attr('href', url + '#' + code + '=' + val);
                        return;
                    }
                }

                $(location).attr('href', url);
                return;
            });
        });

    };
});