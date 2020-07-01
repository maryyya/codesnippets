jQuery(function ($) {
    /**
     * Date picker setting
     */
    datetimepicker_setting();

    /**
     * Disallow some characters
     * for number input
     */
    disallow_characters();

    /**
     * Show the 会員か一般
     * if 有料 in 受講料
     * is clicked.
     */
    show_hide_paid_type();

    /**
     * Clear the search
     */
    clear_search();

    /**
     * Date time picker setting
     */
    function datetimepicker_setting() {
        $('#seminar_start_date, #seminar_end_date, #form_start_date, #form_end_date').appendDtpicker({
            "autodateOnStart": false,
            "locale": "ja",
            "minuteInterval": 5,
        });
    }

    /**
     * To disallow characters in
     * number input.
     */
    function disallow_characters() {
        $('input[type=number]').on('keydown', function (e) {
            var key = ['e', '-', '.'];
            if (key.includes(e.key)) {
                return false;
            }
        });
    }

    /**
     * Show the 会員か一般
     * if 有料 in 受講料
     * is clicked.
     */
    function show_hide_paid_type() {
        $('input[name=jukouryou]').on('click', function () {
            var type = $(this).val();
            if (type === 'free') {
                $('#member').val('');
                $('#general').val('');
                $('.paid_type').addClass('display-none');
            } else {
                $('.paid_type').removeClass('display-none');
            }
        });
    }

    /**
     * Clear search
     */
    function clear_search() {
        $('#clear').on('click', function () {
            $('input[type="text"], input[type="number"], input[type="radio"]').val('');
            $('input[type=radio]').prop('checked', false);
            $('.paid_type').addClass('display-none');
        });
    }
});
