(function($) {
    'use strict';

    /**
     * These object contains
     * all messages for
     * this js file.
     *
     * @object hc_msgs
     */
    var hc_msgs = {
        // if title is empty
        ERR_01  : 'タイトルを入力してください。',

        // if content is empty
        ERR_02  : '資料概要を入力してください。',

        // if date is empty
        ERR_03  : '発行日を入力してください。',

        // if date is valid
        ERR_04  : '日付の形式ではありません。',

        // if tag is empty
        ERR_05  : 'タグを選択してください。',

        // if image is empty
        ERR_06  : 'サムネイルを選択してください。',

        // if file is empty
        ERR_07  : 'ファイルを選択してください。',

        // server error from ajax call
        ERR_08  : 'サーバーエラー',

        // for modal confirm update
        INFO_01 : '更新してもよろしいですか？',

        // for modal confirm delete
        INFO_02 : '削除してもよろしいですか？',

        // for success update
        INFO_03 : '更新しました。',
    };

    // this is for the publish date
    $('input[name=hc-file-date]').datepicker({
        changeMonth    : true,
        changeYear     : true,
        showButtonPanel: true,
        dateFormat     : "yy/mm/dd"
    });

    /**
     * 登録ページ
     *
     * If file register button is clicked
     */
    $('#file_register').on('click', file_register_modal);

    /**
     * 詳細ページ ー 更新ボタン
     *
     * If update button is clicked
     */
    $('#file_update').on('click', file_update_modal);

    /**
     * 詳細ページ ー 削除ボタン
     * If delete button is clickeds
     */
    $('#file_delete').on('click', file_delete_modal);

    /**
     * 登録ページ
     *
     * If file register modal button is clicked
     */
    $('.hc-file-register-btn').on('click', file_register);

    /**
     * 詳細ページ ー 更新
     *
     * If file update modal button is clicked
     */
    $('.hc-file-detail-btn').on('click', file_update);

    /**
     * 詳細ページ ー 削除
     *
     * If file delete modal button is clicked
     */
    $('.hc-detail-delete').on('click', file_delete);

    /**
     * If modal cancel button is clicked
     */
    $('.hc-btn-cancel').on('click', cancel);

    /**
     * Remove the error div
     */
    $('.wrap').on('click', '.hc-server-error .notice-dismiss', remove_error_div);

    /**
     * ファイルとメンバー紐付け登録
     *
     * If member register button is clicked
     */
    $('#file_member_register_btn').on('click', file_member_register_modal);

    /**
     * ファイルとメンバー紐付け登録
     *
     * If member register modal button is clicked
     */
    $('.file-member-btn').on('click', file_member_register);

    /**
     * ファイルとメンバー紐付け削除
     *
     * If file member delete button is clicked
     */
    $('#file_delete_member').on('click', file_member_delete_modal);

    /**
     * ファイルとメンバー紐付け削除
     *
     * If file member delete modal button is clicked
     */
    $('.file-member-delete-modal-btn').on('click', file_member_delete);

    /**
     * If check all members is checked
     */
    $('input[name=hc-member-check-all]').on('click', file_check_all_member);

    /**
     * Cancel modal button
     * Close the modal if cancel button in
     * modal is clicked.
     */
    function cancel() {
        $('#confirmMemberModal').css('display', 'none');
    }

    /**
     * If register button is clicked
     * then confirm modal will show.
     */
    function file_register_modal() {
        $('#confirmMemberModal').fadeIn( 500, function() {
            $('#confirmMemberModal').css('display', 'block');
        });
    }

    /**
     * If 更新ボタン is clicked then
     * this function will show a
     * confirmation update modal.
     */
    function file_update_modal() {
        $('.hc-file-detail-btn').css('display', 'inline-block');
        $('.hc-detail-delete').css('display', 'none');
        $('#confirmMemberModal').fadeIn( 500, function() {
            $('#confirmMemberModal').css('display', 'block');
        });
    }

    /**
     * If 削除ボタン is clicked then
     * this function will show a
     * confirmation delete modal.
     */
    function file_delete_modal() {
        $('.hc-modal-body').html(hc_msgs.INFO_02);
        $('.hc-file-detail-btn').css('display', 'none');
        $('.hc-detail-delete').css('display', 'inline-block');
        $('#confirmMemberModal').fadeIn( 500, function() {
            $('#confirmMemberModal').css('display', 'block');
        });
    }

    /**
     * If 登録ボタン is clicked from
     * ファイルとメンバー紐付け登録
     * then display the modal.
     */
    function file_member_register_modal() {
        $('#confirmMemberModal').fadeIn( 500, function() {
            $('#confirmMemberModal').css('display', 'block');
        });
    }

    /**
     * If 削除ボタン is clicked from
     * ファイルとメンバー紐付け
     * then display the modal.
     */
    function file_member_delete_modal() {
        $('#confirmMemberModal').fadeIn( 500, function() {
            $('#confirmMemberModal').css('display', 'block');
        });
    }

    /**
     * This is for file registration.
     */
    function file_register() {
        $('.hc-server-error').css('display', 'none');
        $('#confirmMemberModal').css('display', 'none');
        $('.hc-server-error p').html('');

        // get the value from textarea tinymce
        var text_content   = $('#wp-special_content-wrap #wp-special_content-editor-container').find('#special_content').val();

        // get the value from textarea visual editor
        var visual_content = $('#special_content_ifr').contents().find('html').find('#tinymce').html();

        // check whether tinymce or html editor is visible
        var active_display = $('.tmce-active').is(":visible");

        // get value
        var param = {
            action    : hc_ajax.hc_ajax_action,
            title     : $('input[name=hc-file-title]').val(),
            // content   : active_display === false ? text_content : (visual_content === '<p><br data-mce-bogus="1"></p>' ? '' : visual_content),
            content   : $('input[name=hc-file-content]').val(),
            issue_date: $('#hc-file-date').val(),
            tag       : typeof $('input[name=hc-file-tag]:checked').val() === 'undefined' ? '' : $('input[name=hc-file-tag]:checked').val(),
            file      : $('#header_file').val() === '0' ? '' : $('#header_file').val(),
            // image_path: typeof $('.true_pre_image').attr('alt') === 'undefined' ? '' : $('.true_pre_image').attr('alt'),
            type      : 'register'
        };

        // check first if input is okay to be sent into the server.
        if (check_file(param) === false) {
            return;
        }

        // send data to the server
        $.ajax({
            url     : hc_ajax.ajax_url,
            type    : 'POST',
            dataType: 'json',
            data    : param,
            beforeSend: function() {
                $('#loaderMemberModal').css('display', 'block');
            },
            success : function(response) {
                if (response.success !== true) {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error p').html(hc_msgs.ERR_08);
                    return false;
                }

                var res = JSON.parse(response.data);
                if (res.status === 'ng') {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error p').html(res.msg.replace(/\n/g,"<br />"));
                } else {
                    $('.hc-server-error').css('display', 'none');
                    $('.hc-server-error p').html('');
                    window.location = res.data.admin_url+'admin.php?page=hc_file_detail&file_id='+res.data.file_id+'&action=edit';
                }
            },
            error   : function() {
                $('#loaderMemberModal').css('display', 'none');
            }
        });
    }

    /**
     * This is for updating the file.
     */
    function file_update() {
        $('.hc-server-error').css('display', 'none');
        $('#confirmMemberModal').css('display', 'none');
        $('.hc-server-error p').html('');

        // if error div is not existing
        if ($('.hc-server-error').length < 1) {
            var error_html = '';
            error_html += '<div class="hc-server-error hc-file-error">';
            error_html += '<p></p>';
            error_html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">この通知を非表示にする</span></button>';
            error_html += '</div>';
            $(error_html).insertBefore('.hc-title-div');
        }

        // get the value from textarea tinymce
        var text_content   = $('#wp-special_content-wrap #wp-special_content-editor-container').find('#special_content').val();

        // get the value from textarea visual editor
        var visual_content = $('#special_content_ifr').contents().find('html').find('#tinymce').html();

        // check whether tinymce or html editor is visible
        var active_display = $('.tmce-active').is(":visible");

        // get value
        var param = {
            action    : hc_ajax.hc_ajax_action,
            title     : $('input[name=hc-file-title]').val(),
            // content   : active_display === false ? text_content : (visual_content === '<p><br data-mce-bogus="1"></p>' ? '' : visual_content),
            content   : $('input[name=hc-file-content]').val(),
            issue_date: $('#hc-file-date').val(),
            tag       : typeof $('input[name=hc-file-tag]:checked').val() === 'undefined' ? '' : $('input[name=hc-file-tag]:checked').val(),
            file      : $('#header_file').val() === '0' ? '' : $('#header_file').val(),
            // image_path: typeof $('.true_pre_image').attr('alt') === 'undefined' ? '' : $('.true_pre_image').attr('alt'),
            type      : 'detail',
            file_id   : $('input[name=file_id]').val(),
        };

        // check first if input is okay to be sent into the server.
        if (check_file(param) === false) {
            return;
        }

        // send data to the server
        $.ajax({
            url     : hc_ajax.ajax_url,
            type    : 'POST',
            dataType: 'json',
            data    : param,
            beforeSend: function() {
                $('#loaderMemberModal').css('display', 'block');
            },
            success : function(response) {
                if (response.success !== true) {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error p').html(hc_msgs.ERR_08);
                    return false;
                }

                var res = JSON.parse(response.data);
                if (res.status === 'ng') {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error').addClass('error');
                    $('.hc-server-error p').html(res.msg.replace(/\n/g,"<br />"));
                    $('.hc-server-error').removeClass('notice');
                    $('.hc-server-error').removeClass('is-dismissible');
                    $('.hc-server-error').removeClass('updated');
                    $('.hc-server-error button').css('display', 'none');
                } else {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error button').css('display', 'block');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error').addClass('updated');
                    $('.hc-server-error').addClass('notice');
                    $('.hc-server-error').addClass('is-dismissible');
                    $('.hc-server-error').removeClass('error');
                    $('.wp-core-ui .notice.is-dismissible').css('padding-right', '15px');
                    var content = '<button type="button" class="notice-dismiss" style="display: block;"><span class="screen-reader-text">この通知を非表示にする</span></button>';
                    $('.hc-server-error').html('<p>'+hc_msgs.INFO_03+'</p>'+content);
                    $(window).scrollTop(0);
                }
            },
            error   : function() {
                $('#loaderMemberModal').css('display', 'none');
            }
        });
    }

    /**
     * Delete the file
     */
    function file_delete() {
        $('.hc-server-error').css('display', 'none');
        $('#confirmMemberModal').css('display', 'none');
        $('.hc-server-error p').html('');

        // get values
        var param = {
            action : hc_ajax.hc_ajax_action,
            type   : 'delete',
            file_id: $('input[name=file_id]').val()
        };

        // send data to the server
        $.ajax({
            url       : hc_ajax.ajax_url,
            type      : 'POST',
            dataType  : 'json',
            data      : param,
            beforeSend: function() {
                $('#loaderMemberModal').css('display', 'block');
            },
            success   : function(response) {
                if (response.success !== true) {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error p').html(hc_msgs.ERR_08);
                    $('.hc-server-error').removeClass('updated');
                    return false;
                }

                var res = JSON.parse(response.data);
                if (res.status === 'ng') {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error').addClass('error');
                    $('.hc-server-error p').html(res.msg.replace(/\n/g,"<br />"));
                    $('.hc-server-error').removeClass('notice');
                    $('.hc-server-error').removeClass('is-dismissible');
                    $('.hc-server-error').removeClass('updated');
                    $('.hc-server-error button').css('display', 'none');
                } else {
                    window.location = res.data.admin_url+'admin.php?page=hc_file_list';
                }
            },
            error    : function() {
                $('#loaderMemberModal').css('display', 'none');
            }
        });
    }

    /**
     * If 登録ボタン is clicked from
     * ファイルとメンバー紐付け登録 modal
     * then register the data into
     * the server.
     */
    function file_member_register() {
        $('#confirmMemberModal').css('display', 'none');
        var selected = [];

        // get the checked members
        $('.hc-file-member-list tbody tr td input:checked').each(function() {
            selected.push($(this).attr('value'));
        });

        // check if there is checked member
        if (selected.length < 1) {
            $('.file_member_error').html('選択してください。');
            return;
        }

        $('.file_member_error').html('');

        var file_id = $('#file_id').val();

        var param = {
            action  : hc_ajax.hc_ajax_action,
            mem_data: selected,
            file_id : file_id.length > 0 ? file_id : '',
            type    : 'file_mem_register'
        };

        $.ajax({
            url       : hc_ajax.ajax_url,
            type      : 'POST',
            dataType  : 'json',
            data      : param,
            beforeSend: function() {
                $('#loaderMemberModal').css('display', 'block');
            },
            success   : function(response) {
                if (response.success !== true) {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error p').html(hc_msgs.ERR_08);
                    $('.hc-server-error').removeClass('updated');
                    return false;
                }

                var res = JSON.parse(response.data);
                if (res.status === 'ng') {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error').addClass('error');
                    $('.hc-server-error p').html(res.msg.replace(/\n/g,"<br />"));
                    $('.hc-server-error').removeClass('notice');
                    $('.hc-server-error').removeClass('is-dismissible');
                    $('.hc-server-error').removeClass('updated');
                    $('.hc-server-error button').css('display', 'none');
                } else {
                    window.location = res.data.admin_url+'admin.php?page=hc_file_member&file_id='+res.data.id;
                }
            },
            error     : function() {
                $('#loaderMemberModal').css('display', 'none');
            }
        });
    }

    /**
     * If 削除ボタン is clicked from
     * ファイルとメンバー紐付け modal
     * then hard delete the data into
     * the server.
     */
    function file_member_delete() {
        $('#confirmMemberModal').css('display', 'none');
        var selected = [];

        // get the checked members
        $('.hc-file-member-list tbody tr td input:checked').each(function() {
            selected.push($(this).attr('value'));
        });

        // check if there is checked member
        if (selected.length < 1) {
            $('.file_member_error').html('選択してください。');
            return;
        }

        $('.file_member_error').html('');

        var file_id = $('#file_id').val();

        var param = {
            action  : hc_ajax.hc_ajax_action,
            mem_data: selected,
            file_id : file_id.length > 0 ? file_id : '',
            type    : 'file_mem_delete'
        };

        $.ajax({
            url       : hc_ajax.ajax_url,
            type      : 'POST',
            dataType  : 'json',
            data      : param,
            beforeSend: function() {
                $('#loaderMemberModal').css('display', 'block');
            },
            success   : function(response) {
                if (response.success !== true) {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error p').html(hc_msgs.ERR_08);
                    $('.hc-server-error').removeClass('updated');
                    return false;
                }

                var res = JSON.parse(response.data);
                if (res.status === 'ng') {
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error').addClass('error');
                    $('.hc-server-error p').html(res.msg.replace(/\n/g,"<br />"));
                    $('.hc-server-error').removeClass('notice');
                    $('.hc-server-error').removeClass('is-dismissible');
                    $('.hc-server-error').removeClass('updated');
                    $('.hc-server-error button').css('display', 'none');
                } else {
                    window.location = res.data.admin_url+'admin.php?page=hc_file_member&file_id='+res.data.id;
                }
            },
            error     : function() {
                $('#loaderMemberModal').css('display', 'none');
            }
        });
    }

    /**
     * Check the parameters of the file
     *
     * @return {Boolean} false if there's an error else true
     */
    function check_file(param) {
        var res = true;
        $('.hc-title-error').html('');
        $('.hc-content-error').html('');
        $('.hc-date-error').html('');
        $('.hc-tag-error').html('');
        $('.hc-file-error').html('');
        $('.hc-thumbnail-error').html('');

        // check for タイトル
        if (param.title.length < 1) {
            $('.hc-title-error').html(hc_msgs.ERR_01);
            return false;
        }

        // check for 資料概要
        if (param.content.length < 1) {
            $('.hc-content-error').html(hc_msgs.ERR_02);
            return false;
        }

        // check for 発行日
        if (param.issue_date.length < 1) {
            $('.hc-date-error').html(hc_msgs.ERR_03);
            return false;
        } else if (is_date_valid(param.issue_date) === false) {
            $('.hc-date-error').html(hc_msgs.ERR_04);
            res = false;
        }

        // check for タグ
        if (param.tag.length < 1) {
            $('.hc-tag-error').html(hc_msgs.ERR_05);
            return false;
        }

        // check for ファイル
        if (param.file.length < 1) {
            $('.hc-file-error').html(hc_msgs.ERR_07);
            return false;
        }

        // check for サムネイル
        // if (param.image_path.length < 1) {
        //     $('.hc-thumbnail-error').html(hc_msgs.ERR_06);
        //     return false;
        // }

        return true;
    }

    /**
     * If checkbox for check all members
     * is checked.
     */
    function file_check_all_member() {
        if ($(this).is(':checked')) {
            $('input[name=hc-member-check]').prop('checked', true);
        } else {
            $('input[name=hc-member-check]').prop('checked', false);
        }
    }

    /**
     * This is to validate the date
     *
     * @param  {string}  param input date
     * @return {Boolean} res   true if date is okay otherwise false.
     */
    function is_date_valid(param) {
        var regex = /^[0-9]{4}[\/|.|-](0[1-9]|1[0-2]|[1-9])[\/|.|-](0[1-9]|[1-2][0-9]|3[0-1]|[1-9])$/;
        var res = regex.exec(param) === null ? false : true;

        return res;
    }

    /**
     * The main function of this
     * is to remove the error div.
     */
    function remove_error_div() {
        $('.hc-server-error').fadeOut(300, function(){ $(this).remove();});
    }
})(jQuery);
