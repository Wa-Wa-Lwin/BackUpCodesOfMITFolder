require([
    "jquery"
], function($) {
    $(document).ready(function() {
        $("#gridProductsSoldOrder_table td").css('background-color', 'transparent');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-status');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-shipping-method');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-payment-method');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-shipment-id');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-invoice-id');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-shipping-amount');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-discount-amount');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-rewards-discount-amount');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-ordered-total');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-ordered-refunded');
        makeRowSpan($("#gridProductsSoldOrder_table"), '.col-order_id');
        $('#gridProductsSoldOrder_table tr').css('border-top', '0.5px solid #beb7b7');
        $('#gridProductsSoldOrder_table tr').css('border-bottom', '0.5px solid #beb7b7');

    });

    function makeRowSpan(selector, index) {
        selector.each(function() {
            var values = $(this).find(index);
            var order = $(this).find('.col-order_id');
            var run = 1;
            for (var i = values.length - 1; i > -1; i--) {
                console.log(values.eq(i).text().trim());
                if (values.eq(i).text().trim() === values.eq(i - 1).text().trim() && order.eq(i).text().trim() === order.eq(i - 1).text().trim()) {

                    values.eq(i).remove();
                    run++;
                } else {
                    values.eq(i).attr("rowspan", run);
                    run = 1;
                }
            }
        })
    }

});