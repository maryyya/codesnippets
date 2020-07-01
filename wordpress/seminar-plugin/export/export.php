<?php

/**
 * Export data
 */
class Export
{
    /**
     * Encoding for the csv.
     * The first one.
     */
    const FIRST_ENCODING_TYPE = 'SJIS-win';

    /**
     * Encoding for the csv.
     * The second one.
     */
    const SECOND_ENCODING_TYPE = 'UTF-8';

    /**
     * Common function helper
     *
     * @var object
     */
    public $helper;

    /**
     * Temporary csv パス
     *
     * @var string
     */
    private $csv_path;

    /**
     * Constructor
     */
    public function __construct($helper_obj, $data = [])
    {
        setlocale(LC_ALL, 'ja_JP.UTF-8');
        $post = $_POST;
        $type = '';
        if (isset($post['type'])) {
            $type = $post['type'];
        } elseif (isset($post['sankasha'])) {
            $type = 'sankasha';
        } elseif (isset($post['seikyuyo'])) {
            $type = 'seikyuyo';
        } elseif (isset($post['keiriyo'])) {
            $type = 'keiriyo';
        }

        $this->helper = $helper_obj;

        $this->create_applicant_csv($type, $post, $data);
    }

    /**
     * Create temporary csv first
     *
     * @param int $seminar_id
     * @return
     */
    private function create_applicant_csv($type, $post, $db_data)
    {

        $data = [];
        $header = [];
        switch ($type) {
                // 各参加者リスト
            case 'seminar_list':
                // check if empty
                if (empty($post['seminar_id'])) {
                    return;
                }

                if ((int) $post['seminar_id'] < 1) {
                    return;
                }

                $seminar_id     = $post['seminar_id'];
                $res            = $this->get_seminar_list_data($seminar_id);
                $data           = $res['data'];
                $header         = $res['header'];
                $this->csv_path = get_the_title($seminar_id) . '.csv';
                break;

                // 全参加者リスト
            case 'sankasha':
                $res            = $this->get_sankasha_data($db_data);
                $data           = $res['data'];
                $header         = $res['header'];
                $this->csv_path = '参加者リスト.csv';
                break;

                // 請求用
            case 'seikyuyo':
                $res            = $this->get_seikyuyo_data($db_data);
                $data           = $res['data'];
                $header         = $res['header'];
                $this->csv_path = '請求用.csv';
                break;

                // 経理用
            case 'keiriyo':
                $res            = $this->get_keiriyo_data($db_data);
                $data           = $res['data'];
                $header         = $res['header'];
                $this->csv_path = '経理用.csv';
                break;

            default:
                # code...
                break;
        }

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=" . $this->csv_path);

        // to create a temporary csv file
        $handle = fopen('php://output', 'w');
        if ($handle !== false) {

            // first is the header
            fputcsv($handle, $header);

            // the proceed with writing the data from the db
            foreach ($data as $key => $items) {
                $final_data = [];
                foreach ($items as &$val) {
                    $final_data[] = $this->convert_encoding($val);
                }
                fputcsv($handle, $final_data);
            }
        }

        fpassthru($handle);
        exit;
    }

