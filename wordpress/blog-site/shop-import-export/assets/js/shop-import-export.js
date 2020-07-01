jQuery(function($){
    var shop_js_path = $('input[name=shop_js_path]').val();
    var sie_msgs = {
        INFO_01: 'インポートしてもよろしいですか？',
        INFO_02: ''
    }

    $('#import').on('click', function() {
        var file = $('#shop_import_file').val();
        if (file.length < 1) {
            alert('ファイルを入れてください。');
            return false;
        }
        $('#confirmImportModal').fadeIn(500);
    });

    $('#export').on('click', function() {
        $('#confirmExportModal').fadeIn(500);
    });

    $('#custom-sched-export').on('click', function() {
        $('#confirmCustomSchedExportModal').fadeIn(500);
    });

    $('#download').on('click', function() {
        downloadFunc();
        // $('#confirmDownloadLogModal').fadeIn(500);
    });

    $('.sie-btn-import').on('click', importFunc);
    $('.sie-btn-cancel').on('click', cancel);
    $('.sie-btn-export').on('click', exportFunc);
    $('.sie-btn-download').on('click', downloadFunc);
    $('.sie-btn-custom-sched-export').on('click', customSchedExportFunc);

    /**
     * Cancel modal button
     * Close the modal if cancel button in
     * modal is clicked.
     */
    function cancel() {
        $('#confirmExportModal').css('display', 'none');
        $('#confirmImportModal').css('display', 'none');
        $('#confirmDownloadLogModal').css('display', 'none');
        $('#confirmCustomSchedExportModal').css('display', 'none');
    }

    /**
     * Export
     */
    function exportFunc() {
        $('#confirmExportModal').css('display', 'none');
        var filepath = $('input[name="shop_csv_filepath"]').val();
        var download_file = $('input[name="shop_download_file"]').val();
        $.ajax({
            url : shop_js_path+'export',
            type: 'POST',
            data: {},
            beforeSend: function() {
                $('#loader').css('display', 'block');
            },
            success : function(res) {
                $('#loader').css('display', 'none');
                window.location = download_file;
            },
            error   : function() {
                $('#loader').css('display', 'none');
            }
        });
    }

    /**
     * カスタム営業時間
     */
    function customSchedExportFunc() {
        $('#confirmCustomSchedExportModal').css('display', 'none');
        var download_file = $('input[name="shop_download_file"]').val();
        var form = document.getElementById('sie-custom-sched-form');

        $.ajax({
            url : shop_js_path+'sched-export',
            type: 'POST',
            data: {type: 'custom-sched'},
            beforeSend: function() {
                $('#loader').css('display', 'block');
            },
            success : function(res) {
                $('#loader').css('display', 'none');
                form.action = download_file;
                $('#sie-custom-sched-form').submit();
            },
            error   : function() {
                $('#loader').css('display', 'none');
            }
        });
    }

    /**
     * Download
     */
    function downloadFunc() {
        $('#confirmDownloadLogModal').css('display', 'none');
        var filepath = $('input[name="shop_csv_filepath"]').val();
        var download_file = $('input[name="shop_download_file"]').val();
        var form = document.getElementById('dl-form');
        $('#loader').css('display', 'none');
        form.action = download_file;
        $('#dl-form').submit();
    }

    /**
     * Import
     */
    function importFunc() {
        $('#confirmImportModal').css('display', 'none');
        var file = $('input[name=shop_import_csv_file]').val();

        $.ajax({
            url : shop_js_path+'import',
            type: 'POST',
            data: {file: file},
            beforeSend: function() {
                $('#loader').css('display', 'block');
            },
            success : function(res) {
                alert('予約しました。5分以内に実行されます。実行結果は一覧表示されます。');
                $('#loader').css('display', 'none');
            },
            error   : function() {
                $('#loader').css('display', 'none');
            },
            complete : function(res) {
                alert('予約しました。5分以内に実行されます。実行結果は一覧表示されます。');
                $('.sie_remove_image_button').css('display', 'none');
                $('.sie_upload_file_button').addClass('button').html('ファイルを選択');
                $('#loader').css('display', 'none');
            },
        });
    }
});






