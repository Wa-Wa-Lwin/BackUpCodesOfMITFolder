define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function($, modal, url) {
    'use strict';
    return function() {
        // $('#custom-side-menu').html(generateCode(data));

        $(document).ready(function() {
            var result;
            url.setBaseUrl(BASE_URL);
            var custom_controller_url = url.build('payatstore/index/storelist');
            $.ajax({
                url: custom_controller_url,
                type: 'GET',
                dataType: 'json', // added data type
                success: function(data) {

                    result = data.res;
                    var body = '';
                    $.each(result, function(index, value) {
                        var id = value.id;
                        body += '<li class="flex-item"> \
                <input type="radio" class="pas-item-radio" id = "' + id + '" name="payatstore" value=' + value.id + '> \
                <span class="pas-item-name"><strong>' + value.name + '</strong></span> \
                <address class="pas-address"> ' + value.address + ' </address> </li>';
                        // < ul class="pas-address" > \
                        //  <li class="pas-item-addr"> ' + value.address + '</li> \
                        //  <li class="pas-item-region"> ' + value.region + '</li> \
                        //  <li class="pas-item-country"> ' + value.country + '</li> \
                        //  <li class="pas-item-phone"> ' + value.phone + '</li></ul></li>';
                    });
                    if (body) {
                        $("#store-pickup-custom").empty();
                        $('#store-pickup-custom').append(body);
                    }
                }
            })

            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                'modalClass': 'store-pickup-modal',
                title: 'Choose Store',
                buttons: [{
                    text: $.mage.__('Close'),
                    class: '',
                    click: function() {
                        this.closeModal();
                    }
                }]
            };


            // $('.flex-item').click(function() {
            $('body').on('click', '.flex-item', function(event) {
                $('.store-selected').removeClass('store-selected');
                $(this).addClass('store-selected').find('input').prop('checked', true);
                $('[name="custom_attributes[pay_at_store_id]"]').val($(this).addClass('store-selected').find('input').val());
                setSelectedStoreName(result, $(this).addClass('store-selected').find('input').val());
                $("#store-pickup-modal").modal("closeModal");
            });


            var popup = modal(options, $('#store-pickup-modal'));
            $("#pickupstore-custom").on('click', function() {
                $("#store-pickup-modal").modal("openModal");
            });



        });

        function setSelectedStoreName(storeList, selectedId) {
            $.each(storeList, function(index, element) {
                if (Number(element.id) == Number(selectedId)) {
                    $('#pickupstore-custom').text(element.name);
                    return;
                }
            });
        }

    }

});