    /**
     * Get data. There's a functionality that has
     * a separator(区切り). This will determine what to
     * output on the 参加者.
     *
     * @param  int   $seminar_id セミナーID or 投稿ID
     * @return array             contains header and db data
     */
    private function get_seminar_list_data($seminar_id)
    {
        global $wpdb;
        $participant_data = $common_data = $tmp_data = $separator = $data = [];

        // お申し込みフォームデータ
        $form_id       = get_field('application_form_id', $seminar_id);  // お申し込みフォームID
        $form_fields   = $this->get_form_fields($form_id);               // with no separator
        $form_fields_2 = $this->get_form_fields($form_id, 2);            // with separator

        // セミナーコード
        $seminar_code = get_field('ppc_seminar_code', $seminar_id);

        $sql = <<<SQL
        SELECT
            *
        FROM
            {$wpdb->prefix}seminar_application
        WHERE
            seminar_id = %d
            AND form_id = %d
        ORDER BY post_date
        SQL;

        $prepare = $wpdb->prepare($sql, [$seminar_id, $form_id]);
        $res = $wpdb->get_results($prepare);
        if (empty($res)) {
            return ['data' => [], 'header' => []];
        }

        foreach ($res as $key => $val) {
            // お申し込みフォームデータ
            $application_data = json_decode($val->data, JSON_UNESCAPED_UNICODE);

            // unset unused or unnecessary data. These are all default data
            unset($application_data['send']);
            unset($application_data['mw_wp_form_token']);
            unset($application_data['_wp_http_referer']);
            unset($application_data['mw-wp-form-form-id']);
            unset($application_data['mw-wp-form-form-verify-token']);
            unset($application_data['seminar_id']);
            unset($application_data['mwf_upload_files']);
            unset($application_data['__children']);
            unset($application_data['checkbox']);

            $separator = 0;
            foreach ($application_data as $app_key => $app_val) {
                // check if with separator
                if (strpos($app_key, '_separator') !== false) {
                    $separator = $app_val;
                }

                // for the common data or the 連絡者データ
                if ($separator < 1) {
                    $common_data[$key]['セミナーコード'] = $seminar_code;
                    $common_data[$key]['No'] = $key + 1;
                    $common_data[$key]['受講票No'] = 1;

                    if ($app_key === 'type') {
                        $common_data[$key]['種別'] = ppc_get_seminar_type($app_val);
                        continue;
                    }

                    // tel
                    if ($app_key === 'tel') {
                        $common_data[$key]['電話番号'] = $app_val['data'];
                        continue;
                    }

                    // fax
                    if ($app_key === 'fax') {
                        $common_data[$key]['FAX番号'] = $app_val['data'];
                        continue;
                    }

                    // assign the label from the fields
                    $label = isset($form_fields[$app_key]) ? $form_fields[$app_key] : $app_key;
                    // if value is array then get the data value
                    if (is_array($app_val)) {
                        $common_data[$key][$label] = $app_val['data'];
                        continue;
                    }

                    $common_data[$key][$label] = $app_val;
                    continue;
                }

                // do not include the ppc_participant_separator_{$i} in inserting the data
                if ($app_key === 'ppc_participant_separator_' . $separator) {
                    continue;
                }

                // with separator data
                $label = isset($form_fields_2[$app_key]) ? $form_fields_2[$app_key] : $app_key;

                // if value is array
                if (is_array($app_val)) {
                    $participant_data[$key][$separator][$label] = $app_val['data'];
                    continue;
                }

                // do not include empty value
                if (empty($app_val)) {
                    continue;
                }

                $participant_data[$key][$separator]['jukohyou_no'] = $separator;
                $participant_data[$key][$separator][$label] = $app_val;
            }
        }

        if (empty($participant_data)) {
            $data = $common_data;
        } else {
            // Organize the data. Combine 連絡者データ and 参加者データ
            foreach ($participant_data as $key => $participants) {
                foreach ($participants as $participant_no => $val) {
                    unset($common_data[$key]['セミナーコード']);
                    unset($common_data[$key]['No']);
                    unset($common_data[$key]['受講票No']);
                    $tmp_data[$key . $participant_no] = array_merge($common_data[$key], $val);
                }
            }

            // 基本情報
            foreach (array_values($tmp_data) as $key => $val) {
                $basic_data = [
                    'セミナーコード' => $seminar_code,
                    'No'            => $key + 1,
                    '受講票No'      => $val['jukohyou_no']
                ];
                unset($val['jukohyou_no']);
                $data[$key] = array_merge($basic_data, $val);
            }
        }

        if (empty($data)) {
            return ['data' => [], 'header' => []];
        }

        return ['data' => $data, 'header' => $this->convert_encoding(array_keys($data[0]))];
    }

