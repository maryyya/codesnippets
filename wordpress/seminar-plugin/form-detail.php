<?php

/**
 * お申し込みデータ詳細
 */
class Form_Detail
{
    /**
     * Plugin path
     *
     * @var string
     */
    public $plugin_path;

    /**
     * Plugin url
     *
     * @var string
     */
    public $plugin_url;

    /**
     * Common function helper
     *
     * @var object
     */
    public $helper;

    /**
     * Constructor
     *
     * @param object $helper_obj
     * @param string $plugin_path
     * @param string $plugin_url
     */
    public function __construct($helper_obj, $plugin_path_param, $plugin_url_param)
    {
        $this->helper = $helper_obj;
        $this->plugin_path = $plugin_path_param;
        $this->plugin_url = $plugin_url_param;
    }

    /**
     * お申し込みデータの詳細ページ
     *
     * @return
     */
    public function ppc_admin_form_detail_data()
    {
        wp_enqueue_style('ppc-wp-admin-formdata', $this->plugin_url . 'assets/css/formdata.css', array(), false, 'all');

        $application_id = !empty($_GET['application_id']) ? $_GET['application_id'] : -1;
        $res = $this->get_data($application_id);
        require_once $this->plugin_path . 'template/form-detail.php';
    }

    /**
     * Get お申し込みデータ
     *
     * @param int     $application_id
     * @return object $res
     */
    private function get_data($application_id)
    {
        global $wpdb;
        $sql = <<<SQL
        SELECT
            application.seminar_id,
            application.form_id,
            participant.seminar_code,
            participant.ID AS participant_id,
            application.post_date,
            application.amount,
            application.data AS application_data,
            participant.data AS participant_data
        FROM
            {$wpdb->prefix}seminar_participant participant
                RIGHT JOIN
            {$wpdb->prefix}seminar_application application ON participant.application_id = application.ID
        WHERE
            application.ID = %d
        GROUP BY application.seminar_id
        ORDER BY participant.ID;
        SQL;

        $prepare = $wpdb->prepare($sql, [$application_id]);
        $res = $wpdb->get_results($prepare);
        if (empty($res)) {
            return ['data' => [], 'header' => []];
        }

        $form_fields  = $this->get_form_fields($res[0]->form_id);
        $seminar_code = get_field('ppc_seminar_code', $res[0]->seminar_id);

        $data = [];
        foreach ($res as $key => $val) {
            $application_data = json_decode($val->application_data, JSON_UNESCAPED_UNICODE);
            $participant_data = json_decode($val->participant_data, JSON_UNESCAPED_UNICODE);

            $data[$key]['seminar_id'] = $res[0]->seminar_id;

            // total amount for the form
            $data[$key]['amount'] = $res[0]->amount;

            // セミナーコード
            $data[$key]['セミナーコード'] = $seminar_code;

            // 申込日
            $data[$key]['申込日'] = date('Y年m月d日　H時i分', strtotime($val->post_date));

            // お申し込みフォームデータ
            unset($application_data['send']);
            unset($application_data['mw_wp_form_token']);
            unset($application_data['_wp_http_referer']);
            unset($application_data['mw-wp-form-form-id']);
            unset($application_data['mw-wp-form-form-verify-token']);
            unset($application_data['seminar_id']);
            unset($application_data['mwf_upload_files']);
            foreach ($application_data as $app_key => $app_val) {
                if (strpos($app_key, 'separator') !== false) {
                    continue;
                }

                if (!is_array($app_val)) {

                    // type
                    if ($app_key === 'type') {
                        $data[$key]['種別'] = ppc_get_seminar_type($app_val);
                        continue;
                    }

                    $label = isset($form_fields[$app_key]) ? $form_fields[$app_key] : $app_val;
                    $data[$key][$label] = $app_val;
                    continue;
                }


                // tel
                if ($app_key === 'tel') {
                    $data[$key]['電話番号'] = $app_val['data'];
                }

                // fax
                if ($app_key === 'fax') {
                    $data[$key]['FAX番号'] = $app_val['data'];
                }
            }
        }

        return $data;
    }

    /**
     * To get the label for the csv
     */
    private function get_form_fields($form_id)
    {
        $fields = [];
        $mw_fields = get_post_meta($form_id, 'mw-wp-form-generator', true);
        $custom_fields = get_post_meta($form_id, 'ppc_application_custom_fields', true);

        foreach ($mw_fields as $input_type) {
            foreach ($input_type as $val) {
                // if no number in the name
                if (preg_match('/\w+_*\d+/', $val['name']) !== 1) {
                    $fields[$val['name']] = $val['mw-wp-form-generator-display-name'];
                    continue;
                }

                // if no number
                if (preg_match('/\d+/', $val['name'], $match) !== 1) {
                    continue;
                }

                // if no match
                if (!isset($match[0])) {
                    continue;
                }

                $fields[$val['name']] = $val['mw-wp-form-generator-display-name'] . '_' . $match[0];
            }
        }

        // default
        $fields['ppc_sei_applicant'] = '連絡姓';
        $fields['ppc_mei_applicant'] = '連絡名';
        $fields['ppc_sei_kana_applicant'] = '連絡セイ';
        $fields['ppc_mei_kana_applicant'] = '連絡メイ';

        foreach ($custom_fields as $val) {
            // 参加者
            if (strpos($val['name'], 'participant') !== false) {
                $field_name = $val['data']['field_name'];
                $fields['ppc_sei_participant_name_' . $field_name]      = '参加者姓' . $field_name;
                $fields['ppc_mei_participant_name_' . $field_name]      = '参加者名' . $field_name;
                $fields['ppc_sei_kana_participant_name_' . $field_name] = '参加者セイ' . $field_name;
                $fields['ppc_mei_kana_participant_name_' . $field_name] = '参加者メイ' . $field_name;
            }

            // 住所
            if (strpos($val['name'], 'place') !== false) {
                $field_name = $val['data']['field_name'];
                $fields['ppc_zip_' . $field_name]       = '郵便番号';
                $fields['ppc_pref_' . $field_name]      = '都道府県';
                $fields['ppc_municipal_' . $field_name] = '市町村名';
                $fields['ppc_building_' . $field_name]  = '番地・建物名';
            }

            // お取引店
            if (strpos($val['name'], 'dealer') !== false) {
                $field_name = $val['data']['field_name'];
                $fields['ppc_hokuriku_' . $field_name] = '北海道銀行　支店名';
                $fields['ppc_hokkaido_' . $field_name] = '北陸銀行　支店名';
            }

            // 備考
            if (
                strpos($val['name'], 'remarks') !== false
                || strpos($val['name'], 'participant_textarea') !== false
                || strpos($val['name'], 'biko') !== false
            ) {
                $field_name = $val['data']['field_name'];
                $fields[$field_name] = '備考';
            }
        }

        return $fields;
    }
}
