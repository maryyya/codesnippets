<?php

/**
 * Export csv
 */
require_once 'export/export.php';

/**
 * This class is for
 * listing all seminar
 * with participants
 */
class All_Form_List
{
    /**
     * Pagination limit
     */
    const LIMIT = 20;

    /**
     * Plugin path
     *
     * @var string
     */
    public $plugin_path;

    /**
     * Common function helper
     *
     * @var object
     */
    public $helper;

    /**
     * AcfプラグインURL
     */
    private $plugin_url = '';

    /**
     * Constructor
     *
     * @param object $helper_obj
     * @param string $plugin_path
     */
    public function __construct($helper_obj, $plugin_path_param, $plugin_url_param)
    {
        session_start();
        if (!empty($_POST)) {
            $_SESSION = $_POST;
        }

        $this->helper      = $helper_obj;
        $this->plugin_path = $plugin_path_param;
        $this->plugin_url  = $plugin_url_param;

        add_action('admin_enqueue_scripts', [$this, 'ppc_seminar_list_enqueue_scripts']);
        if (isset($_POST['action']) && $_POST['action'] === 'export') {
            $export = new Export($helper_obj);
        } else if (isset($_POST['sankasha']) || isset($_POST['seikyuyo']) || isset($_POST['keiriyo'])) {
            $type = isset($_POST['seikyuyo']) || isset($_POST['keiriyo']) ? 'seikyuyo' : 'csv';
            $cond = $this->get_sql_condition($_POST);
            $data  = $this->get_data($cond, 1, $type);
            $export = new Export($helper_obj, $data);
        }
    }

    /**
     * Add the styles and js needed on the
     * お申し込み全データ
     *
     * @return
     */
    public function ppc_seminar_list_enqueue_scripts()
    {
        $post_type = !empty($_GET['post_type']) ? $_GET['post_type'] : '';
        $page = !empty($_GET['page']) ? $_GET['page'] : '';
        if ($post_type === 'seminar' && $page === 'ppcallseminardata') {
            wp_enqueue_style('ppc-wp-admin-formdata', $this->plugin_url . 'assets/css/formdata.css', array(), false, 'all');

            // jquery datepicker css
            wp_enqueue_style('ppc_list_datepicker_css', $this->plugin_url . 'assets/css/jquery.simple-dtpicker.css');

            // custom css
            wp_enqueue_style('ppc_list_custom_css', $this->plugin_url . 'assets/css/custom.css');

            // jquery datepicker js
            wp_enqueue_script('ppc_list_datepicker_js', $this->plugin_url . 'assets/js/jquery.simple-dtpicker.js');

            // custom js
            wp_enqueue_script('ppc_list_custom_js', $this->plugin_url . 'assets/js/custom.js');
        }
    }

