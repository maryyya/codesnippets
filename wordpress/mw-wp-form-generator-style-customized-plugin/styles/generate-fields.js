jQuery(function ($) {
    /**
     * Display class name
     */
    var disp_class_name = 'ppc-disp-none';
    var ppc_theme_dir = $('input[name=ppc_theme_dir]').val();

    /**
     * This list is the original for the mw wp form
     */
    var custom_list = {
        // カスタムテキスト
        'mwform_ppc_display_text': {
            label: 'テキストのみ',
            name: 'mwform_ppc_display_text',
            method: function (mw_options, mw_option, mw_data, res, position, disp) {
                mwform_ppc_create_html_text_disp(mw_options, mw_option, mw_data, res, position, disp);
            }
        },

        // h3タグ
        'mwform_ppc_h3': {
            label: '見出し',
            name: 'mwform_ppc_h3',
            method: function (mw_options, mw_option, mw_data, res, position, disp) {
                mwform_ppc_create_html_h3(mw_options, mw_option, mw_data, res, position, disp);
            }
        },

        // 住所
        'mwform_ppc_place': {
            label: '住所',
            name: 'mwform_ppc_place',
            method: function (mw_options, mw_option, mw_data, res, position, disp) {
                mwform_ppc_place(mw_options, mw_option, mw_data, res, position, disp);
            }
        },

        // ご連絡氏名
        'mwform_ppc_applicant_name': {
            label: 'ご連絡担当者氏名',
            name: 'mwform_ppc_applicant_name',
            method: function (mw_options, mw_option, mw_data, res, position, disp) {
                mwform_ppc_applicant_name(mw_options, mw_option, mw_data, res, position, disp);
            }
        },

        // 参加者のseparator
        'mwform_ppc_participant_separator': {
            label: '参加者の区切り',
            name: 'mwform_ppc_participant_separator',
            method: function (mw_options, mw_option, mw_data, res, position, disp) {
                mwform_ppc_participant_separator(mw_options, mw_option, mw_data, res, position, disp);
            }
        },

        // 参加者氏名
        'mwform_ppc_participant_name': {
            label: '参加者氏名',
            name: 'mwform_ppc_participant_name',
            method: function (mw_options, mw_option, mw_data, res, position, disp) {
                mwform_ppc_participant_name(mw_options, mw_option, mw_data, res, position, disp);
            }
        },

        // テキストエリアラベルなし
        'mwform_ppc_textarea_no_label': {
            label: '連絡事項',
            name: 'mwform_ppc_textarea_no_label',
            method: function (mw_options, mw_option, mw_data, res, position, disp) {
                mwform_ppc_textarea_no_label(mw_options, mw_option, mw_data, res, position, disp);
            }
        },

        // チェックボックスはbackgroundがあります。
        'mwform_ppc_checkbox_with_bg': {
            label: 'ご連絡担当者氏名コピーチェックボックスタグ',
            name: 'mwform_ppc_checkbox_with_bg',
            method: function (mw_options, mw_option, mw_data, res, position, disp) {
                mwform_ppc_checkbox_with_bg(mw_options, mw_option, mw_data, res, position, disp);
            }
        },

        // 新入社員研修会のチェックボックススケジュール
        // 'mwform_ppc_fresh_checkbox_sched': {
        //     label: '新入社員研修会のチェックボックススケジュール',
        //     name: 'mwform_ppc_fresh_checkbox_sched',
        //     method: function (mw_options, mw_option, mw_data, res, position, disp) {
        //         mwform_ppc_fresh_checkbox_sched(mw_options, mw_option, mw_data, res, position, disp);
        //     }
        // },


        // お取引店
        'mwform_ppc_dealer': {
            label: '取引銀行支店名',
            name: 'mwform_ppc_dealer',
            method: function (mw_options, mw_option, mw_data, res, position, disp) {
                mwform_ppc_dealer(mw_options, mw_option, mw_data, res, position, disp);
            }
        },

        // お取引店
        'mwform_ppc_dealer_dogin': {
            label: '取引銀行支店名（道銀）',
            name: 'mwform_ppc_dealer_dogin',
            method: function (mw_options, mw_option, mw_data, res, position, disp) {
                mwform_ppc_dealer_dogin(mw_options, mw_option, mw_data, res, position, disp);
            }
        },
    };

    /**
     * カテゴリーのカスタムフィールドを表示される・されない
     */
    hide_check_category();

    /**
     * 新入社員研修会コース
     */
    // shinnyushain_course();

    /**
     * MW WP Form settings for customized stuff
     */
    mw_wp_form();

    /**
     * Require name for every field
     */
    require_name();

    /**
     * To add checkbox label
     * on the validation box
     */
    add_validation_checkbox_label();

    /**
     * To change the numbering of the mw wp form
     * or position
     */
    $('input[name=save]').on('click', function (e) {
        $('.mw-wp-form-generator-form-options .mw-wp-form-generator-form-option.repeatable-box').each(function (i) {
            var $input = $(this).find('input');
            change_index($input, i);

            var $textarea = $(this).find('textarea');
            change_index($textarea, i);
        });

        // e.preventDefault();
    });

    /**
     * This works on input and
     * textarea
     *
     * @param object $input
     */
    function change_index($input, i) {
        $input.each(function () {
            $single_input = $(this);
            var input_name = $single_input.attr('name');
            var replacement = input_name.replace(/\d+/, i);
            $single_input.attr('name', replacement);
        });
    }

    /**
     * カテゴリーのカスタムフィールドを表示される・されない
     */
    function hide_check_category() {
        var seminar_cat_val = $('input[name=ppc_seminar_category]').val();
        if (typeof seminar_cat_val === 'undefined') {
            return;
        }
        var seminar_category = $.parseJSON(seminar_cat_val);
        var post_cat_id = typeof $('input[name=ppc_post_cat_id]').val() !== 'undefined' ? parseInt($('input[name=ppc_post_cat_id]').val()) : '';
        var seminar_acf = seminar_category.map(function (val) {
            return val.acf !== null ? val.acf : '';
        });

        for (let i = 0; i < seminar_category.length; i++) {
            const element = seminar_category[i];
            if (element.acf !== null) {
                var post_cat_val = parseInt(post_cat_id) === parseInt(element.term_id) ? true : false;
                check(post_cat_val, element.acf, seminar_acf);
            }

            $('.wp-admin').on('click', '#in-seminar_cat-' + element.term_id, function () {
                // check_parent_term_radio(this, element);
                check(this, element.acf, seminar_acf);
            });
        }
    }

    /**
     * Check whether the category
     * radio button is checked
     *
     * @param object el
     * @param string acf
     * @param array seminar_acf
     */
    function check(el, acf, seminar_acf) {
        if ($(el).is(':checked') || el === true) {
            split(acf, 'removeClass');
            for (let y = 0; y < seminar_acf.length; y++) {
                if (seminar_acf[y].indexOf(acf) < 0) {
                    split(seminar_acf[y], 'addClass');
                }
            }
        } else {
            split(acf, 'addClass');
        }
    }

    /**
     * Split the acf value since there are some
     * that has two acf groups like lilac group
     *
     * @param string acf
     * @param string type
     */
    function split(acf, type) {
        if (acf === '' || acf === null) {
            return;
        }
        var split = acf.split(', ');
        if (split.length > 1) {
            for (let y = 0; y < split.length; y++) {
                const element_split = split[y];
                if (type === 'addClass') {
                    $('#acf-' + element_split).addClass(disp_class_name);
                } else {
                    $('#acf-' + element_split).removeClass(disp_class_name);

                }
            }
        } else {
            if (type === 'addClass') {
                $('#acf-' + acf).addClass(disp_class_name);
            } else {
                $('#acf-' + acf).removeClass(disp_class_name);
            }
        }
    }

    /**
     * 新入社員研修会コース
     */
    function shinnyushain_course() {
        var str = '<div class="acf-field acf-field-text">';
        str += '<div class="acf-label">';
        str += '<label>日数</label></div>';
        str += '<div class="acf-input">';
        str += '<div class="acf-date-time-picker acf-input-wrap" data-date_format="yy-mm-dd" data-time_format="HH:mm:ss" data-first_day="1">';
        str += '<input type="hidden" id="acf-ddddd" class="input-alt" name="acf[ddddd]" value="2019-11-08 00:00:00" style="cursor: pointer;">';
        str += '<input type="text" class="input hasDatepicker" value="" id="dp1573109493202">';
        str += '</div>';
        str += '</div>';
        str += '</div>';
        // $('.wp-admin .shinyu-last').after(str);
    }

    /**
     * This function is for the additional customized fields
     * in mw wp form. This is in admin page.
     */
    function mw_wp_form() {
        var mw_add = '.mw-wp-form-generator-add-btn ';
        var mw_options = '.mw-wp-form-generator-form-options';
        var mw_option = '.mw-wp-form-generator-form-option';
        var select = mw_add + ' select';

        var html = '';
        html += '<optgroup label="カスタマイズ（オリジナル）">';
        for (const i in custom_list) {
            if (custom_list.hasOwnProperty(i)) {
                const el = custom_list[i];
                html += '<option value="' + i + '">' + custom_list[i].label + '</option>';

                // remove button
                $(mw_options).on('click', '.ppc-remove-btn-' + i, function () {
                    $(this).parent().remove();
                });

                // open close button
                $(mw_options).on('click', '.ppc-open-close-btn-' + i + ' b', function () {
                    // $('.ppc-div-' + custom_list[i].value + ' .repeatable-box-content').toggle();
                    $(this).parent().parent().find('.repeatable-box-content').toggle();
                });
            }
        }
        html += '</optgroup>';
        $(select).append(html);

        // if the customized(↑) was selected
        $(mw_add + ' .button').on('click', function () {
            var select_val = $(select).val();
            if (typeof custom_list[select_val] === 'undefined') {
                return;
            }
            custom_list[select_val].method(mw_options, mw_option, custom_list[select_val], {
                data: {
                    display_name: '',
                    value: ''
                }
            }, 'append', 'block');
        });

        // to display if there's custom fields
        mwform_display_custom_field(mw_options, mw_option);

        // select the form style automatically
        mwform_select_style();
    }

    /**
     * To display the text in front page
     *
     * @param string mw_options This is the class selector for appending the customized text.
     * @param string mw_options This is the class selector for the rows.
     * @param string mw_data    This is the custom field data. For admin use only.
     * @param string res        This is the user inputted data.
     * @param int    position   Position on where to put the custom field.
     * @param string display    If added then 'block', if there's data already then 'none'. This is in 'repeatable-box-content' div class
     */
    function mwform_ppc_create_html_text_disp(mw_options, mw_option, mw_data, res, position, disp) {
        var row_num = position === 'append' ? $(mw_options + ' ' + mw_option).length + 1 : position;
        var custom_field = 'mw-wp-form-generator[_' + row_num + '][ppc_custom_field]';
        var name_generator = 'mw-wp-form-generator[_' + row_num + '][data][value]';
        var row_label = 'mw-wp-form-generator[_' + row_num + '][data][display_name]';
        var row_label_class = 'ppc-label-' + mw_data.name;

        var html = '';
        html += '<div class="mw-wp-form-generator-form-option repeatable-box" data-field="mwform_textarea" style="display: block;">';
        html += '<div class="remove-btn ppc-remove-btn-' + mw_data.name + '"><b>×</b></div>';
        html += '<div class="open-btn ppc-open-close-btn-' + mw_data.name + '"><span>' + mw_data.label + '</span> ( テキストが表示される )<b>▼</b></div>';
        html += '<div class="repeatable-box-content" style="display: ' + disp + ';">';
        html += '<span class="mwf_note">例。<br><img style="width:100%" src="' + ppc_theme_dir + '/common/images/sample_text.png"></span>';
        html += '<p>';
        html += '<strong>表示名</strong>';
        html += '<input type="text" class="' + row_label_class + '" name="' + row_label + '" value="' + res.data.display_name + '">';
        html += '</p>';
        html += '<p>';
        html += '<strong>内容</strong>';
        html += '<textarea class="' + mw_data.name + '" name="' + name_generator + '">' + res.data.value + '</textarea>';
        html += '<span class="mwf_note">入力しなかった場合、このフォーム項目は表示されません。表示される場合はnl2brのphp機能を使わないです。</span>';
        html += '</p>';
        html += '<input type="hidden" name="' + custom_field + '" value="' + mw_data.name + '">';
        html += '<!-- end .repeatable-box-content --></div>';
        html += '<!-- end .repeatable-box --></div>';

        // put the custom field in its specific position
        mwform_add_html(position, html, mw_options, mw_option);
    }

    /**
     * Just display some h3 on the form
     *
     * @param string mw_options This is the class selector for appending the customized text.
     * @param string mw_options This is the class selector for the rows.
     * @param string mw_data    This is the custom field data. For admin use only.
     * @param string res        This is the user inputted data.
     * @param int    position   Position on where to put the custom field.
     * @param string display    If added then 'block', if there's data already then 'none'. This is in 'repeatable-box-content' div class
     */
    function mwform_ppc_create_html_h3(mw_options, mw_option, mw_data, res, position, disp) {
        var row_num = position === 'append' ? $(mw_options + ' ' + mw_option).length + 1 : position;
        var row_label = 'mw-wp-form-generator[_' + row_num + '][data][value]';
        var custom_field = 'mw-wp-form-generator[_' + row_num + '][ppc_custom_field]';
        var row_label_class = 'ppc-label-' + mw_data.name;
        var html = '';
        html += '<div class="mw-wp-form-generator-form-option repeatable-box" data-field="mwform_textarea" style="display: block;">';
        html += '<div class="remove-btn ppc-remove-btn-' + mw_data.name + '"><b>×</b></div>';
        html += '<div class="open-btn ppc-open-close-btn-' + mw_data.name + '"><span>' + mw_data.label + '</span> ( テキストが表示される )<b>▼</b></div>';
        html += '<div class="repeatable-box-content" style="display: ' + disp + ';">';
        html += '<span class="mwf_note">例。<br><img style="width:100%" src="' + ppc_theme_dir + '/common/images/sample_h3.png"></span>';
        html += '<p>';
        html += '<strong>表示名</strong>';
        html += '<input type="text" class="' + row_label_class + '" name="' + row_label + '" value="' + res.data.value + '">';
        html += '<span class="mwf_note">入力しなかった場合、このフォーム項目は表示されません。</span>';
        html += '</p>';
        html += '<input type="hidden" name="' + custom_field + '" value="' + mw_data.name + '">';
        html += '<!-- end .repeatable-box-content --></div>';
        html += '<!-- end .repeatable-box --></div>';

        // put the custom field in its specific position
        mwform_add_html(position, html, mw_options, mw_option);
    }

    /**
     * Just display the 住所 on the form
     *
     * @param string mw_options This is the class selector for appending the customized text.
     * @param string mw_options This is the class selector for the rows.
     * @param string mw_data    This is the custom field data. For admin use only.
     * @param string res        This is the user inputted data.
     * @param int    position   Position on where to put the custom field.
     * @param string display    If added then 'block', if there's data already then 'none'. This is in 'repeatable-box-content' div class
     */
    function mwform_ppc_place(mw_options, mw_option, mw_data, res, position, disp) {
        var row_num = position === 'append' ? $(mw_options + ' ' + mw_option).length + 1 : position;
        var custom_field = 'mw-wp-form-generator[_' + row_num + '][ppc_custom_field]';
        var field_name = 'mw-wp-form-generator[_' + row_num + '][data][field_name]';
        var field_name_value = typeof res.data.field_name === 'undefined' ? '' : res.data.field_name;

        var html = '';
        html += '<div class="mw-wp-form-generator-form-option repeatable-box" data-field="mwform_textarea" style="display: block;">';
        html += '<div class="remove-btn ppc-remove-btn-' + mw_data.name + '"><b>×</b></div>';
        html += '<div class="open-btn ppc-open-close-btn-' + mw_data.name + '"><span>' + mw_data.label + '</span> ( 固定 )<b>▼</b></div>';
        html += '<div class="repeatable-box-content" style="display: ' + disp + ';">';
        html += '<span class="mwf_note">住所は固定フィールドです。<br>フロント側で住所は必須です。なのではバリデーションルールでなくってもいいです。<br>例。<br><img style="width:100%" src="' + ppc_theme_dir + '/common/images/sample_place.png"></span>';
        html += '<input type="hidden" name="' + custom_field + '" value="' + mw_data.name + '">';
        html += '<p>';
        html += '<strong>name<span class="mwf_require">*</span></strong>';
        html += '<span class="mwf_note">この名前は一意である必要があります。<br>他のフィールドにはこの名前を書かないでください。<br>英数字を入力してください。</span>';
        html += '<input type="text" data-label="' + mw_data.label + '" class="ppc_req ppc_field_name" name="' + field_name + '" value="' + field_name_value + '" >';
        html += '<span class="mwf_note">例。address_1</span>';
        html += '</p>';
        html += '<!-- end .repeatable-box-content --></div>';
        html += '<!-- end .repeatable-box --></div>';

        // put the custom field in its specific position
        mwform_add_html(position, html, mw_options, mw_option);
    }

    /**
     * Just display the 名 on the form
     *
     * @param string mw_options This is the class selector for appending the customized text.
     * @param string mw_options This is the class selector for the rows.
     * @param string mw_data    This is the custom field data. For admin use only.
     * @param string res        This is the user inputted data.
     * @param int    position   Position on where to put the custom field.
     * @param string display    If added then 'block', if there's data already then 'none'. This is in 'repeatable-box-content' div class
     */
    function mwform_ppc_applicant_name(mw_options, mw_option, mw_data, res, position, disp) {
        var row_num = position === 'append' ? $(mw_options + ' ' + mw_option).length + 1 : position;
        var custom_field = 'mw-wp-form-generator[_' + row_num + '][ppc_custom_field]';
        var field_name = 'mw-wp-form-generator[_' + row_num + '][data][field_name]';
        var field_name_value = typeof res.data.field_name === 'undefined' ? '' : res.data.field_name;

        var html = '';
        html += '<div class="mw-wp-form-generator-form-option repeatable-box" data-field="mwform_textarea" style="display: block;">';
        html += '<div class="remove-btn ppc-remove-btn-' + mw_data.name + '"><b>×</b></div>';
        html += '<div class="open-btn ppc-open-close-btn-' + mw_data.name + '"><span>' + mw_data.label + '</span> ( 固定 )<b>▼</b></div>';
        html += '<div class="repeatable-box-content" style="display: ' + disp + ';">';
        html += '<span class="mwf_note">これは固定フィールドです。<br>例。<br><img style="width:100%" src="' + ppc_theme_dir + '/common/images/sample_name.png"></span>';
        // html += '<span class="mwf_note">入力しなかった場合、このフォーム項目は表示されません。</span>';
        html += '<input type="hidden" name="' + custom_field + '" value="' + mw_data.name + '">';
        html += '<p>';
        // html += '<strong>name<span class="mwf_require">*</span></strong>';
        // html += '<span class="mwf_note">この名前は一意である必要があります。<br>他のフィールドにはこの名前を書かないでください。<br>英数字を入力してください。<br>「お申込み手続き者と同一」フォームタグを利用する時はnameに「applicant」で入力してください。</span>';
        // html += 'participant_name_<input class="ppc_req" type="hidden" min="1" max="" name="applicant" value="applicant" >';
        // html += '<span class="mwf_note">例。applicant_name_1</span>';
        html += '<input class="ppc_req ppc_field_name" data-label="' + mw_data.label + '" type="hidden" min="1" max="" name="' + field_name + '" value="applicant" >';
        html += '</p>';
        html += '<!-- end .repeatable-box-content --></div>';
        html += '<!-- end .repeatable-box --></div>';

        // put the custom field in its specific position
        mwform_add_html(position, html, mw_options, mw_option);
    }

    /**
     * Just display the 名 on the form
     *
     * @param string mw_options This is the class selector for appending the customized text.
     * @param string mw_options This is the class selector for the rows.
     * @param string mw_data    This is the custom field data. For admin use only.
     * @param string res        This is the user inputted data.
     * @param int    position   Position on where to put the custom field.
     * @param string display    If added then 'block', if there's data already then 'none'. This is in 'repeatable-box-content' div class
     */
    function mwform_ppc_participant_name(mw_options, mw_option, mw_data, res, position, disp) {
        var row_num = position === 'append' ? $(mw_options + ' ' + mw_option).length + 1 : position;
        var custom_field = 'mw-wp-form-generator[_' + row_num + '][ppc_custom_field]';
        var field_name = 'mw-wp-form-generator[_' + row_num + '][data][field_name]';
        var field_name_value = typeof res.data.field_name === 'undefined' ? '' : res.data.field_name;

        var html = '';
        html += '<div class="mw-wp-form-generator-form-option repeatable-box" data-field="mwform_textarea" style="display: block;">';
        html += '<div class="remove-btn ppc-remove-btn-' + mw_data.name + '"><b>×</b></div>';
        html += '<div class="open-btn ppc-open-close-btn-' + mw_data.name + '"><span>' + mw_data.label + '</span> ( 固定 )<b>▼</b></div>';
        html += '<div class="repeatable-box-content" style="display: ' + disp + ';">';
        html += '<span class="mwf_note">これは固定フィールドです。<br>例。<br><img style="width:100%" src="' + ppc_theme_dir + '/common/images/sample_name.png"></span>';
        html += '<span class="mwf_note">入力しなかった場合、このフォーム項目は表示されません。</span>';
        html += '<input type="hidden" name="' + custom_field + '" value="' + mw_data.name + '">';
        html += '<p>';
        html += '<strong>name<span class="mwf_require">*</span></strong>';
        html += '<span class="mwf_note">この名前は一意である必要があります。<br>他のフィールドにはこの名前を書かないでください。<br>数字を入力してください。</span>';
        html += 'participant_name_<input class="ppc_req ppc_field_name" data-label="' + mw_data.label + '" type="number" min="1" max="" name="' + field_name + '" value="' + field_name_value + '" >';
        html += '<span class="mwf_note">例。participant_name_1</span>';
        html += '</p>';
        html += '<!-- end .repeatable-box-content --></div>';
        html += '<!-- end .repeatable-box --></div>';

        // put the custom field in its specific position
        mwform_add_html(position, html, mw_options, mw_option);
    }

    /**
     * テキストエリアラベルなし
     *
     * @param string mw_options This is the class selector for appending the customized text.
     * @param string mw_options This is the class selector for the rows.
     * @param string mw_data    This is the custom field data. For admin use only.
     * @param string res        This is the user inputted data.
     * @param int    position   Position on where to put the custom field.
     * @param string display    If added then 'block', if there's data already then 'none'. This is in 'repeatable-box-content' div class
     */
    function mwform_ppc_textarea_no_label(mw_options, mw_option, mw_data, res, position, disp) {
        var row_num = position === 'append' ? $(mw_options + ' ' + mw_option).length + 1 : position;
        var custom_field = 'mw-wp-form-generator[_' + row_num + '][ppc_custom_field]';
        var name_generator = 'mw-wp-form-generator[_' + row_num + '][data][value]';
        var field_name = 'mw-wp-form-generator[_' + row_num + '][data][field_name]';
        var field_name_value = typeof res.data.field_name === 'undefined' ? '' : res.data.field_name;

        var html = '';
        html += '<div class="mw-wp-form-generator-form-option repeatable-box" data-field="mwform_textarea" style="display: block;">';
        html += '<div class="remove-btn ppc-remove-btn-' + mw_data.name + '"><b>×</b></div>';
        html += '<div class="open-btn ppc-open-close-btn-' + mw_data.name + '"><span>' + mw_data.label + '</span> ( 固定 )<b>▼</b></div>';
        html += '<div class="repeatable-box-content" style="display: ' + disp + ';">';
        html += '<span class="mwf_note">これは固定フィールドです。<br>例。<br><img style="width:100%" src="' + ppc_theme_dir + '/common/images/sample_textarea.png"></span>';
        html += '<p>';
        html += '<strong>name<span class="mwf_require">*</span></strong>';
        html += '<span class="mwf_note">この名前は一意である必要があります。<br>他のフィールドにはこの名前を書かないでください。<br>英数字を入力してください。</span>';
        html += '<input class="field_name" data-label="' + mw_data.label + '" type="text" name="' + field_name + '" value="' + field_name_value + '" >';
        html += '<span class="mwf_note">例。participant_textarea_1</span>';
        html += '</p>';
        html += '<p>';
        html += '<strong>説明</strong>';
        html += '<textarea class="' + mw_data.name + '" name="' + name_generator + '">' + res.data.value + '</textarea>';
        html += '<span class="mwf_note">入力しなかった場合、このフォーム項目は表示されません。テキストボックスの上に入れます。</span>';
        html += '</p>';
        html += '<input type="hidden" name="' + custom_field + '" value="' + mw_data.name + '">';
        html += '<!-- end .repeatable-box-content --></div>';
        html += '<!-- end .repeatable-box --></div>';

        // put the custom field in its specific position
        mwform_add_html(position, html, mw_options, mw_option);
    }

    /**
     * チェックボックスはグレーの背景色があり
     *
     * @param string mw_options This is the class selector for appending the customized text.
     * @param string mw_options This is the class selector for the rows.
     * @param string mw_data    This is the custom field data. For admin use only.
     * @param string res        This is the user inputted data.
     * @param int    position   Position on where to put the custom field.
     * @param string display    If added then 'block', if there's data already then 'none'. This is in 'repeatable-box-content' div class
     */
    function mwform_ppc_checkbox_with_bg(mw_options, mw_option, mw_data, res, position, disp) {
        var row_num = position === 'append' ? $(mw_options + ' ' + mw_option).length + 1 : position;
        var custom_field = 'mw-wp-form-generator[_' + row_num + '][ppc_custom_field]';
        var row_label = 'mw-wp-form-generator[_' + row_num + '][data][display_name]';
        var field_name = 'mw-wp-form-generator[_' + row_num + '][data][field_name]';
        var field_name_value = typeof res.data.field_name === 'undefined' ? '' : res.data.field_name;

        var html = '';
        html += '<div class="mw-wp-form-generator-form-option repeatable-box" data-field="mwform_textarea" style="display: block;">';
        html += '<div class="remove-btn ppc-remove-btn-' + mw_data.name + '"><b>×</b></div>';
        html += '<div class="open-btn ppc-open-close-btn-' + mw_data.name + '"><span>' + mw_data.label + '</span> ( 固定 )<b>▼</b></div>';
        html += '<div class="repeatable-box-content" style="display: ' + disp + ';">';
        html += '<span class="mwf_note"><img style="width:100%" src="' + ppc_theme_dir + '/common/images/sample_checkbox_1.png"></span>';
        html += '<span class="mwf_note">例。<br><img style="width:100%" src="' + ppc_theme_dir + '/common/images/sample_checkbox.png"></span>';
        html += '<p>';
        html += '<strong>name<span class="mwf_require">*</span></strong>';
        html += '<span class="mwf_note">この名前は一意である必要があります。<br>他のフィールドにはこの名前を書かないでください。<br>英数字を入力してください。</span>';
        html += 'participant_checkbox_<input type="number" data-label="' + mw_data.label + '" class="ppc_field_name" name="' + field_name + '" value="' + field_name_value + '" >';
        html += '<span class="mwf_note">例。participant_checkbox_1</span>';
        html += '</p>';
        html += '<p>';
        html += '<strong>チェックボックスラベル</strong>';
        html += '<input type="text" name="' + row_label + '" value="' + res.data.display_name + '">';
        html += '<span class="mwf_note">入力しなかった場合、このフォーム項目は表示されません。</span>';
        html += '</p>';
        html += '<input type="hidden" name="' + custom_field + '" value="' + mw_data.name + '">';
        html += '<!-- end .repeatable-box-content --></div>';
        html += '<!-- end .repeatable-box --></div>';

        // put the custom field in its specific position
        mwform_add_html(position, html, mw_options, mw_option);
    }

    /**
     * Just display the お取引店 on the form
     *
     * @param string mw_options This is the class selector for appending the customized text.
     * @param string mw_options This is the class selector for the rows.
     * @param string mw_data    This is the custom field data. For admin use only.
     * @param string res        This is the user inputted data.
     * @param int    position   Position on where to put the custom field.
     * @param string display    If added then 'block', if there's data already then 'none'. This is in 'repeatable-box-content' div class
     */
    function mwform_ppc_dealer(mw_options, mw_option, mw_data, res, position, disp) {
        var row_num = position === 'append' ? $(mw_options + ' ' + mw_option).length + 1 : position;
        var custom_field = 'mw-wp-form-generator[_' + row_num + '][ppc_custom_field]';
        var field_name = 'mw-wp-form-generator[_' + row_num + '][data][field_name]';
        var field_name_value = typeof res.data.field_name === 'undefined' ? '' : res.data.field_name;

        var html = '';
        html += '<div class="mw-wp-form-generator-form-option repeatable-box" data-field="mwform_textarea" style="display: block;">';
        html += '<div class="remove-btn ppc-remove-btn-' + mw_data.name + '"><b>×</b></div>';
        html += '<div class="open-btn ppc-open-close-btn-' + mw_data.name + '"><span>' + mw_data.label + '</span> ( 固定 )<b>▼</b></div>';
        html += '<div class="repeatable-box-content" style="display: ' + disp + ';">';
        html += '<span class="mwf_note">これは固定フィールドです。<br>例。<br><img style="width:100%" src="' + ppc_theme_dir + '/common/images/sample_dealer.png"></span>';
        html += '<span class="mwf_note">入力しなかった場合、このフォーム項目は表示されません。</span>';
        html += '<input type="hidden" name="' + custom_field + '" value="' + mw_data.name + '">';
        html += '<p>';
        html += '<strong>name<span class="mwf_require">*</span></strong>';
        html += '<span class="mwf_note">この名前は一意である必要があります。<br>他のフィールドにはこの名前を書かないでください。<br>英数字を入力してください。</span>';
        html += '<input type="text" data-label="' + mw_data.label + '" class="ppc_field_name" name="' + field_name + '" value="' + field_name_value + '" >';
        html += '<span class="mwf_note">例。dealer</span>';
        html += '</p>';
        html += '<!-- end .repeatable-box-content --></div>';
        html += '<!-- end .repeatable-box --></div>';

        // put the custom field in its specific position
        mwform_add_html(position, html, mw_options, mw_option);
    }

    /**
     * Just display the お取引店 （道銀）on the form
     *
     * @param string mw_options This is the class selector for appending the customized text.
     * @param string mw_options This is the class selector for the rows.
     * @param string mw_data    This is the custom field data. For admin use only.
     * @param string res        This is the user inputted data.
     * @param int    position   Position on where to put the custom field.
     * @param string display    If added then 'block', if there's data already then 'none'. This is in 'repeatable-box-content' div class
     */
    function mwform_ppc_dealer_dogin(mw_options, mw_option, mw_data, res, position, disp) {
        var row_num = position === 'append' ? $(mw_options + ' ' + mw_option).length + 1 : position;
        var custom_field = 'mw-wp-form-generator[_' + row_num + '][ppc_custom_field]';
        var field_name = 'mw-wp-form-generator[_' + row_num + '][data][field_name]';
        var field_name_value = typeof res.data.field_name === 'undefined' ? '' : res.data.field_name;

        var html = '';
        html += '<div class="mw-wp-form-generator-form-option repeatable-box" data-field="mwform_textarea" style="display: block;">';
        html += '<div class="remove-btn ppc-remove-btn-' + mw_data.name + '"><b>×</b></div>';
        html += '<div class="open-btn ppc-open-close-btn-' + mw_data.name + '"><span>' + mw_data.label + '</span> ( 固定 )<b>▼</b></div>';
        html += '<div class="repeatable-box-content" style="display: ' + disp + ';">';
        html += '<span class="mwf_note">これは固定フィールドです。<br>例。<br><img style="width:100%" src="' + ppc_theme_dir + '/common/images/sample_dealer_dogin.png"></span>';
        html += '<span class="mwf_note">入力しなかった場合、このフォーム項目は表示されません。</span>';
        html += '<input type="hidden" name="' + custom_field + '" value="' + mw_data.name + '">';
        html += '<p>';
        html += '<strong>name<span class="mwf_require">*</span></strong>';
        html += '<span class="mwf_note">この名前は一意である必要があります。<br>他のフィールドにはこの名前を書かないでください。<br>英数字を入力してください。</span>';
        html += '<input type="text" data-label="' + mw_data.label + '" class="ppc_field_name" name="' + field_name + '" value="' + field_name_value + '" >';
        html += '<span class="mwf_note">例。dealer</span>';
        html += '</p>';
        html += '<!-- end .repeatable-box-content --></div>';
        html += '<!-- end .repeatable-box --></div>';

        // put the custom field in its specific position
        mwform_add_html(position, html, mw_options, mw_option);
    }

    /**
     * Just display the 名 on the form
     *
     * @param string mw_options This is the class selector for appending the customized text.
     * @param string mw_options This is the class selector for the rows.
     * @param string mw_data    This is the custom field data. For admin use only.
     * @param string res        This is the user inputted data.
     * @param int    position   Position on where to put the custom field.
     * @param string display    If added then 'block', if there's data already then 'none'. This is in 'repeatable-box-content' div class
     */
    function mwform_ppc_participant_separator(mw_options, mw_option, mw_data, res, position, disp) {
        var row_num = position === 'append' ? $(mw_options + ' ' + mw_option).length + 1 : position;
        var custom_field = 'mw-wp-form-generator[_' + row_num + '][ppc_custom_field]';
        var field_name = 'mw-wp-form-generator[_' + row_num + '][data][field_name]';
        var field_name_value = typeof res.data.field_name === 'undefined' ? '' : res.data.field_name;

        var html = '';
        html += '<div class="mw-wp-form-generator-form-option repeatable-box" data-field="mwform_textarea" style="display: block;">';
        html += '<div class="remove-btn ppc-remove-btn-' + mw_data.name + '"><b>×</b></div>';
        html += '<div class="open-btn ppc-open-close-btn-' + mw_data.name + '"><span>' + mw_data.label + '</span> ( 固定 )<b>▼</b></div>';
        html += '<div class="repeatable-box-content" style="display: ' + disp + ';">';
        html += '<span class="mwf_note">これは固定フィールドです。';
        // html += '<span class="mwf_note">入力しなかった場合、このフォーム項目は表示されません。</span>';
        html += '<input type="hidden" name="' + custom_field + '" value="' + mw_data.name + '">';
        html += '<p>';
        html += '<strong>name<span class="mwf_require">*</span></strong>';
        html += '<span class="mwf_note">この名前は一意である必要があります。<br>他のフィールドにはこの名前を書かないでください。<br>数字を入力してください。</span>';
        html += 'separator_<input class="ppc_req" data-label="' + mw_data.label + '" type="number" min="1" max="" name="' + field_name + '" value="' + field_name_value + '" >';
        html += '<span class="mwf_note">例。separator_1</span>';
        html += '</p>';
        html += '<!-- end .repeatable-box-content --></div>';
        html += '<!-- end .repeatable-box --></div>';

        // put the custom field in its specific position
        mwform_add_html(position, html, mw_options, mw_option);
    }

    /**
     * To add the html. It also depends if there's
     * a specific position already. So if there's a
     * position the
     
     * @param string position   'append' or the num of the position.
     * @param string html       The html to append.
     * @param string mw_options Class on where to put.
     * @param string mw_option  Class on where to put.
     */
    function mwform_add_html(position, html, mw_options, mw_option) {
        if (position === 'append') {
            $(mw_options).append(html);
        } else {
            var final_position = position - 1;
            if (final_position < 0) {
                if ($(mw_options + ' ' + mw_option).length > 0) {
                    $(mw_options + ' ' + mw_option + ':first').before(html);
                } else {
                    $(mw_options).html(html);
                }
            } else {
                $(mw_options + ' ' + mw_option).eq(final_position).after(html);
            }
        }
    }

    /**
     * To display the custom fields in admin
     * page in mw wp form
     *
     * @param string mw_options This is just the class name from the mw wp form tag
     * @param string mw_option  This is just the class name from the mw wp form tag
     */
    function mwform_display_custom_field(mw_options, mw_option) {
        var customfields = $('input[name=ppc_application_custom_fields]').val();
        if (typeof customfields === 'undefined') {
            return
        }

        var json_parse = $.parseJSON(customfields);
        if (typeof json_parse === 'undefined') {
            return;
        }

        for (let i = 0; i < json_parse.length; i++) {
            const res = json_parse[i];

            var custom_field = res.name;
            custom_list[custom_field].method(mw_options, mw_option, custom_list[custom_field], res, res.position, 'none');
        }
    }

    /**
     * Select the style which is the
     * doginsoken. The customized
     * design for this.
     */
    function mwform_select_style() {
        $('#mw-wp-form_styles select').val('doginsoken')
    }

    /**
     * Get the name fields for
     * each generated form.
     */
    function get_name_fields() {
        var require_fields = [],
            input_name_val = [];

        $('.mw-wp-form-generator-form-options.ui-sortable .targetKey').each(function () {
            var name = $(this).attr('name');
            if (name.indexOf("[name]") < 0) {
                return;
            }
            var input = 'input[name="' + name + '"]';
            require_fields.push(input);
            input_name_val.push($(this).val());
        });

        return {
            'require_fields': require_fields,
            'input_name_val': input_name_val
        };
    }

    /**
     * To require name every field.
     * And to add the note for the name.
     * That it should not be empty and only alphanumeric.
     */
    function require_name() {
        var name_fields = get_name_fields();
        var require_fields = name_fields['require_fields'];

        for (let i = 0; i < require_fields.length; i++) {
            var note = '<span class="mwf_note">この名前は一意である必要があります。<br>他のフィールドにはこの名前を書かないでください。<br>英数字とアンダースコア「_」を入力してください。</span>';
            $(note).insertAfter(require_fields[i]);
        }

        $('#publish').on('click', function (e) {
            var name_fields = get_name_fields();
            var input_name_val = name_fields['input_name_val'];
            var require_fields = name_fields['require_fields'];
            var errfields = [],
                samefields = [];
            var cnt = require_fields.length;
            var tmp_input_name_val = input_name_val;

            for (let i = 0; i < cnt; i++) {
                const input_val = $(require_fields[i]).val();
                if (typeof input_val !== 'undefined') {
                    var disp_name = $(require_fields[i]).parent().prev().find('input').val();
                    // check if value is not empty
                    if (input_val.length < 1) {
                        errfields.push(disp_name + 'のnameを入力してください。');
                    }

                    // check the format. Only accepts alphanumeric and underscore
                    if (input_val.length > 0 && !isalphanum(input_val)) {
                        errfields.push(disp_name + 'のnameの形式ではありません。');
                    }

                    // check if it is unique.
                    // if (input_val.length > 0 && input_name_val.includes(input_val, i)) {
                    //     samefields.push(input_val);
                    // }
                }

            }

            /**
             * Require the fields for the name
             * of originally created fields or form tag.
             */
            $('.ppc_field_name').each(function () {
                var label = $(this).data('label');
                if ($(this).val() < 1) {
                    errfields.push(label + 'のnameを入力してください。');
                }
            });

            if (errfields.length > 0) {
                var err_msg = errfields.join('\n');
                alert(err_msg);
                return false;
            }

            var duplicates = find_duplicate_in_array(input_name_val);
            if (duplicates.length > 0) {
                var err_msg = duplicates.join('\n');
                alert(err_msg + '\n\n' + 'nameのフィールドを確認して変更してください。');
                return false;
            }



        });

    }

    /**
     * Check if value is alphanumeric.
     * Underscore is also accepted.
     *
     * @param string num
     */
    function isalphanum(num) {
        var regex = /^[a-zA-Z0-9_]+$/;
        var res = regex.exec(num) === null ? false : true;

        return res;
    }

    /**
     * To find duplicate in an array
     *
     * @param array arra1
     */
    function find_duplicate_in_array(arr) {
        var object = {};
        var result = [];

        arr.forEach(function (item) {
            if (!object[item])
                object[item] = 0;
            object[item] += 1;
        })

        for (var prop in object) {
            if (object[prop] >= 2) {
                result.push(prop + 'が重複しています。');
            }
        }

        return result;
    }

    /**
     * Since the default label in validation box
     * is just 必須項目（チェックボックス）.
     * But this is also applicable in radio so
     * it should be
     * 必須項目（チェックボックスかラジオボタン）
     */
    function add_validation_checkbox_label() {
        $('#mw-wp-form_validation .repeatable-boxes.ui-sortable .repeatable-box-content').each(function (e) {
            $checkbox = $(this).find('table tbody tr td label:nth-child(2)');
            var checkbox_html = $checkbox.html();
            var new_val = checkbox_html.replace('必須項目（チェックボックス）', '必須項目（チェックボックスかラジオボタン）');
            $checkbox.html(new_val);
        });
    }
});