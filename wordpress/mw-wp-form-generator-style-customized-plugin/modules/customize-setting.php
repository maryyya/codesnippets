<?php

/**
 * Just include the mail
 */
require_once 'mail.php';

/**
 * To add some custom
 * setting into the
 * お申し込み forms
 */
class Customize_setting extends Mail
{
    /**
     * This id is the お問い合わせID
     * The PPC_CONTACT_FORM_ID is in
     * wp-config.php
     */
    const EXCLUDE_ID = PPC_CONTACT_FORM_ID;

    /**
     * This will customize validation,
     * error message, mail forms.
     * All of the mw wp forms.
     */
    public function __construct()
    {
        $mw_posts = get_posts([
            'numberposts' => -1,
            'post_type'   => 'mw-wp-form',
            'exclude'     => self::EXCLUDE_ID,
            'post_status' => 'publish',
            'fields'      => 'ids',
        ]);

        foreach ($mw_posts as $id) {
            // add validation
            add_filter(
                'mwform_validation_mw-wp-form-' . $id,
                [$this, 'set_rule'],
                10,
                3
            );

            // set error messages
            add_filter(
                'mwform_error_message_mw-wp-form-' . $id,
                [$this, 'set_error_messages'],
                10,
                3
            );

            // set mail
            add_filter(
                'mwform_mail_mw-wp-form-' . $id,
                [$this, 'send_mail_main'],
                10,
                2
            );
        }
    }

    /**
     * Set validation rule.
     * ppc_get_validation_rules() is in themes/doginsoken/seminar.php
     *
     * @param object $Validation default validation data setting
     * @param array  $data       no use for this function
     * @param array  $Data       no use for this function
     */
    public function set_rule($Validation, $data, $Data)
    {
        $form_key = str_replace('mw-wp-form-', '', $Data->get_form_key());
        $rules = ppc_get_validation_rules($form_key);
        $custom_fields = get_post_meta($form_key, 'ppc_application_custom_fields', true);
        $participant_req = [];

        foreach ($rules as $key => $val) {
            // only for the 名前
            // 参加者２人以上のフォームで全員登録しない時　のリクエスト
            if (strpos($val['target'], 'participant') === false) {
                continue;
            }

            // 必須
            if (!isset($val['noempty'])) {
                continue;
            }

            if (strpos($val['target'], 'participant_name') !== false) {
                $participant_req[] = $val['target'];
            }

            $Validation->set_rule('ppc_sei_' . $val['target'], 'noempty');
            $Validation->set_rule('ppc_mei_' . $val['target'], 'noempty');
            $Validation->set_rule('ppc_sei_kana_' . $val['target'], 'noempty');
            $Validation->set_rule('ppc_sei_kana_' . $val['target'], 'katakana');
            $Validation->set_rule('ppc_mei_kana_' . $val['target'], 'noempty');
            $Validation->set_rule('ppc_mei_kana_' . $val['target'], 'katakana');
        }

        // 連絡者。固定
        $Validation->set_rule('ppc_sei_applicant', 'noempty');
        $Validation->set_rule('ppc_mei_applicant', 'noempty');
        $Validation->set_rule('ppc_sei_kana_applicant', 'noempty');
        $Validation->set_rule('ppc_sei_kana_applicant', 'katakana');
        $Validation->set_rule('ppc_mei_kana_applicant', 'noempty');
        $Validation->set_rule('ppc_mei_kana_applicant', 'katakana');

        // 住所。固定
        $Validation->set_rule('ppc_zip_address_1', 'zip');
        $Validation->set_rule('ppc_zip_address_1', 'noempty');
        $Validation->set_rule('ppc_pref_address_1', 'noempty');
        $Validation->set_rule('ppc_municipal_address_1', 'noempty');
        $Validation->set_rule('ppc_building_address_1', 'noempty');

        // echo '<pre>';
        // 参加者フォームで必須じゃないときに片方だけ入れたらエラーにしたい
        foreach ($custom_fields as $val) {
            if (strpos($val['name'], 'participant') === false) {
                continue;
            }

            $field_name = $val['data']['field_name'];

            // do not include required fields already
            if (in_array('participant_name_' . $field_name, $participant_req)) {
                continue;
            }

            $Validation->set_rule('ppc_sei_kana_' . $field_name, 'katakana');
            $Validation->set_rule('ppc_mei_kana_' . $field_name, 'katakana');

            $sei = 'ppc_sei_participant_name_' . $field_name;
            $mei = 'ppc_mei_participant_name_' . $field_name;
            $sei_kana = 'ppc_sei_kana_participant_name_' . $field_name;
            $mei_kana = 'ppc_mei_kana_participant_name_' . $field_name;

            if ((isset($data[$sei]) && strlen($data[$sei]) > 0) &&
                (empty($data[$mei]) || empty($data[$sei_kana]) || empty($data[$mei_kana]))
            ) {
                $Validation->set_rule($mei, 'noempty');
                $Validation->set_rule($sei_kana, 'noempty');
                $Validation->set_rule($mei_kana, 'noempty');
            }

            if ((isset($data[$mei]) && strlen($data[$mei]) > 0) &&
                (empty($data[$sei]) || empty($data[$sei_kana]) || empty($data[$mei_kana]))
            ) {
                $Validation->set_rule($sei, 'noempty');
                $Validation->set_rule($sei_kana, 'noempty');
                $Validation->set_rule($mei_kana, 'noempty');
            }

            if ((isset($data[$sei_kana]) && strlen($data[$sei_kana]) > 0) &&
                (empty($data[$mei]) || empty($data[$sei]) || empty($data[$mei_kana]))
            ) {
                $Validation->set_rule($mei, 'noempty');
                $Validation->set_rule($sei, 'noempty');
                $Validation->set_rule($mei_kana, 'noempty');
            }

            if ((isset($data[$mei_kana]) && strlen($data[$mei_kana]) > 0) &&
                (empty($data[$mei]) || empty($data[$sei]) || empty($data[$sei_kana]))
            ) {
                $Validation->set_rule($mei, 'noempty');
                $Validation->set_rule($sei, 'noempty');
                $Validation->set_rule($sei_kana, 'noempty');
            }
        }

        return $Validation;
    }

    /**
     * Set error messages
     *
     * @param string  $error error message
     * @param string  $key   name of the field
     * @param string  $rule  rule of the field
     * @return string        error message
     */
    public function set_error_messages($error, $key, $rule)
    {
        // 必要なルール
        if ($rule === 'noempty' && $key === 'ppc_prefecture') {
            return '選択してください。';
        }

        // 必要なルール
        if ($rule === 'noempty') {
            return '入力してください。';
        }

        return $error;
    }
}