    /**
     * This is for displaying all
     * the seminars with it's participants
     *
     * @return
     */
    public function ppc_admin_form_all_data()
    {
        $post_param = $_SESSION;
        $page = empty($_GET['paged']) ? 1 : $_GET['paged'];
        $cond = $this->get_sql_condition($post_param);
        $sql  = $this->get_data($cond, $page);
        $records = empty($post_param) && empty($_GET['paged']) ? 1 : $sql;
        $sql  = $this->get_data($cond, $page, 'full');
        $results = empty($post_param) && empty($_GET['paged']) ? [] : $sql;

        // セミナーのカテゴリー
        $category_list = get_terms('seminar_cat', array(
            'hide_empty' => false,
            'order' => 'asc',
            'orderby' => 'order'
        ));

        // セミナー開始日時
        $seminar_start_date = isset($post_param['seminar_start_date']) && strlen($post_param['seminar_start_date']) > 0 ? $post_param['seminar_start_date'] : '';

        // セミナー終了日時
        $seminar_end_date = isset($post_param['seminar_end_date']) && strlen($post_param['seminar_end_date']) > 0 ? $post_param['seminar_end_date'] : '';

        // お申し込み受付開始時刻
        $form_start_date = isset($post_param['form_start_date']) && strlen($post_param['form_start_date']) > 0 ? $post_param['form_start_date'] : '';

        // お申し込み受付終了時刻
        $form_end_date = isset($post_param['form_end_date']) && strlen($post_param['form_end_date']) > 0 ? $post_param['form_end_date'] : '';

        // 受講料
        $jukouryou = isset($post_param['jukouryou']) && strlen($post_param['jukouryou']) > 0 ? $post_param['jukouryou'] : '';

        // 無料
        $free = isset($post_param['jukouryou']) && $jukouryou === 'free' ? 'checked' : '';

        // 有料
        $paid = isset($post_param['jukouryou']) && $jukouryou === 'paid' ? 'checked' : '';

        // 定員
        $teiin = isset($post_param['teiin']) && strlen($post_param['teiin']) > 0 ? $post_param['teiin'] : '';

        // 定員
        $member = isset($post_param['member']) && strlen($post_param['member']) > 0 ? $post_param['member'] : '';

        // 一般
        $general = isset($post_param['general']) && strlen($post_param['general']) > 0 ? $post_param['general'] : '';

        // セミナーコード
        $seminar_code = isset($post_param['seminar_code']) && strlen($post_param['seminar_code']) > 0 ? $post_param['seminar_code'] : '';

        require_once $this->plugin_path . 'template/all-form-list.php';
    }

    /**
     * Set the sql condition
     *
     * @param  array  $param
     * @return string $cond
     */
    private function get_sql_condition($post_param)
    {
        $param = [];
        $query = [];

        // セミナーカテゴリー
        if (isset($post_param['category']) && strlen($post_param['category']) > 0) {
            $param[] = $post_param['category'];
            $query[] = 'term.term_id = %d';
        }

        // セミナー開始日時
        if (isset($post_param['seminar_start_date']) && strlen($post_param['seminar_start_date']) > 0) {
            $param[] = $this->reformat_datetime($post_param['seminar_start_date']);
            $query[] = 'seminar.seminar_start >= %s';
        }

        // セミナー終了日時
        if (isset($post_param['seminar_end_date']) && strlen($post_param['seminar_end_date']) > 0) {
            $param[] = $this->reformat_datetime($post_param['seminar_end_date']);
            $query[] = 'seminar.seminar_end <= %s';
        }

        // お申し込み受付開始時刻
        if (isset($post_param['form_start_date']) && strlen($post_param['form_start_date']) > 0) {
            $param[] = $this->reformat_datetime($post_param['form_start_date']);
            $query[] = 'seminar.application_start >= %s';
        }

        // お申し込み受付終了時刻
        if (isset($post_param['form_end_date']) && strlen($post_param['form_end_date']) > 0) {
            $param[] = $this->reformat_datetime($post_param['form_end_date']);
            $query[] = 'seminar.application_end <= %s';
        }

        // 受講料
        if (isset($post_param['jukouryou']) && strlen($post_param['jukouryou']) > 0) {
            $param[] = $post_param['jukouryou'];
            $query[] = "jukouryou.meta_value LIKE '%%%s%%'";
        }

        // 定員
        if (isset($post_param['teiin']) && strlen($post_param['teiin']) > 0) {
            $param[] = $post_param['teiin'];
            $query[] = 'teiin.meta_value = %s';
        }

        // 会員
        if (isset($post_param['member_fee']) && strlen($post_param['member_fee']) > 0) {
            $param[] = $post_param['member_fee'];
            $query[] = 'member_fee.meta_value = %s';
        }

        // 一般
        if (isset($post_param['general_fee']) && strlen($post_param['general_fee']) > 0) {
            $param[] = $post_param['general_fee'];
            $query[] = 'general_fee.meta_value = %s';
        }

        // セミナーコード
        if (isset($post_param['seminar_code']) && strlen($post_param['seminar_code']) > 0) {
            $param[] = $post_param['seminar_code'];
            $query[] = 'ppc_seminar_code.meta_value = %s';
        }

        $querys = '';
        if (!empty($query)) {
            $querys = 'WHERE ' . implode(' AND ', $query);
        }

        return ['query' => $querys, 'param' => $param];
    }

