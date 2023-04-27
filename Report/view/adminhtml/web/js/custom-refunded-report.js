require([
    "jquery"
], function($) {
    $(document).ready(function() {
        $("#gridProductsRefundedOrder_table td").css('background-color', 'transparent');
        makeRowSpan($("#gridProductsRefundedOrder_table"), '.col-status');
        makeRowSpan($("#gridProductsRefundedOrder_table"), '.col-ordered-adjustment');
        makeRowSpan($("#gridProductsRefundedOrder_table"), '.col-ordered-refunded-amt');
        makeRowSpan($("#gridProductsRefundedOrder_table"), '.col-ordered-sub-total');
        makeRowSpan($("#gridProductsRefundedOrder_table"), '.col-ordered-discount-amt');
        makeRowSpan($("#gridProductsRefundedOrder_table"), '.col-refund-id');
        makeRowSpan($("#gridProductsRefundedOrder_table"), '.col-order_id');
        $('#gridProductsRefundedOrder_table tr').css('border-top', '0.5px solid #beb7b7');
        $('#gridProductsRefundedOrder_table tr').css('border-bottom', '0.5px solid #beb7b7');

    });

    function makeRowSpan(selector, index) {
        selector.each(function() {
            var values = $(this).find(index);
            var order = $(this).find('.col-order_id');
            var run = 1;
            for (var i = values.length - 1; i > -1; i--) {
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