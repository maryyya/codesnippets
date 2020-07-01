<?php
/*
Plugin Name: 店舗情報のインポートエクスポート
Plugin URI: https://www.pripress.co.jp/products/web/02.html
Description: 店舗情報データインポートエクスポート用のカスタムプラグイン
Author: プリプレスセンター
Author URI: https://www.pripress.co.jp/corporate/
Text Domain: shop-import-export
Version: 1.0.0
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Defined variables
 */
require_once('config.php');

/**
 * 店舗情報のインポートエクスポートクラス
 *
 * For the カスタム営業時間 db table
 *  CREATE TABLE `miyagi_shop` (
 *  CREATE TABLE `tokyo_shop` (
 *  CREATE TABLE `saitama_shop` (
 *  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 *  `post_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
 *  `data` longtext COLLATE utf8mb4_unicode_ci,
 *  `user_id` int(10) DEFAULT NULL,
 *  `dtcreate` datetime NOT NULL,
 *  `dtupdate` datetime DEFAULT NULL,
 *  PRIMARY KEY (`ID`)
 *  ) ENGINE=Inn
 */
class ShopImportExport
{
    /**
     * Namespace for the custom rest api
     */
    const SHOP_NAMESPACE = 'shop_core/v2';

    /**
     * ログパス
     */
    const SHOP_LOG_DIR = '/var/log/shoplog';

    /**
     * This is in the js file for the
     * ajax path.
     * /wp-json/shop_core/v2/ is the value.
     */
    const SHOP_JS_PATH = '/wp-json/'.self::SHOP_NAMESPACE.'/';

    /**
     * For displaying the logs
     * Used in the template.php
     *
     * @var array
     */
    private $equivalent = array(
        'id'                    => '店舗ID',
        'post_title'            => 'タイトル',
        'store_name'            => '名称',
        'store_address'         => '住所',
        'store_tel'             => '電話番号',
        'store_url'             => 'URL',
        'reserve_url'           => '予約URL',
        'open_close'            => '営業時間',
        'medical_time'          => '診療時間',
        'store_holiday'         => '定休日',
        'medical_subject'       => '診療科目',
        'genre'                 => 'ジャンル',
        'average_budget'        => '平均予算',
        'credit_card'           => 'クレジットカード',
        'seats'                 => '席数',
        'tobacco'               => '喫煙',
        'access'                => 'アクセス',
        'parking'               => '駐車場',
        'parking_comment'       => '駐車場に関するコメント',
        'facility'              => '店舗設備',
        'remarks'               => '備考',
        'nearest_station'       => '最寄り駅',
        'route'                 => '行き方',
        'store_remarks'         => '店舗備考',
        'ppc_api_id'            => 'API ID',
        'ppc_api_source'        => 'APIデータソース',
        'ppc_api_source_fdoc'   => 'EPARK病院API詳細',
        'ppc_api_source_haisha' => 'EPARK歯科API詳細',
    );

    /**
     * For displaying the logs
     * Used in the template.php
     *
     * @var array
     */
    private $miyagi_equivalent = array(
        'id'                    => '店舗ID',
        'post_title'            => 'タイトル',
        'store_name'            => '名称',
        'store_address'         => '住所',
        'store_tel'             => '電話番号',
        'open_close'            => '営業時間',
        'medical_time'          => '診療時間',
        'store_holiday'         => '定休日',
        'genre'                 => 'ジャンル',
        'seki'                  => '席数',
        'facility'              => '店舗設備',
        'average_budget'        => '平均予算',
        'tobacco'               => '喫煙',
        'credit_card'           => 'クレジットカード',
        'access'                => 'アクセス',
        'parking'               => '駐車場',
        'parking_comment'       => '駐車場に関するコメント',
        'remarks'               => '備考',
        'store_url'             => 'URL',
        'reserve_url'           => '予約URL',

        'ppc_api_id'            => 'API ID',
        'ppc_api_source'        => 'APIデータソース',
        'ppc_api_source_fdoc'   => 'EPARK病院API詳細',
        'ppc_api_source_haisha' => 'EPARK歯科API詳細',
    );

    /**
     * Initialization
     */
    public function __construct() {
        // register routes
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );

        // add submenu into 店舗情報メニュー
        add_action( 'admin_menu', array( $this, 'shop_add_menu' ) );

        // カスタム営業時間のため
        add_action( 'admin_menu', array( $this, 'shop_custom_sched' ) );

        // カスタム営業時間のため、save the data to custom table(prefix_shop)
        add_action('save_post', array( $this, 'custom_sched_save_post_callback' ) );


        // check if the page is in shopimportexport and post type is shop
        if ( !$this->check_page() ) {
            return false;
        }

