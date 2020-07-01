<?php

/**
 * Plugin Name: SDGsとGAPのcsvエクスポート
 * Plugin URI: http://www.pripress.co.jp/products/web/02.html
 * Description: CSV Export data of sdgs and gap
 * Version: 1.0
 * Author: Pripress
 * Author URI: http://www.pripress.co.jp/corporate/outline.html
 */

if (!defined('ABSPATH')) {
    exit;
}

class Sdgs_gap_export
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
     * GAP
     */
    const GAP_KEY = 'field_59e4318ac332a';

    /**
     * SDGS
     */
    const SDGS_KEY = 'field_59e4309905762';

    /**
     * プラグインURL
     *
     * @var string
     */
    private $plugin_url;

    /**
     * プラグインパス
     *
     * @var string
     */
    private $plugin_path;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plugin_url  = plugin_dir_url(__FILE__);
        $this->plugin_path = plugin_dir_path(__FILE__);

        add_action('admin_menu', array($this, 'add_menu'));

        // only load the css and js in sdgs gap export page
        if (!empty($_GET['page']) && $_GET['page'] === 'sdgs_export') {
            add_action('admin_enqueue_scripts', array($this, 'load_admin_style'));
        }
    }

    /**
     * メニュー追加
     *
     * @return
     */
    public function add_menu()
    {
        add_menu_page('SDGsとGAPのデータエクスポート', 'csvエクスポート', 'manage_options', 'sdgs_export', array($this, 'template'), 'dashicons-download', 10);
    }

    /**
     * Add style
     *
     * @return
     */
    public function load_admin_style()
    {
        wp_enqueue_style('sdgs_admin_css', $this->plugin_url . '/assets/css/style.css', false, '1.0.0');

        // // jquery datepicker css
        // wp_enqueue_style('ppc_list_datepicker_css', $this->plugin_url . '/assets/css/jquery.simple-dtpicker.css');

        // // jquery datepicker js
        // wp_enqueue_script('ppc_list_datepicker_js', $this->plugin_url . '/assets/js/jquery.simple-dtpicker.js');

        // jquery datepicker css
        wp_enqueue_style('ppc_list_datepicker_css', $this->plugin_url . '/assets/css/DatePickerX.min.css');

        // jquery datepicker js
        wp_enqueue_script('ppc_list_datepicker_js', $this->plugin_url . '/assets/js/DatePickerX.min.js');

        // custom js
        wp_enqueue_script('ppc_custom_js', $this->plugin_url . '/assets/js/custom.js');
    }

    /**
     * Add the html style in 管理ページ
     *
     * @return
     */
    public function template()
    {
        $post = $_POST;
        if (!empty($post)) {
            $this->export($post);
        }
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">SDGsとGAPのデータエクスポート</h1>
            <form method="POST">
                <table id="sdgs-export-table">
                    <tbody>
                        <tr>
                            <th scope="row">カテゴリー</th>
                            <td id="front-static-pages">
                                <p>
                                    <label><input name="category" type="radio" value="gyousei" class="tog">行政情報</label>
                                    <label><input name="category" type="radio" value="katsudou" class="tog">EPO活動情報</label>
                                </p>
                            </td>
                            <td>
                                <p></p>
                            </td>
                        </tr>
                        <tr>
                            <th>公開日</th>
                            <td><input name="start_date" id="start_date" type="text" value="">　～　<input name="end_date" id="end_date" type="text" value=""></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="submit">
                                <div>
                                    <input type="button" name="clear" id="clear" class="button" value="クリア">
                                    <input type="submit" name="submit" id="submit" class="button button-primary" value="CSVエクスポート">
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
<?php

    }

    /**
     * エクスポートCSV
     *
     * @param string $param POST パラメータ
     */
    private function export($param)
    {
        $res      = $this->get_data($param);
        $csv_name = empty($param['category']) ? '全部' : ($param['category'] === 'katsudou' ? 'EPO活動情報' : '行政情報');
        ob_end_clean();
        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=" . $csv_name . '.csv');

        // to create a temporary csv file
        $handle = fopen('php://output', 'w');
        if ($handle !== false) {

            // first is the header
            fputcsv($handle, $res['header']);

            // the proceed with writing the data from the db
            foreach ($res['data'] as $key => $items) {
                $final_data = array();
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
     * To get the data according to category
     * Header is also included.
     *
     * @param  string $param POST パラメータ
     * @return array            data and header
     */
    private function get_data($post_param)
    {
        global $wpdb;
        $cnd   = "post.post_status IN (%s , %s, %s)";
        $param = array('publish', 'draft', 'pending');

        // カテゴリー
        if (isset($post_param['category']) && strlen($post_param['category']) > 0) {
            $cnd .= ' AND post.post_type = %s';
            $param[] = $post_param['category'];
        } else {
            $cnd .= ' AND post.post_type IN (%s, %s)';
            $param[] = 'gyousei';
            $param[] = 'katsudou';
        }

        // 公開日スタート
        if (isset($post_param['start_date']) && strlen($post_param['start_date']) > 0) {
            $cnd .= ' AND post.post_date >= %s';
            $param[] = $post_param['start_date'] . ' 00:00:00';
        }

        // 公開日終了
        if (isset($post_param['end_date']) && strlen($post_param['end_date']) > 0) {
            $cnd .= ' AND post.post_date <= %s';
            $param[] = $post_param['end_date'] . ' 23:59:59';
        }

        $sql = <<<SQL
SELECT
post.ID,
post.post_date,
post.post_type,
CASE
    WHEN post.post_status = 'publish' THEN '公開済み'
    WHEN post.post_status = 'draft' THEN '下書き'
    WHEN post.post_status = 'pending' THEN 'レビュー待ち'
END as post_status,
post.post_title
FROM
{$wpdb->prefix}posts post
WHERE
$cnd
ORDER BY post.post_date DESC
-- LIMIT 2
SQL;
        $prepare      = $wpdb->prepare($sql, $param);
        $res          = $wpdb->get_results($prepare);
        $gap_choices  = get_field_object(self::GAP_KEY)['choices'];
        $sdgs_choices = get_field_object(self::SDGS_KEY)['choices'];

        $tmp_header = array(
            'No.',
            // 'ID',
            '公開日',
            // 'ステータス',
            '記事',
            // 'SDGs',
            '政',
            '包',
            '教',
            'ユ',
            'コ',
            '',
            '',
        );

        // if no category then identify the category
        if (empty($post_param['category'])) {
            array_splice($tmp_header, 2, 0, 'カテゴリー');
        }

        $header = array_merge($tmp_header, array_keys($sdgs_choices));

        $data = array();
        foreach ($res as $key => $val) {
            $cnt          = $key + 1;
            $data[$key][] = $cnt; // No
            // $data[$key][] = $val->ID; // 投稿ID
            $data[$key][] = date('Y年m月d日', strtotime($val->post_date)); // 投稿日
            // $data[$key][] = $val->post_status; // 投稿ステータス

            if (empty($post_param['category'])) {
                $data[$key][] = $val->post_type === 'gyousei' ? '行政情報' : 'EPO活動情報'; // 投稿タイプかカテゴリー
            }

            $data[$key][] = $val->post_title; // 投稿タイトル

            $sdgs_val_list = get_field('sdgs_tag', $val->ID);
            $gap_val_list  = get_field('gap_tag', $val->ID);

            $sdgs = array();
            if (!empty($sdgs_val_list)) {
                foreach ($sdgs_val_list as $val) {
                    $sdgs[] = (int) $val['value'];
                }
            }

            $gap = array();
            if (!empty($gap_val_list)) {
                foreach ($gap_val_list as $val) {
                    $gap[] = (int) $val['value'];
                }
            }

            foreach ($gap_choices as $gap_key => $gap_val) {
                if (in_array($gap_key, $gap)) {
                    $data[$key][] = 1; // GAP値
                } else {
                    $data[$key][] = ''; // GAP値
                }
            }

            $data[$key][] = ''; // space between gap and sdgs value, so no confusion
            $data[$key][] = ''; // space between gap and sdgs value, so no confusion

            foreach ($sdgs_choices as $sdgs_key => $sdgs_val) {
                if (in_array($sdgs_key, $sdgs)) {
                    $data[$key][] = 1; // SDGS値
                } else {
                    $data[$key][] = ''; // SDGS値
                }
            }
        }

        $converted = $this->convert_encoding($header);
        return array('data' => $data, 'header' => $converted);
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

$exec = new Sdgs_gap_export();
