<?php

/**
 * Export csv
 */
require_once 'export/export.php';

/**
 * お申し込みデータ一覧
 */
class Form_List extends Export
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
     */
    public function __construct($helper_obj, $plugin_path_param, $plugin_url_param)
    {
        $this->helper = $helper_obj;
        $this->plugin_path = $plugin_path_param;
        $this->plugin_url = $plugin_url_param;

        if (!empty($_POST['action']) && $_POST['action'] === 'export') {
            $export = new Export($helper_obj);
        }
    }

    /**
     * To add page for お申し込みデータ
     * {home_url}/manager/wp-admin/admin.php?page=ppcformdata&seminar_id={seminar_id}
     */
    public function ppc_admin_form_list_data()
    {
        wp_enqueue_style('ppc-wp-admin-formdata', $this->plugin_url . 'assets/css/formdata.css', array(), false, 'all');

        $seminar_id = empty($_GET['seminar_id']) ? -1 : $_GET['seminar_id'];
        $page   = empty($_GET['paged']) ? 1 : $_GET['paged'];

        $seminar_start = get_field('seminar_start', $seminar_id);
        $seminar_end   = get_field('seminar_end', $seminar_id);
        $sched = ppc_get_seminar_date($seminar_start, $seminar_end);
        $term = get_the_terms($seminar_id, 'seminar_cat');

        $records = $this->get_cnt($seminar_id);
        $results = $this->get_data($seminar_id, $page);

        require_once $this->plugin_path . 'template/form-list.php';
    }

    /**
     * Get the total count for the
     * seminar list. This is mainly
     * used for pagination
     *
     * @param int $seminar_id セミナーID OR 投稿ID
     * @return int
     */
    private function get_cnt($seminar_id)
    {
        global $wpdb;
        $form_id = get_field('application_form_id', $seminar_id);

        $sql = <<<SQL
        SELECT
            count(*) as cnt
        FROM
            {$wpdb->prefix}seminar_application application
        WHERE
            seminar_id = %d
            AND form_id = %d
        SQL;
        $prepare = $wpdb->prepare($sql, [$seminar_id, $form_id]);
        $res     = $wpdb->get_results($prepare);
        $records = $res[0]->cnt < 1 ? 1 : $res[0]->cnt;

        return $records;
    }

    /**
     * Get the seminar application data
     *
     * @param int $seminar_id セミナーID OR 投稿ID
     * @param int $page       page number, this is mainly used for pagination
     * @return array
     */
    private function get_data($seminar_id, $page)
    {
        global $wpdb;
        $form_id = get_field('application_form_id', $seminar_id);

        $limit  = self::LIMIT;
        $offset = ($page - 1) * $limit;

        $sql = <<<SQL
        SELECT
            ID,
            JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."company_name"')) as company_name,
            JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."applicant_title"')) as title,
            JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."ppc_sei_applicant"')) as sei,
            JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."ppc_mei_applicant"')) as mei,
            JSON_UNQUOTE(JSON_EXTRACT(application.data, '$."type"')) as type,
            (SELECT count(*) FROM {$wpdb->prefix}seminar_participant WHERE application_id = application.ID) as participants,
            (SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = 'jukouryou' AND post_id = application.seminar_id) as jukouryou,
            (SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = 'member_fee' AND post_id = application.seminar_id) as member_fee,
            (SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = 'general_fee' AND post_id = application.seminar_id) as general_fee,
            application.amount,
            application.post_date
        FROM
            {$wpdb->prefix}seminar_application application
        WHERE
            seminar_id = %d
            AND form_id = %d
        ORDER BY ID DESC
        LIMIT {$offset}, {$limit}
        SQL;

        $prepare = $wpdb->prepare($sql, [$seminar_id, $form_id]);
        $res = $wpdb->get_results($prepare);

        return $res;
    }

    /**
     * ページネーション
     *
     * @param int $records    number of total records
     * @param int $page       page number
     * @param int $seminar_id セミナーID OR 投稿ID
     * @return
     */
    private function pagination($records, $page, $seminar_id)
    {
        $pages = (int) ceil($records / self::LIMIT);
        $nxtpg = $page + 1;
        $prvpg = (int) $page === 1 ? 1 : $page - 1;

        include 'template/pagination.php';
    }
}