    /**
     * 全参加者リスト
     *
     * @param  array $db_data db data according to search
     * @return array          data and header. data prepared for csv output
     */
    private function get_sankasha_data($db_data)
    {
        $header = [
            'セミナーコード',
            '受講票No',
            'No',
            '申込日',
            '会社名',
            '会社名フリガナ',
            '会員種別',
            '業種',
            '北海道銀行　支店名',
            '北陸銀行　　支店名',
            '参加者部署',
            '参加者',
            '参加者フリガナ',
            '受講料',
            '連絡担当者',
            '役職（連絡担当者）',
            '郵便番号',
            '住所',
            '電話',
            'FAX',
            'メールアドレス',
            'コース',
            '開催日',
            '性別',
            '年齢',
            '特記事項',
        ];

        $data = [];
        foreach ($db_data as $key => $val) {
            $no = $key + 1;
            $type = ppc_get_seminar_type($val->type);
            $tuition_fee = $type === '一般' ? $val->general_fee : $val->member_fee;

            // TEL
            $tel = '';
            if (!empty($val->tel)) {
                $tmp_tel = json_decode($val->tel);
                if (!empty($tmp_tel->data)) {
                    $tel = $tmp_tel->data;
                }
            }

            // FAX
            $fax = '';
            if (!empty($val->fax)) {
                $tmp_fax = json_decode($val->fax);
                if (!empty($tmp_fax->data)) {
                    $fax = $tmp_fax->data;
                }
            }

            $json_data = json_decode($val->application_data, JSON_UNESCAPED_UNICODE);
            $participant_data = $this->get_participants_detail($json_data);
            $application_data = $this->get_application_data($json_data);

            // 性別
            $gender = isset($participant_data['gender_' . $val->participant_no]) ? $participant_data['gender_' . $val->participant_no] : '';

            // 年齢
            $age = isset($participant_data['age_' . $val->participant_no]) ? $participant_data['age_' . $val->participant_no] : '';

            // 部署・役職
            $busho = isset($participant_data['busho_' . $val->participant_no]) ? $participant_data['busho_' . $val->participant_no] : '';

            // 連絡部署・役職
            $renbusho = isset($val->renbusho) ? $val->renbusho : (isset($val->applicant_title) ? $val->applicant_title : '');

            $data[$key] = [
                $val->ppc_seminar_code,                                         // セミナーコード
                $val->seminar_code,                                             // 受講票No
                $no,                                                            // 順番
                ppc_get_seminar_date($val->post_date),                          // 申込日
                $val->company_name,                                             // 会社名
                $val->company_name_kana,                                        // 会社名カナ
                $type,                                                          // 種別
                $val->gyoshu,                                                   // 業種
                $val->ppc_hokuriku_dealer,                                      // 北海道銀行　支店名
                $val->ppc_hokkaido_dealer,                                      // 北陸銀行　　支店名
                $busho,                                                         // 部署・役職
                $val->participant_sei . ' ' . $val->participant_mei,            // 参加者
                $val->participant_sei_kana . ' ' . $val->participant_mei_kana,  // 参加者フリガナ
                number_format($tuition_fee),                                    // 受講料
                $val->applicant_sei . ' ' . $val->applicant_mei,                // 連絡担当者
                $renbusho,                                                      // 役職（連絡担当者）
                $application_data['zip'],                                       // 郵便番号
                $application_data['address'],                                   // 住所
                $tel,                                                           // 電話
                $fax,                                                           // FAX
                isset($val->applicant_mail) ? $val->applicant_mail : '',        // 連絡者メールアドレス
                $val->post_title,                                               // コース
                ppc_get_seminar_date($val->seminar_start, $val->seminar_end),   // 開催日
                $gender, // 性別
                $age,    // 年齢
                $application_data['remarks'], // 特記事項
            ];
        }

        return ['data' => $data, 'header' => $this->convert_encoding($header)];
    }

    /**
     * 請求用CSV
     *
     * @param  array $db_data db data according to search
     * @return array          data and header. data prepared for csv output
     */
    private function get_seikyuyo_data($db_data)
    {
        $header = [
            'セミナーコード',
            '受講票No',
            'No',
            '会社名',
            '会社名カナ',
            '連絡担当者',
            '役職（連絡担当者）',
            '郵便番号',
            '住所',
            '申込人数',
            '単価',
            '料金',
            '開催日'
        ];

        $data = [];
        foreach ($db_data as $key => $val) {
            $jukouryou = get_field('jukouryou', $val->seminar_id);
            if ($jukouryou === 'free') {
                continue;
            }

            // 順序
            $no = $key + 1;

            // 受講料
            $type = ppc_get_seminar_type($val->type);

            // 単価
            $tuition_fee = $type === '一般' ? $val->general_fee : $val->member_fee;

            // お申し込みデータ
            $json_data = json_decode($val->application_data, JSON_UNESCAPED_UNICODE);
            $application_data = $this->get_application_data($json_data);

            // 連絡部署・役職
            $renbusho = isset($val->renbusho) ? $val->renbusho : (isset($val->applicant_title) ? $val->applicant_title : '');

            // 開催日
            $seminar_start = date('Y年m月d日', strtotime($val->seminar_start)) . '（' . ppc_get_japanese_day($val->seminar_start) . '）';

            $data[$key] = [
                $val->ppc_seminar_code,                                     // セミナーコード
                $val->seminar_code,                                         // 受講票No`
                $val->application_id,                                       // No
                $val->company_name,                                         // 会社名
                $val->company_name_kana,                                    // 会社名カナ
                $val->applicant_sei . ' ' . $val->applicant_mei,            // 連絡担当者
                $renbusho,                                                  // 役職（連絡担当者）
                $application_data['zip'],                                   // 郵便番号
                $application_data['address'],                               // 住所
                $val->participant_total,                                    // 申込人数
                number_format($tuition_fee),                                // 単価
                number_format($val->total_amount),                          // 料金
                $seminar_start,                                             // 開催日
            ];
        }

        return ['data' => $data, 'header' => $this->convert_encoding($header)];
    }