    /**
     * Get all seminar data
     * with participants
     *
     * @param  array  $sql  'query' => query with conditions and 'param' => is array of $_POST parameters
     * @param  int    $page page number, this from the parameter get
     * @param  string $type 'cnt' or' seikyuyo'
     * @return object $res  database data
     */
    private function get_data($cond, $page, $type = 'cnt')
    {
        global $wpdb;
        $param   = $cond['param'];
        $sub_sql = 'count(*) AS cnt';
        $limit_sql = $seikyuyo_subsql = $seikyuyo_subgroupbysql = '';

        // for the full query data
        if ($type !== 'cnt') {
            $limit  = self::LIMIT;
            $offset = ($page - 1) * $limit;
            $limit_sql = $type === 'csv' || $type === 'seikyuyo' ? '' : "LIMIT {$offset}, {$limit}";

            // for 請求用CSV
            if ($type === 'seikyuyo' || $type === 'keiriyo') {
                $seikyuyo_subsql = <<<SUBSQL
                COUNT(participant.application_id) AS participant_total,
                application.amount AS total_amount,
                SUBSQL;
                $seikyuyo_subgroupbysql = 'GROUP BY participant.application_id';
            }

            $sub_sql = <<<SUBSQL
participant.seminar_code,
application.ID AS application_id,
{$seikyuyo_subsql}
CONCAT(ppc_seminar_code.meta_value, participant.seminar_code) AS official_seminar_code,
ppc_seminar_code.meta_value as ppc_seminar_code,
application.post_date,
application.seminar_id,
application.data as application_data,
participant.no as participant_no,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."company_name"')) AS company_name,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."company_name_kana"')) AS company_name_kana,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."gyoshu"')) AS gyoshu,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."ppc_hokuriku_dealer"')) AS ppc_hokuriku_dealer,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."ppc_hokkaido_dealer"')) AS ppc_hokkaido_dealer,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."gyoshu"')) AS gyoshu,
JSON_UNQUOTE(JSON_EXTRACT(participant.data, '$."sei"')) AS participant_sei,
JSON_UNQUOTE(JSON_EXTRACT(participant.data, '$."mei"')) AS participant_mei,
JSON_UNQUOTE(JSON_EXTRACT(participant.data, '$."sei_kana"')) AS participant_sei_kana,
JSON_UNQUOTE(JSON_EXTRACT(participant.data, '$."mei_kana"')) AS participant_mei_kana,
JSON_UNQUOTE(JSON_EXTRACT(participant.data, '$."mail"')) AS participant_mail,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."ppc_sei_applicant"')) AS applicant_sei,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."ppc_mei_applicant"')) AS applicant_mei,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."applicant_gender"')) AS applicant_gender,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."applicant_title"')) AS applicant_title,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."renbusho"')) AS renbusho,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."applicant_mail"')) AS applicant_mail,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."tel"')) AS tel,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."fax"')) AS fax,
JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."type"')) AS type,
posts.post_title,
seminar.application_start,
seminar.application_end,
seminar.seminar_start,
seminar.seminar_end,
term.term_id AS term_id,
term.name AS term_name,
term.slug AS term_slug,
jukouryou.meta_value AS jukouryou,
teiin.meta_value AS teiin,
member_fee.meta_value AS member_fee,
general_fee.meta_value AS general_fee
SUBSQL;
        }

        $sql = <<<SQL
