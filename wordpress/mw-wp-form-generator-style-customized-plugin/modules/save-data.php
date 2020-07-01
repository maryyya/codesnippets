<?php

/**
 * This is to save the data
 */
require_once 'create-invoice.php';

/**
 * To save the input data
 */
class Save_data extends Create_invoice
{
    /**
     * Save the application data.
     * And if there's no error in saving
     * the data, then this will also
     * return the invoice key which
     * will be used in the invoice url
     *
     * @param array $data        input data
     * @param int   $seminar_id  seminar id or 投稿ID
     * @param int   $term        カテゴリーデータ
     * @param int   $form_id     お申し込みID or 投稿ID
     * @param array $participant 参加者
     * @param array $invoice_key 請求書キー
     * @return boolean|string    false if there's error in saving the data or string which is the invoice key
     */
    protected function save_application_data($data, $seminar_id, $term, $form_id, $participants, $invoice_key)
    {
        global $wpdb;

        $sql = <<<SQL
            INSERT INTO {$wpdb->prefix}seminar_application
            (seminar_id, term_id, form_id, invoice_key, data, amount, post_date)
            VALUES (%d, %d, %d, %s, %s, %s, %s)
        SQL;

        $amount = $this->calculate_invoice($seminar_id, $data['type'], $participants);

        $param = [
            $seminar_id,    // セミナーID
            $term->term_id, // カテゴリーID
            $form_id,       // お申し込みID
            $invoice_key,   // 請求書キー
            json_encode($data, JSON_UNESCAPED_UNICODE), // input data
            $amount, // total amount
            date('Y-m-d H:i:s'),
        ];

        try {
            $prepare = $wpdb->prepare($sql, $param);
            $insert = $wpdb->query($prepare);
            if ($insert !== 1) {
                ppc_error_log([
                    'msg'   => 'Error inserting お申し込み data. This happened after submitting the お申し込みフォーム, and before sending the mail.',
                    'file'  => __FILE__,
                    'line'  => __LINE__,
                    'sql'   => $sql,
                    'param' => json_encode($param, JSON_UNESCAPED_UNICODE),
                ]);

                return false;
            }

            // error in saving the participant's data
            if (!$this->save_participant_data($data, $seminar_id, $form_id)) {
                return false;
            }

            return $invoice_key;
        } catch (\Exception $e) {
            ppc_error_log([
                'msg'   => 'Error inserting お申し込み data. This happened after submitting the お申し込みフォーム, and before sending the mail. This is the error:' . $e->getMessage(),
                'file'  => __FILE__,
                'line'  => __LINE__,
                'sql'   => $sql,
                'param' => json_encode($param, JSON_UNESCAPED_UNICODE),
            ]);
            return false;
        }
    }

    /**
     * 参加者のデータ
     * Get the participant's content
     *
     * @param  array         $data         Input data or post data
     * @return array|boolean $participants Only get the participants data or false if empty participants
     */
    protected function get_participants($data)
    {
        $tmp_participants = [];
        foreach ($data as $key => $val) {
            if (strpos($key, 'participant') !== 0 && !strpos($key, 'participant')) {
                continue;
            }

            $num = substr($key, -1);
            $tmp_participants[$num] = '';
        }

        $participants = [];

        if (empty($tmp_participants)) {
            return false;
        }

        foreach ($tmp_participants as $key => $val) {
            // セイ
            if (strlen($data['ppc_sei_participant_name_' . $key]) < 1) {
                continue;
            }

            // メイ
            if (strlen($data['ppc_mei_participant_name_' . $key]) < 1) {
                continue;
            }

            // セイカナ
            if (strlen($data['ppc_sei_kana_participant_name_' . $key]) < 1) {
                continue;
            }

            // メイカナ
            if (strlen($data['ppc_mei_kana_participant_name_' . $key]) < 1) {
                continue;
            }

            $participants[] = [
                'sei'      => $data['ppc_sei_participant_name_' . $key], // 姓
                'mei'      => $data['ppc_mei_participant_name_' . $key], // 名
                'sei_kana' => $data['ppc_sei_kana_participant_name_' . $key], // セイかな
                'mei_kana' => $data['ppc_mei_kana_participant_name_' . $key], // 名かな
                // MOD 2020.02.10
                //                'mail'     => $data['participant_mail_' . $key],         // メールアドレス
                //                'title'    => $data['participant_title_' . $key],        // お役職
                'mail'     => isset($data['participant_mail_' . $key]) && strlen($data['participant_mail_' . $key]) > 0 ? $data['participant_mail_' . $key] : '',
                'title'    => isset($data['participant_title_' . $key]) && strlen($data['participant_title_' . $key]) > 0 ? $data['participant_title_' . $key] : '',
            ];
        }

        return $participants;
    }

