$(function () {
    /**
     * For clear button
     */
    $('.clear-btn').on('click', function () {
        $('.search-block input').val('');
        $('.search-block select').val(1);
    });

    /**
     * For pagination. This one's confusing so
     * be patient debugging.
     */
    $('input[name=page_text_top], input[name=page_text_bottom]').on('keypress', function(event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            $('input[name=page_hidden]').val($(this).val());
        }
    });

    /**
     * For pagination. This one's confusing so
     * be patient debugging.
     */
    $('.pagination-btn').on('click', function() {
        $('input[name=page_hidden]').val($(this).val());
    });
});
