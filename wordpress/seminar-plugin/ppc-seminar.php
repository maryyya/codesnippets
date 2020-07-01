<?php

/**
 * Plugin Name: PPC SEMINAR
 * Description: セミナーデータ表示。セミナーエキスパート
 * Version: 1.0
 * Author: PPC
 * Author URI: https://www.pripress.co.jp/products/web/02.html
 * Text Domain: mw-wp-form-generator-style-doginsoken
 * Created : January 22, 2020
 */

/**
 * For common functions
 */
require_once 'helper.php';

/**
 * List of all seminar and participant
 */
require_once 'all-form-list.php';

/**
 * For each seminar list
 */
require_once 'form-list.php';

/**
 * Detail for each list
 */
require_once 'form-detail.php';

/**
 * セミナーのお申し込みデータ
 */
class PPC_SEMINAR
{
    /**
     * プラグインパス
     *
     * @var string
     */
    public $plugin_path;

    /**
     * プラグインパス
     *
     * @var string
     */
    public $plugin_url;

    /**
     * Common functions
     */
    public $helper;

    /**
     * contructor
     */
    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);

        $this->helper = new Helper;

        // add pages
        add_action('admin_menu', [$this, 'ppc_add_page'], 999);

        // add columns
        add_action('manage_seminar_posts_columns', [$this, 'ppc_add_seminar_columns']);

        add_action('manage_mw-wp-form_posts_custom_column', [$this, 'ppc_add_mw_wp_form_columns_data'], 10, 2);

        // add data to columns
        add_action('manage_seminar_posts_custom_column', [$this, 'ppc_add_seminar_columns_data'], 10, 2);
    }

    /**
     * To add pages for the 一覧と詳細
     *
     * @return
     */
    public function ppc_add_page()
    {
        $all_form_list = new All_Form_List($this->helper, $this->plugin_path, $this->plugin_url);

        // お申し込み全て一覧
        add_submenu_page(
            'edit.php?post_type=seminar',
            'お申し込み全データ',
            'お申し込み全データ',
            'edit_pages',
            'ppcallseminardata',
            [$all_form_list, 'ppc_admin_form_all_data']
        );

        $form_list = new Form_List($this->helper, $this->plugin_path, $this->plugin_url);

        // お申し込み一覧（各セミナー）
        add_menu_page(
            'お申し込み一覧データ',
            'お申し込み一覧データ',
            'edit_pages',
            'ppcformdata',
            [$form_list, 'ppc_admin_form_list_data'],
        );

        $form_detail = new Form_Detail($this->helper, $this->plugin_path, $this->plugin_url);

        // お申し込み詳細
        add_menu_page(
            'お申し込み詳細データ',
            'お申し込み詳細データ',
            'edit_pages',
            'ppcformdetaildata',
            [$form_detail, 'ppc_admin_form_detail_data'],
        );

        remove_menu_page('ppcformdata');
        remove_menu_page('ppcformdetaildata');
        remove_submenu_page('edit.php?post_type=mw-wp-form', 'mw-wp-form-save-data');
        remove_submenu_page('edit.php', 'mw-wp-form-save-data');
        remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=category');
        remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=post_tag');
    }

    /**
     * Add columns in seminar page list
     * {home_url}/manager/wp-admin/admin.php?page=ppcformdata&seminar_id={seminar_id}
     *
     * @param array $columns Default table columns
     */
    public function ppc_add_seminar_columns($columns)
    {
        $new_columns = array();
        foreach ($columns as $column_name => $column_display_name) {
            if ($column_name == 'date') {
                $new_columns['seminar_code'] = 'セミナーコード';
                $new_columns['form_id']      = 'フォームID';
                $new_columns['category']     = 'カテゴリー';
                $new_columns['category']     = 'カテゴリー';
                $new_columns['form_data']    = 'お申し込みデータ';
                $new_columns['form_status']  = 'お申し込みステータス';
            }
            $new_columns[$column_name] = $column_display_name;
        }
        return $new_columns;
    }

    /**
     * Change data on フォームIDカラム
     *
     * @param string $column
     * @param int $post_id
     * @return
     */
    public function ppc_add_mw_wp_form_columns_data($column, $post_id)
    {
        if ($column === 'mwform_form_key') {
            echo '<input style="text-align:center" type="text" name="post_id" value="' . $post_id . '" readonly>';
        }
    }

    /**
     * Add data to columns in
     * {home_url}/manager/wp-admin/admin.php?page=ppcformdata&seminar_id={seminar_id}
     *
     * @param array $columns Default table columns
     * @param int   $post_id 投稿ID
     */
    public function ppc_add_seminar_columns_data($column, $post_id)
    {
        switch ($column) {
                // セミナーコード
            case 'seminar_code':
                $val = get_field('ppc_seminar_code', $post_id);
                if (!empty($val)) {
                    echo $val;
                }
                break;

                // フォームID
            case 'form_id':
                $val = get_field('application_form_id', $post_id);
                if (!empty($val)) {
                    echo '<a href="' . get_admin_url() . 'post.php?post=' . $val . '&action=edit" target="_blank">' . $val . '</a>';
                }
                break;

                // カテゴリー
            case 'category':
                $term = get_the_terms($post_id, 'seminar_cat');
                if (!empty($term[0])) {
                    echo $term[0]->name;
                }
                break;

                // ステータス「受付中か受付終了」
            case 'form_status':
                $html = $this->helper->status_html($post_id);
                echo $html;
                break;

                // 何人
            case 'form_data':
                echo '<a href="' . get_admin_url() . 'admin.php?page=ppcformdata&seminar_id=' . $post_id . '">' . ppc_get_participant_total($post_id) . '／' . get_field('teiin', $post_id) . '　参加者</a>';
                break;

            default:
                break;
        }
    }
}
$PPC_SEMINAR = new PPC_SEMINAR;