    /**
     * 参加者
     *
     * Save the participant data
     *
     * @param  array   $data       post input data
     * @param  int     $seminar_id セミナーID
     * @param  int     $form_id    お申し込みフォームID
     * @return boolean             true if insert data is successful else, false
     */
    private function save_participant_data($data, $seminar_id, $form_id)
    {
        global $wpdb;
        $participants = $this->get_participants($data);
        if (empty($participants)) {
            ppc_error_log([
                'msg'   => '参加者なし. This happened after submitting the お申し込みフォーム, and before sending the mail.',
                'file'  => __FILE__,
                'line'  => __LINE__,
            ]);
            return false;
        }

        $tmp_sql = [];
        $param   = [];
        $last_application_id = $this->get_last_application_id();
        $last_participant_id = $this->get_last_participant_id($seminar_id, $form_id);
        foreach ($participants as $key => $val) {
            $cnt = ($key + 1) + $last_participant_id;
            $no = $key + 1;
            $tmp_sql[] = '(%d, %d, %d, %s, %s)';
            $param[] = $last_application_id;
            $param[] = $cnt;
            $param[] = $no;
            $param[] = json_encode($val, JSON_UNESCAPED_UNICODE);
            $param[] = date('Y-m-d H:i:s');
        }

        $imploded_sql = implode(",\n", $tmp_sql);
        $sql = <<<SQL
        INSERT INTO {$wpdb->prefix}seminar_participant
        (application_id, seminar_code, no, data, post_date)
        VALUES
        {$imploded_sql};
        SQL;

        try {
            $prepare = $wpdb->prepare($sql, $param);
            $action = $wpdb->query($prepare);
            if ($action < 1 || !is_int($action)) {
                ppc_error_log([
                    'msg'   => 'Error inserting 参加者 data. This happened after submitting the お申し込みフォーム, and before sending the mail.',
                    'file'  => __FILE__,
                    'line'  => __LINE__,
                    'sql'   => $sql,
                    'param' => json_encode($param, JSON_UNESCAPED_UNICODE),
                ]);
                return false;
            }
            return true;
        } catch (\Exception $e) {
            ppc_error_log([
                'msg'   => 'Error inserting 参加者 data. This happened after submitting the お申し込みフォーム, and before sending the mail. This is the error:' . $e->getMessage(),
                'file'  => __FILE__,
                'line'  => __LINE__,
                'sql'   => $sql,
                'param' => json_encode($param, JSON_UNESCAPED_UNICODE),
            ]);
            return false;
        }
    }

    /**
     * This is to get the last
     * application id
     *
     * @return int application id
     */
    protected function get_last_application_id()
    {
        global $wpdb;

        $sql = <<<SQL
        SELECT MAX(ID) as application_id from {$wpdb->prefix}seminar_application;
        SQL;
        $res = $wpdb->get_results($sql);
        return empty($res[0]->application_id) ? 0 : (int) $res[0]->application_id;
    }

    /**
     * This is to get the last
     * application id
     *
     * @return int participant_id
     */
    protected function get_last_participant_id($seminar_id, $form_id)
    {
        global $wpdb;

        $sql = <<<SQL
        SELECT
            MAX(participant.seminar_code) AS participant_order
        FROM
            {$wpdb->prefix}seminar_participant participant
        INNER JOIN
            {$wpdb->prefix}seminar_application application ON participant.application_id = application.ID
        AND application.seminar_id = %d and application.form_id = %d
        SQL;
        $prepare = $wpdb->prepare($sql, [$seminar_id, $form_id]);
        $res = $wpdb->get_results($prepare);
        return empty($res[0]->participant_order) ? 0 : (int) $res[0]->participant_order;
    }
}