    /**
     * 経理用CSV
     *
     * @param  array $db_data db data according to search
     * @return array          data and header. data prepared for csv output
     */
    private function get_keiriyo_data($db_data)
    {
        $header = [
            '識別フラグ',        // 静的
            '伝票No.',          // 静的
            '決算',             // 静的
            '月末最終営業日',   // 動的
            '借方勘定科目',     // 静的
            '借方補助科目',     // 動的
            '借方部門',         // 静的
            '借方税区分',       // 静的
            '借方金額',         // 動的
            '借方税金額',       // 静的
            '貸方勘定科目',     // 静的
            '貸方補助科目',     // 静的
            '貸方部門',         // 動的
            '貸方税区分',       // 静的
            '貸方金額',         // 動的
            '貸方税金額',       // 静的
            '摘要',             // 動的
            '番号',             // 静的
            '期間',             // 静的
            'タイプ',           // 静的
            '生成元',           // 静的
            '仕訳メモ',         // 静的
            '付箋1',            // 静的
            '付箋2',            // 静的
            '調整',             // 静的
        ];

        $data = [];
        foreach ($db_data as $key => $val) {
            $jukouryou = get_field('jukouryou', $val->seminar_id);
            if ($jukouryou === 'free') {
                continue;
            }

            // 摘要
            $tekiyo = $val->ppc_seminar_code . '　' . date('ymd', strtotime($val->post_date));
            switch ($val->term_slug) {
                    // らいらっく
                case 'lilac':
                    // 開催地
                    $tmp_city = get_field('lilac-city', $val->seminar_id);
                    $city = empty($tmp_city) ? '' : mb_substr($tmp_city['label'], 0, 1);

                    // 参加対象者
                    $tmp_taishosha = get_field('lilac-taishosha', $val->seminar_id);
                    $taishosha = empty($tmp_taishosha) ? '' : $tmp_taishosha['label'];

                    $tekiyo .= $city . '　' . $taishosha;
                    break;

                    // 新入社員
                case 'fresh':
                    $tekiyo .= '新　新入社員';
                    break;

                    // 経営塾
                case 'management':
                    // 開催地
                    $tmp_type = get_field('ppc_management_type', $val->seminar_id);
                    $type = empty($tmp_type) ? '' : $tmp_type['label'];

                    $tekiyo .= '経　' . $type;
                    break;

                default:
                    # code...
                    break;
            }

            // 連絡会社名
            $tekiyo .= $val->company_name . '　' . $val->participant_total . '件';

            $data[$key] = [
                '',                                         // 識別フラグ
                '',                                         // 伝票No.
                '',                                         // 決算
                date('Y/m/t', strtotime($val->post_date)),  // お申し込み登録（月末最終営業日）
                '売掛金',                                   // 借方勘定科目
                $val->post_title,                           // セミナータイトル
                '',                                         // 借方部門
                '対象外',                                   // 借方税区分
                $val->total_amount,                         // 借方金額
                '0',                                        // 借方税金額
                'コンサル売上',                              // 貸方勘定科目
                '',                                         // 貸方補助科目
                $val->post_title,                           // セミナータイトル
                '課税売上10%',                               // 貸方税区分
                $val->total_amount,                         // 貸方金額
                '',                                         // 貸方税金額
                $tekiyo,                                    // 摘要
                '',                                         // 番号
                '',                                         // 期間
                '',                                         // タイプ
                '',                                         // 生成元
                '',                                         // 仕訳メモ
                '',                                         // 付箋1
                '',                                         // 付箋2
                '',                                         // 調整
            ];
        }

        return ['data' => $data, 'header' => $this->convert_encoding($header)];
    }