SELECT
{$sub_sql}
FROM
{$wpdb->prefix}seminar seminar
    INNER JOIN
{$wpdb->prefix}seminar_application application ON seminar.post_id = application.seminar_id
    INNER JOIN
{$wpdb->prefix}seminar_participant participant ON application.ID = participant.application_id
    INNER JOIN
{$wpdb->prefix}posts posts ON seminar.post_id = posts.ID
    AND posts.post_status IN ('publish', 'draft', 'pending')
    INNER JOIN
(SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'jukouryou') as jukouryou ON jukouryou.post_id = seminar.post_id
    INNER JOIN
(SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'teiin') as teiin ON teiin.post_id = seminar.post_id
    INNER JOIN
(SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'member_fee') as member_fee ON member_fee.post_id = seminar.post_id
    INNER JOIN
(SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'general_fee') as general_fee ON general_fee.post_id = seminar.post_id
    INNER JOIN
(SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'ppc_seminar_code') as ppc_seminar_code ON ppc_seminar_code.post_id = seminar.post_id
    INNER JOIN
{$wpdb->prefix}terms term ON seminar.term_id = term.term_id
{$cond['query']}
{$seikyuyo_subgroupbysql}
ORDER BY seminar.ID DESC
{$limit_sql}
SQL;
        $prepare = $sql;
        if (!empty($param)) {
            $prepare = $wpdb->prepare($sql, $param);
        }

        $res = $wpdb->get_results($prepare);
        if ($type === 'cnt') {
            $res = $res[0]->cnt < 1 ? 1 : $res[0]->cnt;
        }

        return $res;
    }

    /**
     * ページネーション
     *
     * @param int    $records    number of total records
     * @param int    $page       page number
     * @param int    $seminar_id セミナーID OR 投稿ID
     * @param string $pagenm     can be 'all' or none
     * @return
     */
    private function pagination($records, $page, $seminar_id = 0, $pagenm = '')
    {
        $pages = (int) ceil($records / self::LIMIT);
        $nxtpg = $page + 1;
        $prvpg = (int) $page === 1 ? 1 : $page - 1;

        include 'template/all-form-pagination.php';
    }

    /**
     * To reformat the date and time
     * Usually used in date parameters in
     * get_sql_condition. Since the value
     * is like '2019年02月09日　12時03分'
     * so I want it to be like '2019-02-09 12:03',
     * so that I can use it the where conditions
     * for the db
     *
     * @param  string $datetime post parameter. Can be used for セミナー開始日時、セミナー終了日時、お申し込み受付開始時刻、お申し込み受付終了時刻
     * @return string           formatted datetime according to database's data.
     */
    private function reformat_datetime($datetime)
    {
        return date('Y-m-d H:i:s', strtotime($datetime));
    }

    /**
     * Get the tel and fax data since
     * the data is in json format.
     * like {"data": "011-234-5678", "separator": "-"}
     * That kind of format is the default
     * for mw wp form.
     *
     * @param  string  $param encoded json data tel or fax
     * @return string         tel or fax string
     */
    private function get_tel_fax_data($param) {
        if (empty($param)) {
            return '';
        }

        $decode = json_decode($param);
        return empty($decode->data)?'':$decode->data;
    }

    /**
     * Get the 住所
     * This is a combination of
     * 郵便番号と都道府県と市町村名と番地・建物名
     *
     * @param  string  $param encoded json data. The お申し込みフォームデータ。
     * @return string         郵便番号と都道府県と市町村名と番地・建物名
     */
    private function get_place($param) {
        $place = [];

        if (empty($param)) {
            return '';
        }

        $decode = json_decode($param);
        foreach ($decode as $key => $value) {
            // 郵便番号
            if (mb_strpos($key, 'zip')) {
                $place[] = $value;
            }

            // 都道府県
            if (mb_strpos($key, 'prefecture')) {
                $place[] = $value;
            }

            // 市町村名
            if (mb_strpos($key, 'municipal')) {
                $place[] = $value;
            }

            // 番地・建物名
            if (mb_strpos($key, 'building')) {
                $place[] = $value;
            }
        }

        return implode('', $place);
    }
}
