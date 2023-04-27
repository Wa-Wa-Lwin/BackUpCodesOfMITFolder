define([
    'jquery',
], function($) {
    'use strict';
    return function() {
        $(document).ready(function() {
            var detailTxt = $('#tab-label-description-title').text();
            detailTxt = detailTxt.replace(/ /g, '').trim();
            var regEx = /[^a-z\d]/i;
            var valid = !(regEx.test(detailTxt));
            if (!valid && $('#tab-label-additional-title')) {
                $('#tab-label-additional-title').text('အချက်အလက်များ');
            }
            let i = 0;

            function myLoop() {
                setTimeout(function() {
                    if ($('.custom-ds-lbl.product-detail') && $('.fotorama__stage')) {
                        $('.fotorama__stage').prepend($('.custom-ds-lbl.product-detail'));
                        $('.custom-ds-lbl.product-detail').removeClass('hidden');
                        i = 5;
                    }
                    i++;
                    if (i < 5) {
                        myLoop();
                    }
                }, 3000)
            }

            myLoop();

        });

    };
});