    /**
     * To get the label for the csv
     *
     * @param  int   $form_id お申し込みフォームID
     * @param  int   $type    0 or 2. This means that it will get fields with separator or not
     * @return array $fields  list of form labels
     */
    private function get_form_fields($form_id, $type = 0)
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
                if ($type == 0) {
                    $fields['ppc_sei_participant_name_' . $field_name]      = '参加者姓' . $field_name;
                    $fields['ppc_mei_participant_name_' . $field_name]      = '参加者名' . $field_name;
                    $fields['ppc_sei_kana_participant_name_' . $field_name] = '参加者セイ' . $field_name;
                    $fields['ppc_mei_kana_participant_name_' . $field_name] = '参加者メイ' . $field_name;
                } else {
                    $fields['ppc_sei_participant_name_' . $field_name]      = '参加者姓';
                    $fields['ppc_mei_participant_name_' . $field_name]      = '参加者名';
                    $fields['ppc_sei_kana_participant_name_' . $field_name] = '参加者セイ';
                    $fields['ppc_mei_kana_participant_name_' . $field_name] = '参加者メイ';
                }
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

    /**
     * To convert the application data
     * which is from json
     * to just array.
     * And also organize the data.
     *
     * @param  array $db_data
     * @return array $data
     */
    private function get_application_data($db_data)
    {
        foreach ($db_data as $key => $val) {
            // 郵便番号
            if (strpos($key, 'ppc_zip') !== false) {
                $data['zip'] = $val;
            }

            // 都道府県
            if (strpos($key, 'ppc_pref') !== false) {
                $data['pref'] = $val;
            }

            // 市町村名
            if (strpos($key, 'ppc_municipal') !== false) {
                $data['municipal'] = $val;
            }

            // 番地・建物名
            if (strpos($key, 'ppc_building') !== false) {
                $data['building'] = $val;
            }

            // 備考
            if (strpos($key, 'remarks') !== false || strpos($key, 'remark') !== false || strpos($key, 'biko') !== false) {
                $data['remarks'] = $val;
            }
        }

        $data['zip']     = empty($data['zip']) ? '' : $data['zip'];        // 郵便番号
        $data['remarks'] = empty($data['remark']) ? '' : $data['remark'];  // 備考

        $pref      = empty($data['pref']) ? '' : $data['pref'];       // 都道府県
        $municipal = empty($data['pref']) ? '' : $data['municipal'];  // 市町村名
        $building  = empty($data['pref']) ? '' : $data['building'];   // 番地・建物名
        $data['address'] = $pref . $municipal . $building;

        return $data;
    }

    /**
     * 参加者のデータ
     * Get the participant's detail
     *
     * @param  array         $data         Input data or post data
     * @return array|boolean $participants Only get the participants data or false if empty participants
     */
    private function get_participants_detail($data)
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

        $i = 1;
        foreach ($tmp_participants as $key => $val) {
            $age = '';
            if (isset($data['age_' . $i])) {
                $age = $data['age_' . $i];
            }

            $gender = '';
            if (isset($data['gender_' . $i])) {
                $gender = $this->get_gender($data['gender_' . $i]);
            }

            if (isset($data['sex_' . $i])) {
                $gender = $this->get_gender($data['sex_' . $i]);
            }

            if (isset($data['seibetsu_' . $i])) {
                $gender = $this->get_gender($data['seibetsu_' . $i]);
            }

            $busho = '';
            if (isset($data['busho_' . $i])) {
                $busho = $data['busho_' . $i];
            }

            $participants['age_' . $i]    = $age;     // 年齢
            $participants['gender_' . $i] = $gender;  // 性別
            $participants['busho_' . $i]  = $busho;   // 部署・役職

            $i++;
        }

        return $participants;
    }

    /**
     * Get the type of gender according
     * to the value
     *
     * @param  string $val db value. This value is from a radio button
     * @return string      japanese equivalent
     */
    private function get_gender($val)
    {
        $gender = '';
        switch ($val) {
            case 'woman':
            case 'female':
            case 'girl':
            case 'ona':
            case 'onna':
            case 'onnanoko':
            case 'onanoko':
            case 'josei':
                $gender = '女';
                break;

            case 'man':
            case 'male';
            case 'boy':
            case 'oto':
            case 'otoko':
            case 'otokonoko':
            case 'dansei':
                $gender = '男';
                break;

            default:
                $gender = $val;
                break;
        }

        return $gender;
    }

    /**
     * Convert encoding so that the
     * csv can be read.
     *
     * @param  array|string $param Default data. Array is for the header and the string is for the db data.
     * @return array|string        Converted data
     */
    private function convert_encoding($param)
    {
        if (is_array($param)) {
            $data = array();
            foreach ($param as $key => $val) {
                $data[$key] = mb_convert_encoding($val, self::FIRST_ENCODING_TYPE, self::SECOND_ENCODING_TYPE);
            }

            return $data;
        } else {
            return mb_convert_encoding($param, self::FIRST_ENCODING_TYPE, self::SECOND_ENCODING_TYPE);
        }
    }
}
