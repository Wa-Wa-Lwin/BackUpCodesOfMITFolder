require([
    "jquery"
], function($) {
    $(document).ready(function() {
        $('body').on('click', '.table-checkout-shipping-method tbody tr', function(event) {

            $('.table-checkout-shipping-method tbody tr').each(function(index) {
                if (index === 0) {
                    $(this).css('border', 'none');
                }
            });

            $('.table-checkout-shipping-method tbody tr.selected').removeClass('selected');
            $(this).closest('.table-checkout-shipping-method tbody tr').addClass('selected');
        });
    });
});