        // for the css
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // only allow csv files into importing
        add_filter( 'upload_mimes', array( $this, 'shop_restict_mime' ) );
    }

    /**
     * For the css for this plugins template
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'shop-import-export-css', SHOP_PLUGIN_URL . 'assets/css/shop-import-export.css', array(), false, 'all' );
    }

    /**
     * Register the route with the callback
     */
    public function register_routes() {
        require_once('export.php');
        require_once('import.php');

        $export = new Export();
        $import = new Import();

        register_rest_route( self::SHOP_NAMESPACE, 'export', array(
            'methods'  => 'POST',
            'callback' => array( $export, 'export_exec' ),
        ) );

        register_rest_route( self::SHOP_NAMESPACE, 'import', array(
            'methods'  => 'POST',
            'callback' => array( $import, 'import_exec' ),
        ) );

        // カスタム営業時間エクスポート
        register_rest_route( self::SHOP_NAMESPACE, 'sched-export', array(
            'methods'  => 'POST',
            'callback' => array( $export, 'sched_export_exec' ),
        ) );
    }

    /**
     * Add menu on 店舗情報
     */
    public function shop_add_menu() {
        // import
        add_submenu_page(
            'edit.php?post_type=shop'
            , 'インポートエクスポート'
            , 'インポートエクスポート'
            , 'manage_options'
            , 'shopimportexport'
            , array( $this, 'shop_import_export' )
        );
    }

    /**
     * インポートメイン機能
     *
     * @return boolean|none
     */
    public function shop_import_export() {
        // check if the page is in shopimportexport and post type is shop
        if ( !$this->check_page() ) {
            return false;
        }

        // add media for import
        if ( ! did_action( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        }

        $shop_js_path = self::SHOP_JS_PATH;

        // import result
        $log = $this->get_log();

        // add the template
        require_once(SHOP_PLUGIN_PATH.'template.php');
    }

    /**
     * カスタム営業時間のHTML
     */
    public function shop_custom_sched() {
        global $pagenow;
        global $wpdb;
        $post = isset( $_GET['post'] ) ? $_GET['post']:'';
        $post_type = !empty( $_GET['post_type'] )?$_GET['post_type']:'';
        if ( ( $pagenow === 'post-new.php' && $post_type === 'shop' ) || ( $pagenow === 'post.php' && 'shop' === get_post_type( $post ) ) ) {
            $acf_last_input = SHOP_SITE_TYPE === 'miyagi'?MIYAGI_ACF_LAST_INPUT:ACF_LAST_INPUT;
            echo '<input type="hidden" name="sie_plugin_url" value="'.SHOP_PLUGIN_URL.'">';
            echo '<input type="hidden" name="acf_last_input" value="'.$acf_last_input.'">';
            echo '<input type="hidden" name="ppc_home_url" value="'.home_url().'">';
            wp_enqueue_script( 'shop-custom-sched', SHOP_PLUGIN_URL.'/assets/js/shop-custom-sched.js', array( 'jquery' ) );
            wp_enqueue_style( 'shop-custom-sched-css', SHOP_PLUGIN_URL . 'assets/css/shop-custom-sched.css', array(), false, 'all' );

            $data = '';
            if ( !empty( $post ) ) {
                $cntsql = 'SELECT count(ID) as cnt FROM '.$wpdb->prefix.'shop WHERE post_id = '.$post;
                $cntres = $wpdb->get_results( $cntsql );
                $cnt = ( int )$cntres[0]->cnt;

                if ( $cnt > 0 ) {
                    $sql = 'SELECT ID, post_id, data FROM '.$wpdb->prefix.'shop WHERE post_id = '.$post;
                    $res = $wpdb->get_results( $sql );
                    $data = json_encode( $res, JSON_UNESCAPED_UNICODE );
                }
            }

            echo "<input type='hidden' name='sie-shop-value' value='".$data."'>";
        }
    }

    /**
     * カスタム営業時間保存
     *
     * @param int $post_id
     */
    public function custom_sched_save_post_callback( $post_id ) {
        global $post;

        if ( empty( $post ) ) {
            return false;
        }

        if ( $post->post_type != 'shop' ) {
            return;
        }

        global $wpdb;
        $param   = $_POST['shop'];
        $user_id = $_POST['user_ID'];
        $post_id = $_POST['post_ID'];

        $cols = array(
            'post_id',
            'data',
            'user_id',
            'dtcreate',
        );

        $sqls = array();

        // this is a string of ids
        $deleted_ids = $this->check_shop_item( $param['sched'], $post_id );
        if ( !empty( $deleted_ids ) ) {
            $sqls[] = 'DELETE FROM '.$wpdb->prefix.'shop WHERE ID IN ('.$deleted_ids.');';
        }

        foreach( $param['sched'] as $sched ) {
            if ( empty( $sched['time'] ) ) {
                continue;
            }

            $sched['mon_display'] = isset( $param['mon_display'] )?(int)$param['mon_display']:0;
            $sched['tue_display'] = isset( $param['tue_display'] )?(int)$param['tue_display']:0;
            $sched['wed_display'] = isset( $param['wed_display'] )?(int)$param['wed_display']:0;
            $sched['thu_display'] = isset( $param['thu_display'] )?(int)$param['thu_display']:0;
            $sched['fri_display'] = isset( $param['fri_display'] )?(int)$param['fri_display']:0;
            $sched['sat_display'] = isset( $param['sat_display'] )?(int)$param['sat_display']:0;
            $sched['sun_display'] = isset( $param['sun_display'] )?(int)$param['sun_display']:0;
            $sched['hol_display'] = isset( $param['hol_display'] )?(int)$param['hol_display']:0;

            $data = json_encode($sched, JSON_UNESCAPED_UNICODE);
            $sched_data = $wpdb->remove_placeholder_escape( esc_sql( $data ) );
            $vals = array(
                $post_id,
                "'".$sched_data."'",
                $user_id,
                '"'.date('Y-m-d H:i:s').'"',
            );

            if ( empty( $sched['item_id'] ) ) {
                $sql = 'INSERT INTO '.$wpdb->prefix.'shop ('.implode( ', ', $cols ).')';
                $sql.= ' VALUES ('.implode( ', ', $vals ).') ';
            } else {
                $sql = "UPDATE ".$wpdb->prefix."shop SET data = '".$sched_data."', dtupdate = '".date( 'Y-m-d H:i:s' )."' WHERE ID = ".$sched['item_id'].' AND post_id = '.$post_id;
            }

            $sqls[] = $sql;

        }

        // var_dump($sqls);exit;

        if ( !empty( $sqls ) ) {
            foreach( $sqls as $sql ) {
                $res = $wpdb->query( $sql );
                // var_dump($res);
            }
        }
        // exit;
    }

    /**
     * Restrict only to csv and excel
     * during importing file.
     *
     * @param array  $mimes list of default extensions
     * @return array $mimes list of new extensions such as excel and csv files
     */
    public function shop_restict_mime( $mimes ) {
        $mimes = array(
            // 'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
            // 'xlsx'            => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            // 'xlsm'            => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            // 'xlsb'            => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            // 'xltx'            => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            // 'xltm'            => 'application/vnd.ms-excel.template.macroEnabled.12',
            // 'xlam'            => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'csv'             => 'text/csv',
        );

        return $mimes;
    }

    /**
     * Run only when the plugin is activated
     */
    public function activate_plugin() {
        flush_rewrite_rules();
    }

    /**
     * Run only when the plugin is activated
     */
    public function deactivate_plugin() {
        flush_rewrite_rules();
    }

    /**
     * Get the logs and
     * return the latest modified file.
     *
     * @return string
     */
    private function get_log() {
        $logs = scandir( self::SHOP_LOG_DIR );
        $files = array();
        foreach ( $logs as $key => $file ) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            // only the current site
            if ( strpos( $file, SHOP_SITE_TYPE ) === false ) {
                continue;
            }

            $filepath = self::SHOP_LOG_DIR.'/'.$file;
            $files[filemtime($filepath)] = $filepath;
        }

        krsort( $files );

        // return the latest modified
        return reset($files);
    }

    /**
     * Check if page is in shopimportexport.
     * And post type is shop.
     *
     * @return boolean true if it's in shopimportexport but if not then false
     */
    private function check_page() {
        // check the page, only accepts shopImport
        $page = empty( $_GET['page'] )?'':$_GET['page'];
        if ( $page !== 'shopimportexport' ) {
            return false;
        }

        // check the post type, only accepts shop
        $post_type = empty( $_GET['post_type'] )?'':$_GET['post_type'];
        if ( $post_type !== 'shop' ) {
            return false;
        }

        return true;
    }

    /**
     * To check the ids that needed to be
     * deleted. This is used in カスタム営業時間.
     * The datas are in prefix_shop table.
     *
     * @param array $param_sched Post data after registering or updating shop data.
     * @param int   $post_id
     * @return string empty string if no ids to be deleted.
     */
    private function check_shop_item( $param_sched, $post_id ) {
        global $wpdb;

        $ids = array();
        foreach( $param_sched as $val ) {
            // if ( empty( $val['item_id'] ) || empty( $val['time'] ) ) {
            if ( empty( $val['item_id'] ) ) {
                continue;
            }

            $ids[] = $val['item_id'];
        }
        $sql = 'SELECT ID FROM '.$wpdb->prefix.'shop WHERE ID NOT IN ('.implode( ', ', $ids ).') AND post_id = '.$post_id;
        $res = $wpdb->get_results( $sql );

        $deleted_ids = array();
        foreach ( $res as $val ) {
            $deleted_ids[] = $val->ID;
        }

        return empty( $deleted_ids )?'':implode( ',', $deleted_ids );
    }
}

$exec = new ShopImportExport();

register_activation_hook( __FILE__, array( $exec, 'activate_plugin' ) );
register_activation_hook( __FILE__, array( $exec, 'deactivate_plugin' ) );
