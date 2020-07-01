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
        // if loginid is empty
        ERR_01  : 'ログインIDを入力してください。',

        // if email is empty
        ERR_02  : 'メールアドレスを入力してください。',

        // if email is valid
        ERR_03  : 'メールアドレスの形式ではありません。',

        // if password is empty
        ERR_04  : 'パスワードを入力してください。',

        // if password is less than 8
        ERR_05  : '文字数が足りません。（8桁以上）',

        // if not alphanumeric
        ERR_06  : '半角英字で入力してください。',

        // server error from ajax call
        ERR_08  : 'サーバーエラー',

        // for modal confirm update
        INFO_01 : '更新してもよろしいですか？',

        // for modal confirm delete
        INFO_02 : '削除してもよろしいですか？',

        // for success update
        INFO_03 : '更新しました。',
    };

    /**
     * メンバー登録ボタン
     */
    $('#member_register').on('click', register_modal);

    /**
     * メンバー更新ボタン
     */
    $('#member_update').on('click', update_modal);

    /**
     * メンバー削除ボタン
     */
    $('#member_delete').on('click', delete_modal);

    /**
     * If modal cancel button is clicked
     */
    $('.hc-btn-cancel').on('click', cancel);

    /**
     * 登録ページ
     *
     * If modal register button is clicked
     */
    $('.hc-reg-register').on('click', register_member);

    /**
     * 詳細ページ
     *
     * If modal register button is clicked
     */
    $('.hc-detail-register').on('click', update_member);

    /**
     * 詳細ページ
     *
     * If modal register button is clicked
     */
    $('.hc-detail-delete').on('click', delete_member);

    /**
     * Sort for loginid in list page
     */
    $('.hc-sort-loginid').hover(sort_hover_before, sort_hover_after);

    /**
     * Sort for email in list page
     */
    $('.hc-sort-email').hover(sort_hover_before, sort_hover_after);

    /**
     * Sort for date in list page
     */
    $('.hc-sort-date').hover(sort_hover_before, sort_hover_after);

    /**
     * Remove the error div
     */
    $('.wrap .hc-inside').on('click', '.hc-server-error .notice-dismiss', remove_error_div);

    /**
     * Show the password of the user
     */
    $('input[name=show-password]').on('click', show_pass);

    /**
     * If register button is clicked
     * then confirm modal will show.
     */
    function register_modal() {
        $('#confirmMemberModal').fadeIn( 500, function() {
            $('#confirmMemberModal').css('display', 'block');
        });
    }

    /**
     * If 更新ボタン is clicked then
     * this function will show a
     * confirmation update modal.
     */
    function update_modal() {
        $('.hc-modal-body').html(hc_msgs.INFO_01);
        $('.hc-detail-register').css('display', 'inline-block');
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
    function delete_modal() {
        $('.hc-modal-body').html(hc_msgs.INFO_02);
        $('.hc-detail-register').css('display', 'none');
        $('.hc-detail-delete').css('display', 'inline-block');
        $('#confirmMemberModal').fadeIn( 500, function() {
            $('#confirmMemberModal').css('display', 'block');
        });
    }

    /**
     * Cancel modal button
     * Close the modal if cancel button in
     * modal is clicked.
     */
    function cancel() {
        $('#confirmMemberModal').css('display', 'none');
    }

    /**
     * 登録メンバー
     *
     * This will hold the getting of parameter.
     * Check the input first then call on ajax.
     * Insert data on server side.
     * Then redirect to 詳細ページ if there's
     * no server error.
     */
    function register_member() {
        $('.hc-server-error').css('display', 'none');
        $('#confirmMemberModal').css('display', 'none');
        $('.hc-server-error p').html('');
        var param = {
            action  : hc_ajax.hc_ajax_action,
            loginid : $('input[name=member_loginid]').val(),
            email   : $('input[name=member_email]').val(),
            password: $('input[name=member_password]').val(),
            type    : 'member-register',
        };

        // check first if input is okay to be sent into the server.
        if (check_member(param) === false) {
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

                    window.location = res.data.admin_url+'admin.php?page=hc_detail&mem_id='+res.data.hc_member_id+'&action=edit';
                }
            },
            error   : function() {
                $('#loaderMemberModal').css('display', 'none');
            }
        });
    }

    /**
     * 更新メンバー
     *
     * This will hold the getting of parameter.
     * Check the input first then call on ajax.
     * Update data on server side.
     * There will be an alert or popup if there's error.
     */
    function update_member() {
        $('.hc-server-error').css('display', 'none');
        $('#confirmMemberModal').css('display', 'none');
        $('.hc-server-error p').html('');

        // if error div is not existing
        if ($('.hc-server-error').length < 1) {
            var error_html = '';
            error_html += '<div class="hc-server-error">';
            error_html += '<p></p>';
            error_html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">この通知を非表示にする</span></button>';
            error_html += '</div>';
            $(error_html).insertBefore('.hc-loginid-div');
        }

        // do not display the error
        $('.hc-server-error').css('display', 'none');
        $('.hc-server-error p').html('');

        // get all the parameters
        var param = {
            action  : hc_ajax.hc_ajax_action,
            loginid : $('input[name=member_loginid]').val(),
            email   : $('input[name=member_email]').val(),
            password: $('input[name=member_password]').val(),
            type    : 'member-detail',
            memid   : $('input[name=memid]').val(),
        };

        // check first if input is okay to be sent into the server.
        if (check_member(param) === false) {
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
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-server-error button').css('display', 'block');
                    $('.hc-server-error').css('display', 'block');
                    $('.hc-server-error').addClass('updated');
                    $('.hc-server-error').addClass('notice');
                    $('.hc-server-error').addClass('is-dismissible');
                    $('.hc-server-error').removeClass('error');
                    $('.wp-core-ui .notice.is-dismissible').css('padding-right', '15px');
                    $('.hc-server-error p').html(hc_msgs.INFO_03);
                }
            },
            error   : function() {
                $('#loaderMemberModal').css('display', 'none');
            }
        });
    }

    /**
     * 削除メンバー
     *
     * This will hold the getting of parameter.
     * Check the input first then call on ajax.
     * Delete data on server side. Then there will be alert
     * or popup if there's error or success.
     */
    function delete_member() {
        $('.hc-server-error').css('display', 'none');
        $('#confirmMemberModal').css('display', 'none');
        $('.hc-server-error p').html('');

        // get all the parameters
        var param = {
            action  : hc_ajax.hc_ajax_action,
            type    : 'member-delete',
            memid   : $('input[name=memid]').val(),
        };

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
                $('input[name=member_loginid]').val();
                $('input[name=member_email]').val();
                $('input[name=member_password]').val();
                $('input[name=memid]').val();
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
                    window.location = res.data.admin_url+'admin.php?page=hc_list';
                }
            },
            error   : function() {
                $('#loaderMemberModal').css('display', 'none');
            }
        });
    }

    /**
     * Sort for before
     */
    function sort_hover_before() {
        $('input[name=hc-sort-hidden]').val();
        $('input[name=hc-sort-column]').val();

        $(this).find('.hc-sort').css({'visibility':'visible'});
    }

    /**
     * Sort for after
     */
    function sort_hover_after() {
        var order   = $('input[name=hc-sort-hidden]').val();
        var orderby = $('input[name=hc-sort-column]').val();

        $(this).find('.hc-sort').css({'visibility':'hidden'});

        if (typeof order === 'undefined') {
            $('.hc-sort-loginid .hc-sort').css({'visibility':'visible'});

            return;
        }

        $('.hc-sort-'+orderby+' .hc-sort').css({'visibility':'visible'});
    }

    /**
     * The main function of this
     * is to remove the error div.
     */
    function remove_error_div() {
        $('.hc-server-error').fadeOut(300, function(){ $(this).remove();});
    }

    /**
     * Check input if not empty
     * and validate input.
     *
     * @return boolean false if there's error otherwise true.
     */
    function check_member(param) {
        var res = true;
        $('.hc-loginid-error').html('');
        $('.hc-email-error').html('');
        $('.hc-password-error').html('');

        // check if loginid is empty
        if (typeof param.loginid !== 'undefined' && param.loginid.length < 1) {
            $('.hc-loginid-error').html(hc_msgs.ERR_01);
            res = false;
        }

        // check if email is empty
        if (typeof param.email !== 'undefined' && param.email.length < 1) {
            $('.hc-email-error').html(hc_msgs.ERR_02);
            res = false;
        } else if (!is_email(param.email)) {
            $('.hc-email-error').html(hc_msgs.ERR_03);
            res = false;
        }

        // check if password is empty
        if (typeof param.password !== 'undefined' && param.password.length < 1) {
            $('.hc-password-error').html(hc_msgs.ERR_04);
            res = false;
        } else if (param.password.length < 8) {
            $('.hc-password-error').html(hc_msgs.ERR_05);
            res = false;
        }  else if (!is_alphanumeric(param.password)) {
            $('.hc-password-error').html(hc_msgs.ERR_06);
            res = false;
        }

        return res;
    }

    /**
     * This will check if value contains
     * alphanumeric data only.
     *
     * @param  string  param Input value
     * @return boolean res   True if match, otherwise false.
     */
    function is_alphanumeric(param) {
        var regex = /^[0-9A-Za-z]+$/;
        var res = regex.exec(param) === null ? false : true;

        return res;
    }

    /**
     * This will check if value contains
     * valid for email data only.
     *
     * @param  string  param Input value
     * @return boolean res   True if match, otherwise false.
     */
    function is_email(param) {
        // Test for the minimum length the email can be
        if (param.length < 6) {
            return false;
        }

        // test for an @ character after the first position
        if (param.indexOf('@', 1) < 0) {
            return false;
        }

        // split the local and the domain part
        var split = param.split('@');

        // test for the local part, test for invalid characters
        var local_regex = /^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/;
        if (local_regex.exec(split[0]) === null) {
            return false;
        }

        // test if there is no domain
        if (split[1].length < 1) {
            return false;
        }

        // test for the domain part, test for sequence of periods
        var domain_regex = /\.{2,}/;
        if (domain_regex.exec(split[1]) !== null) {
            return false;
        }

        // trim the domain part
        if (split[1].trim() !== split[1]) {
            return false;
        }

        // split the dot(.) part on the domain
        var domain_split = split[1].split('.');

        // domain does not have two counts
        if (domain_split.length < 2) {
            return false;
        }

        // check for the two parts of the domain
        for(var i=0; i < domain_split.length; i++) {
            if (domain_split[i].trim() !== domain_split[i]) {
                return false;
            }

            var domain_regex_other = /^[a-z0-9-]+$/;
            if (domain_regex_other.exec(domain_split[i]) === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Show password
     */
    function show_pass() {
        var type = $(this).is(":checked")?'text':'password';
        $('input[name=member_password]').prop('type', type);
    }
})(jQuery);
