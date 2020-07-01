$( document ).ready(function() {
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

        // if password is empty
        ERR_02  : 'パスワードを入力してください。',

        // server error from ajax call
        ERR_03  : 'サーバーエラー',

        // login fail
        ERR_04  : 'ログインに失敗しました。',
    };

    /**
     * ログインボタン
     *
     * If login button is clicked
     * then go the the login function
     * for validation and authentication.
     */
    $('#hc-login-button').on('click', member_login);

    /**
     * Login function.
     *
     * This function holds the validate function
     * and the checking of actual data from the database.
     */
    function member_login(e) {
        e.preventDefault();

        // get the parameters
        var param = {
            action  : hc_login_ajax.hc_ajax_action,
            mem_id  : $('input[name=mem_id]').val().trim(),
            password: $('input[name=mem_pw]').val().trim(),
            type    : 'auth'
        }

        // check the validation of input data
        if (validateParam(param) === false) {
            return false;
        }

        $.ajax({
            url     : hc_login_ajax.ajax_url,
            type    : 'POST',
            dataType: 'json',
            data    : param,
            beforeSend: function() {
                $('#loaderMemberModal').css('display', 'block');
            },
            success: function(response) {
                if (response.success !== true) {
                    $('.hc-login-error').html('');
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-login-error p').html(hc_msgs.ERR_04);
                }

                var res = JSON.parse(response.data);
                if (res.status === 'ng') {
                    $('.hc-login-error').html('');
                    $('#loaderMemberModal').css('display', 'none');
                    $('.hc-login-error').html('<p>'+res.msg.replace(/\n/g,"<br />")+'</p>');
                } else {
                    $('.hc-login-error').html('');
                    location.reload();
                }
            },
            error: function(res) {
                $('.hc-login-error').html('');
                $('#loaderMemberModal').css('display', 'none');
                $('.hc-login-error p').html(hc_msgs.ERR_04);
            }
        });
    }

    /**
     * Validate login id and password
     *
     * @param  object  param input data「loginid&password」
     * @return boolean res   true if no error else false.
     */
    function validateParam(param) {
        var res = true;
        $('.hc-memid-error').html('');
        $('.hc-password-error').html('');

        // check for loginid
        if (param.mem_id.length < 1) {
            $('.hc-memid-error').html(hc_msgs.ERR_01);
            res = false;
        }

        // check for password
        if (param.password.length < 1) {
            $('.hc-password-error').html(hc_msgs.ERR_02);
            res = false;
        }

        return res;
    }

    /**
     * This will now console the type of error
     *
     * @param  string param It is the function that has error.
     */
    function errorConsole(param) {
        console.log('Error '+param);
    }

    /**
     * Clear session storage
     */
    if (typeof(Storage) !== "undefined") {
        sessionStorage.clear();
        if (typeof(sessionStorage.getItem('myData')) !== "undefined") {
            sessionStorage.removeItem('download_list');
        }
    }
});

