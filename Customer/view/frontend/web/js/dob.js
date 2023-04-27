define([
    'jquery'
], function($) {
    'use strict';
    return function() {
        $(document).ready(function() {
            $('input[name="dob"]').attr('readonly', true);
            $('input[name="dob"]').attr('tabindex', '-1');
            $('input[name="dob"]').css('pointer-events', 'none');
            $('input[name="custom-dob"]').click(function(event) {
                event.preventDefault();
            });
            $('.customer-dob button.ui-datepicker-trigger.v-middle').prop('disabled', true);

        });

    }

});