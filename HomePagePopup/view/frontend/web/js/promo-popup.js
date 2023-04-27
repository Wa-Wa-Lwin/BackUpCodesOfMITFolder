define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function($, modal, url) {
    'use strict';
    return function() {

        $(document).ready(function() {
            var result;
            url.setBaseUrl(BASE_URL);
            var custom_controller_url = url.build('mit_homepagepopup/index/promopopup');
            $.ajax({
                url: custom_controller_url,
                type: 'GET',
                dataType: 'json', // added data type
                success: function(data) {

                    result = data.image;
                    if (result) {
                        // console.log(result);
                        var body = '';

                            body += '<div class="custom-row">\
                                        <div class="custom-col">\
                                            <div class="card-image">\
                                                <div class="card-img-tiles">\
                                                    <div class="inner">\
                                                        <div class="main-popup-img">\
                                                            <button id="close-popup-modal" class="popup-close-btn" type="button" data-role="closeBtn">\
                                                                <span><i class="fa-thin fa-circle-xmark"></i></span>\
                                                            </button>\
                                                            <img src="'+result+'" class="popup-image" alt="Popup Image">\
                                                        </div>\
                                                    </div>\
                                                </div>\
                                            </div>\
                                        </div>\
                                    </div>';
                        
                        if (body) {
                            $("#promo-popup").empty();
                            $('#promo-popup').append(body);
                        }

                        var options = {
                            type: 'popup',
                            responsive: true,
                            innerScroll: false,
                            modalClass: 'promo-modal-popup',
                            buttons: []
                        };
                        var popupCookie = $.cookie('promo_popup_image');
                        if (!popupCookie || popupCookie == 'undefined') {
                            var popup = modal(options, $('#promo-popup'));
                            $.cookie('promo_popup_image', '1', { path: '/', expires: 1 });
                            $('#promo-popup').modal('openModal');
                        }

                        $('.modal-header').hide();
                        $('#close-popup-modal').click(function() {
                            var popup = modal(options, $('#promo-popup'));
                            $('#promo-popup').modal('closeModal');
                        });
                    }

                }
            })

        });

    }

});
