jQuery(function ($) {
    /**
     * Date picker setting
     */
    datetimepicker_setting();

    /**
     * Clear the search
     */
    clear_search();

    /**
     * Date time picker setting
     */
    function datetimepicker_setting() {
        // オプション
        var options = {
            mondayFirst: true,
            format: 'yyyy/mm/dd',
            weekDayLabels: ['月', '火', '水', '木', '金', '土', '日'],
            shortMonthLabels: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
            singleMonthLabels: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
            todayButtonLabel: '本日',
            clearButtonLabel: 'クリア',
        };

        // 公開日スタート
        $('#start_date')[0].DatePickerX.init(options);

        // 公開日終了
        $('#end_date')[0].DatePickerX.init(options);
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