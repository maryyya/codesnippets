<?php

/*
Plugin Name: Hot Chilli
Plugin URI: http://www.pripress.co.jp/products/web/02.html
Description: This plugin contains the adding of yamagishi members and files.
Author: Pripress チーム
Author URI: http://www.pripress.co.jp/corporate/outline.html
Version: 1.0
*/

if ( !is_admin() ) {
    return false;
}

/**
 * The member class
 */
require_once( 'class.hc-member.php' );

/**
 * The file class
 */
require_once( 'class.hc-file.php' );

/**
 * The login class
 */
require_once( 'class.hc-login.php' );

/**
 * The download class
 */
require_once( 'class.hc-download.php' );

/**
 * This will put the 会員の皆様 in the menu
 */
function hc_menu() {
    // 会員メニュー
    add_menu_page(
        '会員の皆様'
        , '会員の皆様'
        , 'manage_options'
        , 'hc_list'
        , 'hc_list'
        , 'dashicons-groups'
        , 14
    );

    // メンバー登録
    add_submenu_page(
        'hc_list'
        , '新規追加'
        , '新規追加'
        , 'manage_options'
        , 'hc_register'
        , 'hc_register'
    );

    // メンバー詳細
    add_submenu_page(
        'hc_list'
        , '詳細ページ'
        , '詳細ページ'
        , 'manage_options'
        , 'hc_detail'
        , 'hc_detail'
    );

    // ファイル一覧
    add_submenu_page(
        'hc_list'
        , 'ファイル一覧'
        , 'ファイル一覧'
        , 'manage_options'
        , 'hc_file_list'
        , 'hc_file_list'
    );

    // ファイル一覧
    add_submenu_page(
        'hc_list'
        , 'ファイル詳細'
        , 'ファイル詳細'
        , 'manage_options'
        , 'hc_file_detail'
        , 'hc_file_detail'
    );

    // ファイル登録
    add_submenu_page(
        'hc_list'
        , 'ファイル登録'
        , 'ファイル登録'
        , 'manage_options'
        , 'hc_file_register'
        , 'hc_file_register'
    );

    // ファイルのメンバー登録
    add_submenu_page(
        'hc_list'
        , 'ファイルのメンバー登録'
        , 'ファイルのメンバー登録'
        , 'manage_options'
        , 'hc_file_member_register'
        , 'hc_file_member_register'
    );

    // ファイルのメンバー一覧
    add_submenu_page(
        'hc_list'
        , 'ファイルのメンバー一覧'
        , 'ファイルのメンバー一覧'
        , 'manage_options'
        , 'hc_file_member'
        , 'hc_file_member'
    );
}

add_action('admin_menu', 'hc_menu');

/**
 * Hide the submenu
 */
function hc_hide_submenu() {
    echo '<style>#toplevel_page_hc_list ul li:nth-child(3), #toplevel_page_hc_list ul li:nth-child(4), #toplevel_page_hc_list ul li:nth-child(6), #toplevel_page_hc_list ul li:nth-child(7), #toplevel_page_hc_list ul li:nth-child(8), #toplevel_page_hc_list ul li:nth-child(9) { display:none };</style>';
}

add_action('admin_enqueue_scripts', 'hc_hide_submenu');

/**
 * This function displays the user's list.
 * This is the intial page for the 会員メニュー page.
 * It is an html page.
 *
 * @return html
 */
function hc_list() {
    // instance for the hc member class
    $hc          = new HC_Member();

    // get all the config data
    $hc_config   = hc_config();

    // limit for the pagination
    $limit       = $hc_config['limit'];

    // get the page number
    $page        = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;

    // get orderby
    $orderby     = isset( $_GET['orderby'] ) && strlen( $_GET['orderby'] ) > 0 ? $_GET['orderby'] : '';

    // get order
    $order       = isset( $_GET['order'] ) && strlen( $_GET['order'] ) > 0 ? $_GET['order'] : '';

    // get the search
    $search      = isset( $_GET['search'] ) && strlen( $_GET['search'] ) ? $_GET['search'] : '';

    // offset for current page
    $offset      = ( $page - 1 ) * $limit;

    // get all the member list except for those that are deleted
    $member_list = $hc->get_member_list( $offset, $limit, $orderby, $order, $search );

    // get the total count for all the members
    $total_item = empty( $member_list[0] ) ? 0 : $member_list[1];

    // page number
    $pagenum    = empty( $member_list[0] ) ? 0 : $page;

    // total number of pages
    $total_page = empty( $member_list[0] ) ? 0 : ceil( $member_list[1] / $limit );

    // get the previous page for the current page
    $prev_page  = max( ( $page - 1 ), 0 );

    // get the next page for the current page
    $next_page  = $page + 1;

    $order_val = 'desc';
    if ( !empty( $orderby ) && !empty( $order ) ) {
        echo '<style>.hc-sort-'.$orderby.' .hc-sort{visibility:visible}</style>';
        echo '<input type="hidden" name="hc-sort-hidden" value="'.$order.'">';
        echo '<input type="hidden" name="hc-sort-column" value="'.$orderby.'">';
        if ( $order === 'desc' ) {
            echo '<style>.hc-sort-'.$orderby.' .sorting-indicator:before{content:"\f140" !important}</style>';

            if ( $orderby === 'loginid' || $orderby === 'email' ) {
                // asort($cars, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
            }
        }

        $order_val = $order === 'desc' ? 'asc' : 'desc';
    } else {
        echo '<style>.hc-sort-loginid .hc-sort{visibility:visible}</style>';
    }

    // queue the css
    wp_enqueue_style( 'hc', plugin_dir_url( __FILE__ ) . 'assets/css/hc.css', array(), false, 'all' );

    // queue the js
    hc_ajax( 'hc-member.js', 'hc_member_search_response' );
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">ファイルダウンロードメンバー一覧</h1>
        <a href="<?php echo site_url();?>/wp-admin/admin.php?page=hc_register" class="page-title-action">新規追加</a>
        <hr class="wp-header-end"><br>
        <p class="hc-search-box">
            <form action="" method="GET">
                <input type="hidden" name="page" value="hc_list">
                <input type="search" id="hc-search-memInput" name="search" value="">
                <input type="submit" id="hc-search-memBtn" class="button" value="検索">
            </form>
        </p>
        <div>
            <table class="wp-list-table widefat fixed striped pages">
                <thead>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url( admin_url() . add_query_arg( hc_query( $page, 'loginid', $order_val ), 'admin.php' ) );?>" class="hc-sort-loginid">
                                <span>ログインID</span>
                                <span class="hc-sort sorting-indicator"></span>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( admin_url() . add_query_arg( hc_query( $page, 'email', $order_val ), 'admin.php' ) );?>" class="hc-sort-email">
                                <span>メールアドレス</span>
                                <span class="hc-sort sorting-indicator"></span>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( admin_url() . add_query_arg( hc_query( $page, 'date', $order_val ), 'admin.php' ) );?>" class="hc-sort-date">
                                <span>作成日時</span>
                                <span class="hc-sort sorting-indicator"></span>
                            </a>
                        </td>
                    </tr>
                </thead>
                <tbody id="the-list">
                    <?php if ( !empty( $member_list[0] ) ) :
                    foreach( $member_list[0] as $value ): ?>
                    <tr>
                        <td>
                            <strong>
                                <a class="row-title" href="<?php echo esc_url( admin_url() . add_query_arg( array( 'page' => 'hc_detail', 'mem_id' => $value->ID, 'action' => 'edit'), 'admin.php' ) );?>"><?php echo esc_html( $value->loginid ); ?></a>
                            </strong>
                        </td>
                        <td><?php echo esc_html( $value->email ); ?></td>
                        <td><?php echo esc_html( $hc->convert_japanese_date( $value->date ) ); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <div class="tablenav bottom">
            <div class="alignleft actions"></div>
            <div class="tablenav-pages"><span class="displaying-num"><?php echo $total_item; ?>個の項目</span>
                <span class="pagination-links">
                    <!-- before page start -->
                    <?php if ( $prev_page > 0 ): ?>
                    <a href="<?php echo esc_url( admin_url() . add_query_arg( hc_query( 1, $orderby, $order ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">«</span>
                    </a>
                    <a href="<?php echo esc_url( admin_url() . add_query_arg( hc_query( $prev_page, $orderby, $order ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">‹</span>
                    </a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <?php endif; ?>
                    <!-- before page end -->

                    <span id="table-paging" class="paging-input">
                        <span class="tablenav-paging-text"><?php echo $pagenum; ?> / <span class="total-pages"><?php echo $total_page; ?></span></span>
                    </span>

                    <!-- next page start -->
                    <?php if ( $total_page > $page ): ?>
                    <a class="next-page" href="<?php echo  esc_url( admin_url() . add_query_arg( hc_query( $next_page, $orderby, $order ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="last-page" href="<?php echo  esc_url( admin_url() . add_query_arg( hc_query( $total_page, $orderby, $order ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">»</span>
                    </a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                    <?php endif; ?>
                    <!-- next page end -->
                </span>
            </div>
            <br class="clear">
        </div>
    </div>
<?php
}

/**
 * Register new member
 * This function contains html code.
 *
 * @return html
 */
function hc_register() {
    wp_enqueue_style( 'hc', plugin_dir_url( __FILE__ ) . 'assets/css/hc.css', array(), false, 'all' );
    hc_ajax( 'hc-member.js', 'hc_member_response' );
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">新規追加メンバー</h1>
        <?php hc_input_html( 'register' ); ?>
    </div>
<?php
}

/**
 * Register new member
 * This function contains html code.
 *
 * @return html
 */
function hc_detail() {
    // instance for the hc member class
    $hc        = new HC_Member();

    // get all the config data
    $hc_config = hc_config();

    // check if there are no parameters
    if ( empty( $_GET ) ) {
        wp_redirect( admin_url().'admin.php?page=hc_list' );
        exit;
    }

    // check if member id and action is not on get
    if ( !isset( $_GET['mem_id'] ) && !isset( $_GET['action'] ) ) {
        wp_die( hc_get_msg( 'ERR-15' ) );
        exit();
    }

    // check if get action is equal to edit
    if ( isset( $_GET['action'] ) && $_GET['action'] !== 'edit' ) {
        wp_die( hc_get_msg( 'ERR-16' ) );
        exit();
    }

    // check if member id exists
    if ( !$hc->check_data_existence( $_GET['mem_id'], 'ID', '%d' ) ) {
        wp_die( hc_get_msg( 'ERR-05' ) );
        exit();
    }

    // check if member id is deleted
    if ( !$hc->check_member_deleted( $_GET['mem_id'] ) ) {
        wp_die( hc_get_msg( 'ERR-06' ).'<br><a href="'.admin_url().'admin.php?page=hc_list">一覧ページに戻る。</a>' );
        exit();
    }

    // get member detail
    $mem_data = $hc->get_member_detail( $_GET['mem_id'] );
    if ( !$mem_data ) {
        wp_die( hc_get_msg( 'ERR-17' ) );
        exit();
    }

    // this will enqueue the css and the js files.
    wp_enqueue_style( 'hc', plugin_dir_url( __FILE__ ) . 'assets/css/hc.css', array(), false, 'all' );
    hc_ajax( 'hc-member.js', 'hc_member_dtl_response' );
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">メンバー編集</h1>
        <a href="<?php echo esc_url( admin_url() . add_query_arg( array( 'page' => 'hc_register' ), 'admin.php' ) ) ?>" class="page-title-action">新規追加</a>
        <hr class="wp-header-end">
        <?php if ( isset( $_GET['from_pg'] ) ):

        // pattern for hc_file_member page
        $pattern1 = '/^hc_file_member-\d+$/';

        // pattern for hc_file_member_register page
        $pattern2 = '/^hc_file_member_register-\d+$/';

        // check if pattern matches
        if( preg_match( $pattern1, $_GET['from_pg'] ) === 1 || preg_match( $pattern2, $_GET['from_pg'] ) === 1 ):
            // get the page name
            $page_data = explode( '-', $_GET['from_pg'] );
            $page_name = $page_data[0] === 'hc_file_member' ? 'ファイルとメンバーの紐付けページ' : 'ファイルとメンバーの紐付け登録ページ';
        ?>
        <p><a href="<?php echo site_url();?>/wp-admin/admin.php?page=<?php echo esc_html( $page_data[0] ).'&file_id='.esc_html( $page_data[1] );?>">← <?php echo esc_html( $page_name );?></a></p>
        <?php endif; endif; ?>

        <?php hc_input_html( 'detail', $mem_data ); ?>
    </div>
    <input type="hidden" name="memid" value="<?php echo esc_html( $mem_data->ID );?>">
<?php
}

/**
 * This function contains the loader
 * and the confirm modal.
 *
 * @param  string $type     'register' for 登録ページ and 'detail' for 詳細ページ
 * @param  array  $mem_data data for 詳細ページ
 * @return html
 */
function hc_input_html( $type, $mem_data = array() ) {
    $btn_type = $type === 'register' ? 'hc-reg-register' : 'hc-detail-register';
    $name = $type === 'register' ? '登録' : '更新';
    $data = array(
        'loginid'  => empty( $mem_data->loginid ) ? '' : $mem_data->loginid,
        'email'    => empty( $mem_data->email ) ? '' : $mem_data->email,
        'password' => empty( $mem_data->password ) ? '' : $mem_data->password,
    );
?>
        <div class="hc-inside">
            <?php if( $type === 'register' ): ?>
            <div class="hc-server-error error">
            <?php else: ?>
            <div class="hc-server-error notice is-dismissible">
            <?php endif; ?>
                <p></p>
            </div>
            <div class="hc-field hc-loginid-div">
                <label class="hc-label">ログインID</label>
                <input type="text" name="member_loginid" placeholder="全角XXX文字程度" value="<?php echo esc_html( $data['loginid'] );?>">
                <span class="hc-error hc-loginid-error"></span>
            </div>
            <div class="hc-field">
                <label class="hc-label">メールアドレス</label>
                <input type="text" name="member_email" placeholder="半角英数記号のみ" value="<?php echo esc_html( $data['email'] );?>">
                <span class="hc-error hc-email-error"></span>
            </div>
            <div class="hc-field">
                <label class="hc-label">パスワード</label>
                <input type="password" name="member_password" placeholder="半角英数記号のみ（8桁以上）" value="<?php echo esc_html( $data['password'] );?>">
                <label><input type="checkbox" name="show-password" value="1">パスワードを表示</label>
                <span class="hc-error hc-password-error"></span>
            </div>
            <?php if( $type === 'register' ): ?>
            <div class="hc-field">
                <input type="button" class="hc-btn hc-btn-blue" id="member_register" value="登録">
            </div>
            <?php else: ?>
            <div class="hc-field">
                <input type="button" class="hc-btn hc-btn-blue" id="member_update" value="更新">
                <input type="button" class="hc-btn hc-btn-red" id="member_delete" value="削除">
            </div>
            <?php endif; ?>
        </div>
        <div id="loaderMemberModal" class="hc-modal">
            <!-- Modal content -->
            <div class="hc-modal-content">
               <img src="<?php echo plugin_dir_url( __FILE__ );?>assets/img/loading.gif">
            </div><!-- Modal content end -->
        </div><!-- The Modal end -->
        <div id="hc-hidden-form"></div>
        <div id="confirmMemberModal" class="hc-modal">
            <!-- Modal content -->
            <div class="hc-modal-content">
                <div class="hc-modal-content-inside">
                    <div class="hc-container">
                        <div class="hc-modal-container-inside hc-modal-body"><?php echo $name;?>してもよろしいですか？</div>
                        <div class="hc-modal-container-inside hc-modal-footer">
                            <button type="button" class="hc-btn-modal hc-btn-register <?php echo $btn_type;?>"><?php echo $name;?></button>
                            <button type="button" class="hc-btn-modal hc-btn-register hc-detail-delete hc-display-none">削除</button>
                            <button type="button" class="hc-btn-modal hc-btn-cancel">キャンセル</button>
                        </div>
                    </div>
                </div>

            </div>
            <!-- Modal content end -->
        </div><!-- The Modal end -->
        <div id="hc-hidden-form"></div>
<?php
}

/**
 * Custom query additional for sort and pagination.
 *
 * @param  int|string $paged     page number
 * @param  string     $type      if login, email, date
 * @param  string     $order_val asc or desc
 * @return array      $arg       argument array to be passed
 */
function hc_query( $paged, $type = '', $order_val = '' ) {
    $arg = array( 'page' => 'hc_list' );

    // for sorting
    if ( !empty( $type ) && !empty( $order_val ) ) {
        $arg['orderby'] = $type;
        $arg['order']   = $order_val;
    }

    // for pagination
    if ( (int)$paged > 1 ) {
        $arg['paged'] = $paged;
    }

    return $arg;
}

/**
 * This will call the ajax function
 *
 * @param  $string $jsfile  js file to be queued
 * @param  $string $action  this is the action that will be called in return for the response of ajax.
 * @return void
 */
function hc_ajax( $jsfile, $action ) {
    // this is to queue the js file
    wp_enqueue_script(
        'hc-ajax-jquery'
        , plugin_dir_url( __FILE__ ) . 'assets/js/' . $jsfile
        , array('jquery')
    );

    // this one is to queue the admin-ajax or making the response.
    wp_localize_script(
        'hc-ajax-jquery'
        , 'hc_ajax'
        , array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'hc_ajax_action' => $action )
    );
}

add_action( 'wp_enqueue_scripts', 'hc_ajax' );

/**
 * This function will return the response
 *
 * @param  array $res Data from the class
 * @return json       This will be the response from the ajax call
 */
function hc_response( $res ) {
    wp_send_json_success(
        json_encode( $res )
    );
}

/**
 * This is for the register page of member.
 *
 * This function will call the member class
 * to get the data. Then call the hc_response
 * function to json encode it. And then passed it
 * back to the js file.
 */
function hc_member_response() {
    $load = new HC_Member();
    $res  = $load->member();

    hc_response( $res );
}
add_action( 'wp_ajax_hc_member_response', 'hc_member_response' );

/**
 * This is for the detail page of the member.
 *
 * This function will call the member class
 * to get the data. Then call the hc_response
 * function to json encode it. And then passed it
 * back to the js file.
 */
function hc_member_dtl_response() {
    $load = new HC_Member();
    $res  = $load->member();

    hc_response( $res );
}
add_action( 'wp_ajax_hc_member_dtl_response', 'hc_member_dtl_response' );

/**
 * This is for the detail page of the member.
 *
 * This function will call the member class
 * to get the data. Then call the hc_response
 * function to json encode it. And then passed it
 * back to the js file.
 */
function hc_member_delete_response() {
    $load = new HC_Member();
    $res  = $load->member();

    hc_response( $res );
}
add_action( 'wp_ajax_hc_member_delete_response', 'hc_member_delete_response' );

/**
 * This is for the detail page of the member.
 *
 * This function will call the member class
 * to get the data. Then call the hc_response
 * function to json encode it. And then passed it
 * back to the js file.
 */
function hc_member_search_response() {
    $load = new HC_Member();
    $res  = $load->member();

    hc_response( $res );
}
add_action( 'wp_ajax_hc_member_search_response', 'hc_member_search_response' );

/**
 * Config file for this plugin
 *
 * @return array this contains all the config data.
 */
function hc_config() {
    return include( 'hc-config.php' );
}

/**
 * This will get the error message
 *
 * @param  string $errnum Error number
 * @param  string $bind   The data to be binded with the msg.
 * @return string         Data bind with errro msg.
 */
function hc_get_msg( $errnum, $bind = '' ) {
    // if errnum is not set
    if ( !isset( $errnum ) ) {
        return '';
    }

    // get the config data
    $hc_config = hc_config();

    // get the message array
    $msg_arr  = $hc_config['msg'];

    // check error number
    if ( !array_key_exists( $errnum, $msg_arr ) ) {
        return '';
    }

    // get the msg
    $msg = $msg_arr[$errnum];

    // check if there is a pattern for binding
    if ( strstr( $msg, '{n}' ) !== false ) {
        mb_regex_encoding('UTF-8');
        // start the replacement
        $msg = mb_ereg_replace('\{n\}', $bind, $msg);
    }

    return $msg;
}

/**
 * This function displays the file's list.
 * This is the initial page for ファイル一覧.
 * It is an html page.
 *
 * @return html
 */
function hc_file_list() {
    // instance for hc file class
    $hc         = new HC_File();

    // get all the config data
    $hc_config  = hc_config();

    // limit for the pagination
    $limit      = $hc_config['limit'];

    // get the page number
    $page       = isset( $_GET['paged'] ) ? sanitize_text_field( intval( $_GET['paged'] ) )  : 1;

    // get the search
    $search     = isset( $_GET['search'] ) && strlen( $_GET['search'] ) > 0 ? sanitize_text_field( $_GET['search'] ) : '';

    // get the tag
    $tag_param      = isset( $_GET['tag'] ) && strlen( $_GET['tag'] ) ? sanitize_text_field( $_GET['tag'] ) : '';
    $tag_s = !in_array( $tag_param, $hc_config['taglist'] ) ? '' : $tag_param;

    // offset for the current page
    $offset     = ( $page - 1 ) * $limit;

    // get all the file list except for those that are deleted
    $file_list  = $hc->get_file_list( $offset, $limit, $search, $tag_s );

    // get total item for list
    $total_item = empty( $file_list[0] ) ? 0 : $file_list[1];

    // page number
    $pagenum    = empty( $file_list[0] ) ? 0 : $page;

    // total number of pages
    $total_page = empty( $file_list[0] ) ? 0 : ceil( $file_list[1] / $limit );

    // get the previous page for the current page
    $prev_page  = max( ( $page - 1 ), 0 );

    // get the next page for the current page
    $next_page  = $page + 1;

    wp_enqueue_style( 'hc', plugin_dir_url( __FILE__ ) . 'assets/css/hc.css', array(), false, 'all' );
?>
    <div class="wrap hc-wrap-file-list">
        <h1 class="wp-heading-inline">ファイルダウンロード一覧</h1>
        <a href="<?php echo site_url();?>/wp-admin/admin.php?page=hc_file_register" class="page-title-action">新規追加</a>
        <hr class="wp-header-end"><br>
        <div class="hc-search tablenav">
            <div class="hc-nav-left">
                <p>検索キーワード</p>
                <form style="display: inline-block;">
                    <input type="hidden" name="page" value="hc_file_list">
                    <input type="text" class="hc-search-file-input" name="search" placeholder="2017年度,リフォームなど" value="<?php echo $search?>">
                    <select style="vertical-align: top;" name="tag">
                        <option value="">タグを選択</option>
                        <?php foreach ( $hc_config['taglist'] as $tag ): ?>
                        <option value="<?php echo $tag; echo $tag === $tag_s ? '" selected="selected' : ''; ?>" ><?php echo $tag; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="submit" id="hc-search" class="button" value="検索">
                </form>
            </div>
            <div class="tablenav-pages hc-nav-right">
                <span class="displaying-num"><?php echo $total_item; ?>個の項目</span>
                <span class="pagination-links">
                    <!-- before page start -->
                    <?php if ( $prev_page > 0 ) : ?>
                    <a href="<?php echo esc_url( admin_url( '/' ).'admin.php?page=hc_file_list' );?>">
                        <span aria-hidden="true">«</span>
                    </a>
                    <a href="<?php echo esc_url( admin_url( '/' ).'admin.php?page=hc_file_list&paged='.$prev_page );?>">
                        <span aria-hidden="true">‹</span>
                    </a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <?php endif; ?>
                    <!-- before page end -->
                    <span class="paging-input">
                        <form style="display: inline-block;">
                            <input type="hidden" name="page" value="hc_file_list">
                            <input class="current-page" id="current-page-selector" type="text" name="paged" value="<?php echo $pagenum; ?>" size="2" aria-describedby="table-paging">
                            <input type="submit" style="position: absolute; left: -9999px"/>
                        </form>
                        <span class="tablenav-paging-text"> /
                            <span class="total-pages"><?php echo $total_page; ?></span>
                        </span>
                    </span>
                     <!-- next page start -->
                    <?php if ( $total_page > $page ): ?>
                    <a class="next-page" href="<?php echo esc_url( admin_url( '/' ).'admin.php?page=hc_file_list&paged='.$next_page ); ?>">
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="last-page" href="<?php echo  esc_url( admin_url( '/' ).'admin.php?page=hc_file_list&paged='.$total_page); ?>">
                        <span aria-hidden="true">»</span>
                    </a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                    <?php endif; ?>
                    <!-- next page end -->
                </span>
            </div>
            <br style="clear: both">
        </div>

        <div class="hc-file-list">
            <ul class="hc-clearfix">
                <?php foreach( $file_list[0] as $value ) :?>
                <li>
                    <div class="hc-wrap">
                        <div class="hc-left">
                            <p class="hc-image"><img src="<?php echo hc_get_file_type( $value->file );?>"></p>
                        </div>
                        <div class="hc-right">
                            <p class="hc-text">
                                <a href="<?php echo admin_url().add_query_arg( array( 'page' => 'hc_file_detail', 'file_id' => $value->ID ), 'admin.php' ); ?>">
                                <?php echo mb_strimwidth( wp_strip_all_tags( $value->title ), 0, 55, '...' ); ?>
                                </a>
                            </p>
                            <span class="hc-file-list-tag"><?php echo $value->tag; ?></span>
                        </div>
                    </div>
                    <a href="<?php echo admin_url().add_query_arg( array( 'page' => 'hc_file_member', 'file_id' => $value->ID ), 'admin.php' ); ?>" class="hc-btn hc-btn-blue hc-margin-r5">アカウント紐付</a>
                    <a href="<?php echo admin_url().add_query_arg( array( 'page' => 'hc_file_detail', 'file_id' => $value->ID ), 'admin.php' ); ?>" class="hc-btn hc-btn-blue">詳細</a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="tablenav bottom">
            <div class="alignleft actions"></div>
            <div class="tablenav-pages"><span class="displaying-num"><?php echo $total_item; ?>個の項目</span>
                <span class="pagination-links">
                    <!-- before page start -->
                    <?php if ( $prev_page > 0 ) : ?>
                    <a href="<?php echo esc_url( admin_url( '/' ).'admin.php?page=hc_file_list' );?>">
                        <span aria-hidden="true">«</span>
                    </a>
                    <a href="<?php echo esc_url( admin_url( '/' ).'admin.php?page=hc_file_list&paged='.$prev_page );?>">
                        <span aria-hidden="true">‹</span>
                    </a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <?php endif; ?>
                    <!-- before page end -->

                    <span id="table-paging" class="paging-input">
                        <span class="tablenav-paging-text"><?php echo $pagenum; ?> / <span class="total-pages"><?php echo $total_page; ?></span></span>
                    </span>

                    <!-- next page start -->
                    <?php if ( $total_page > $page ): ?>
                    <a class="next-page" href="<?php echo esc_url( admin_url( '/' ).'admin.php?page=hc_file_list&paged='.$next_page ); ?>">
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="last-page" href="<?php echo  esc_url( admin_url( '/' ).'admin.php?page=hc_file_list&paged='.$total_page); ?>">
                        <span aria-hidden="true">»</span>
                    </a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                    <?php endif; ?>
                    <!-- next page end -->
                </span>
            </div>
            <br class="clear">
        </div>
    </div>
<?php }

/**
 * This will include the js file for the uploader
 *
 * @return void
 */
function hc_include_myuploadscript() {
    /*
     * I recommend to add additional conditions just to not to load the scipts on each page
     * like:
     * if ( !in_array('post-new.php','post.php') ) return;
     */
    if ( ! did_action( 'wp_enqueue_media' ) ) {
        wp_enqueue_media();
    }

    wp_enqueue_script( 'hc-uploader', plugin_dir_url( __FILE__ ) . 'assets/js/hc-uploader.js', array( 'jquery' ), null, true );
    wp_enqueue_script( 'hc-file-uploader', plugin_dir_url( __FILE__ ) . 'assets/js/hc-file-uploader.js', array( 'jquery' ), null, true );
}

add_action( 'admin_enqueue_scripts', 'hc_include_myuploadscript' );

/**
 * This function contains the image uploader
 *
 * @param  string $name Name of option or name of post custom field.
 * @param  string $value Optional Attachment ID
 * @return string HTML of the Upload Button
 */
function hc_image_uploader_field( $name, $value = '' ) {
    $image      = ' button">イメージを選択';
    $image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
    $display    = 'none'; // display state ot the "Remove image" button

    if ( $image_attributes = wp_get_attachment_image_src( $value, $image_size ) ) {
        // $image_attributes[0] - image URL
        // $image_attributes[1] - image width
        // $image_attributes[2] - image height
        $image = '"><img class="true_pre_image" src="' . $image_attributes[0] . '" style="width:200px;display:block;" / alt="'.$value.'">';
        $display = 'inline-block';
    }

    return '
    <div style="width:150px;">
        <a href="#" class="hc_upload_image_button' . $image . '</a>
        <input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '" />
        <a href="#" class="hc_remove_image_button" style="display:inline-block;display:' . $display . '">画像を削除</a>
    </div>';
}

/**
 * This function contains the file uploader
 *
 * @param  string $name Name of option or name of post custom field.
 * @param  string $value Optional Attachment ID
 * @return string HTML of the Upload Button
 */
function hc_file_uploader_field( $name, $value = '' ) {
    $image      = ' button">ファイルを選択';
    $image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
    $display    = 'none'; // display state ot the "Remove image" button

    if ( $image_attributes = wp_get_attachment_url( (int)$value ) ) {
        $display = 'block';
        $file_name = pathinfo( $image_attributes, PATHINFO_EXTENSION );
        $image = '">';
        $image .= '<div class="hc-thumbnail">';
        $image .= '<div class="hc-centered">';
        $image .= '<img src="'.site_url('/').'wp-includes/images/media/document.png"" / alt="'.$value.'">';
        $image .= '</div>';
        $image .= '<div class="hc-filename">';
        $image .= '<div>'.get_the_title($value).'.'.$file_name.'</div>';
        $image .= '</div>';
        $image .= '</div>';
    }

    return '
    <div style="width:150px;">
        <a href="#" style="text-decoration:none" class="hc_upload_file_button' . $image . '</a>
        <input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '" />
        <a href="#" class="hc_remove_image_button" style="display:inline-block;display:' . $display . '">ファイルを削除</a>
    </div>';
}

/**
 * This function holds the adding
 * of new file. This contains
 * the html page for ファイル登録
 *
 * @return html
 */
function hc_file_register() {
    wp_enqueue_script( 'datepicker', plugin_dir_url( __FILE__ ) . 'assets/js/jquery-ui.min.js', array( 'jquery' ), '1.0', true );
    wp_enqueue_style( 'hc-css', plugin_dir_url( __FILE__ ) . 'assets/css/hc.css', array(), false, 'all' );
    wp_enqueue_style( 'hc-jquery-ui', plugin_dir_url( __FILE__ ) . 'assets/css/jquery-ui.css', array(), false, 'all' );

    hc_ajax( 'hc-file.js', 'hc_file_register_response' );
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">ファイル登録</h1>
        <?php hc_file_input_html( 'register' ); ?>
        <div class="hc-field">
            <input type="button" class="hc-btn hc-btn-blue" id="file_register" value="登録">
        </div>
    </div>
<?php }

/**
 * This is for the register page of member.
 *
 * This function will call the member class
 * to get the data. Then call the hc_response
 * function to json encode it. And then passed it
 * back to the js file.
 */
function hc_file_register_response() {
    $load = new HC_File();
    $res  = $load->file();

    hc_response( $res );
}
add_action( 'wp_ajax_hc_file_register_response', 'hc_file_register_response' );

/**
 * This function holds the html page
 * for the detail page of the file.
 * It contains the file's member list
 * and the file's additional data.
 * It is an html page.
 *
 * @return html
 */
function hc_file_detail() {
    // check if there are no parameters
    if ( empty( $_GET ) ) {
        wp_redirect( admin_url().'admin.php?page=hc_file_list' );
        exit;
    }

    // get all the config data
    $hc_config = hc_config();

    // check if file id and action is not on get
    if ( !isset( $_GET['file_id'] ) && !isset( $_GET['action'] ) ) {
        wp_die( hc_get_msg( 'ERR-25' ) );
        exit();
    }

    // check if get action is equal to edit
    if ( isset( $_GET['action'] ) && $_GET['action'] !== 'edit' ) {
        wp_die( hc_get_msg( 'ERR-16' ) );
        exit();
    }

    // instance for the hc file class
    $hc = new HC_File();

    // sanitize file id
    $file_id = sanitize_text_field( $_GET['file_id'] );

    // check if file id exists
    if ( !$hc->check_data_existence( $file_id ) ) {
        wp_die( hc_get_msg( 'ERR-05' ) );
        exit();
    }

    // get file detail
    $file_data = $hc->get_file_detail( $file_id );
    if ( !$file_data ) {
        wp_die( hc_get_msg( 'ERR-17' ) );
        exit();
    }

    wp_enqueue_script( 'datepicker', plugin_dir_url( __FILE__ ) . 'assets/js/jquery-ui.min.js', array( 'jquery' ), '1.0', true );
    wp_enqueue_style( 'hc-css', plugin_dir_url( __FILE__ ) . 'assets/css/hc.css', array(), false, 'all' );
    wp_enqueue_style( 'hc-jquery-ui', plugin_dir_url( __FILE__ ) . 'assets/css/jquery-ui.css', array(), false, 'all' );

    hc_ajax( 'hc-file.js', 'hc_file_register_response' );
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">ファイル詳細</h1>
        <a href="<?php echo site_url();?>/wp-admin/admin.php?page=hc_file_register" class="page-title-action">新規追加</a>
        <hr class="wp-header-end"><br>
        <div>
            <!-- <strong>パーマリンク:</strong>
            <span id="sample-permalink">
                <a href="<?php //echo site_url().'/member/download/'.$file_id;?>"><?php //echo site_url().'/member/download/'.$file_id;?></a>
            </span><br>
            <span class="hc-red">※ 最初にログインしてください。<a href="<?php //echo site_url().'/member/download/';?>"><?php //echo site_url().'/member/download/';?></a></span> -->
            <br><br>
            <strong>アカウント紐付:</strong>
            <span id="sample-permalink">
                <a href="<?php echo site_url();?>/wp-admin/admin.php?page=hc_file_member&file_id=<?php echo $file_id;?>"><?php echo admin_url().'admin.php?page=hc_file_member&file_id='.esc_html( $file_id ); ?></a>
            </span>
        </div>
        <input type="hidden" name="file_id" value="<?php echo esc_html( $_GET['file_id'] );?>">
        <?php hc_file_input_html( 'detail', $file_data ); ?>
        <div class="hc-field">
            <input type="button" class="hc-btn hc-btn-blue" id="file_update" value="更新">
            <input type="button" class="hc-btn hc-btn-red" id="file_delete" value="削除">
        </div>
    </div>
<?php
}

/**
 * This is for the detail page of file.
 *
 * This function will call the file class
 * to get the data. Then call the hc_response
 * function to json encode it. And then passed it
 * back to the js file. This is for updating the data.
 */
function hc_file_detail_response() {
    $load = new HC_File();
    $res  = $load->file();

    hc_response( $res );
}
add_action( 'wp_ajax_hc_file_detail_response', 'hc_file_detail_response' );

/**
 * This is for the detail page of file.
 *
 * This function will call the file class
 * to get the data. Then call the hc_response
 * function to json encode it. And then passed it
 * back to the js file. This is for deleting the
 * data.
 */
function hc_file_delete_response() {
    $load = new HC_File();
    $res  = $load->file();

    hc_response( $res );
}
add_action( 'wp_ajax_hc_file_delete_response', 'hc_file_delete_response' );

/**
 * Input html for the files
 *
 * @return html
 */
function hc_file_input_html( $type, $file_data = array() ) {
    $hc_config = hc_config();
    $btn_type  = $type === 'register' ? 'hc-file-register-btn' : 'hc-file-detail-btn';
    $name      = $type === 'register' ? '登録' : '更新';

    $data = array(
        'title'    => empty( $file_data->title ) ? '' : $file_data->title,
        'content'  => empty( $file_data->content ) ? '' : $file_data->content,
        'date'     => empty( $file_data->date ) ? '' : $file_data->date,
        'file'     => empty( $file_data->file ) ? '' : $file_data->file,
        'image_id' => empty( $file_data->image_id ) ? '' : $file_data->image_id,
        'tag'      => empty( $file_data->tag ) ? '' : $file_data->tag,
    );

    $date      = empty( $data['date'] ) ? strtotime( date( 'Y/m/d' ) ) : strtotime( $data['date'] );
?>
    <div class="hc-file-register">
        <div class="hc-field hc-title-div">
            <label class="hc-label">タイトル</label>
            <input type="text" class="hc-input-text" name="hc-file-title" value="<?php echo esc_html( $data['title'] );?>">
            <span class="hc-title-error hc-file-error-js"></span>
        </div>
        <div class="hc-field">
            <label class="hc-label">資料概要</label>
            <input type="text" class="hc-input-text" name="hc-file-content" value="<?php echo esc_html( $data['content'] );?>">
            <?php
                // wp_nonce_field( 'hc_detail_nonce_action', 'hc_detail_nonce_field' );
                // wp_editor( stripslashes( html_entity_decode( $data['content'] ) ), 'special_content' );
            ?>
            <span class="hc-content-error hc-file-error-js"></span>
        </div>
        <div class="hc-field">
            <label class="hc-label">発行日</label>
            <input type="text" class="active hc-datepckr" name="hc-file-date" value="<?php echo esc_html( date( 'Y/m/d', $date) );?>" id="hc-file-date" />
            <span class="hc-date-error hc-file-error-js"></span>
        </div>
        <div class="hc-field">
            <label class="hc-label">タグ</label>
            <span class="hc-tag-error hc-file-error-js"></span>
            <ul class="acf-radio-list radio vertical">
                <?php foreach ( $hc_config['taglist'] as $tag ): ?>
                <li>
                    <label>
                        <input type="radio" name="hc-file-tag" value="<?php echo $tag?>" <?php echo ( esc_html( $data['tag'] ) === $tag ) ? 'checked="checked"' : '';?>>タグ<?php echo mb_convert_kana( substr( $tag, 3 ), 'N' );?>
                    </label>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="hc-field">
            <label style="display: block;">ファイル</label><br>
            <?php
                $option_name = 'header_file';
                echo hc_file_uploader_field( $option_name, (int)$data['file'] );
            ?>
            <span class="hc-file-error hc-file-error-js"></span>
        </div>
        <!-- <div class="hc-field">
            <label style="display: block;">サムネイル</label><br> -->
            <?php
                // $option_name = 'header_img';
                // echo hc_image_uploader_field( $option_name, (int)$data['image_id'] );
            ?>
            <!-- <span class="hc-thumbnail-error hc-file-error-js"></span> -->
        <!-- </div> -->
        <div id="loaderMemberModal" class="hc-modal">
            <!-- Modal content -->
            <div class="hc-modal-content">
               <img src="<?php echo plugin_dir_url( __FILE__ );?>assets/img/loading.gif">
            </div><!-- Modal content end -->
        </div><!-- The Modal end -->
        <div id="hc-hidden-form"></div>
        <div id="confirmMemberModal" class="hc-modal">
            <!-- Modal content -->
            <div class="hc-modal-content">
                <div class="hc-modal-content-inside">
                    <div class="hc-container">
                        <div class="hc-modal-container-inside hc-modal-body"><?php echo $name;?>してもよろしいですか？</div>
                        <div class="hc-modal-container-inside hc-modal-footer">
                            <button type="button" class="hc-btn-modal hc-btn-register <?php echo $btn_type;?>"><?php echo $name;?></button>
                            <button type="button" class="hc-btn-modal hc-btn-register hc-detail-delete hc-display-none">削除</button>
                            <button type="button" class="hc-btn-modal hc-btn-cancel">キャンセル</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal content end -->
        </div><!-- The Modal end -->
    </div>
<?php
}

/**
 * This function contains the
 * adding of a new member. This is
 * the html page for the
 * ファイルのメンバー登録
 *
 * @return html
 */
function hc_file_member_register() {

    // check if file_id param is set and not empty
    if ( !isset( $_GET['file_id'] ) && strlen( $_GET['file_id'] ) < 1 ) {
        wp_die( hc_get_msg( 'ERR-26' ) );
        exit();
    }

    wp_enqueue_script( 'datepicker', plugin_dir_url( __FILE__ ) . 'assets/js/jquery-ui.min.js', array( 'jquery' ), '1.0', true );
    wp_enqueue_style( 'hc', plugin_dir_url( __FILE__ ) . 'assets/css/hc.css', array(), false, 'all' );
    hc_ajax( 'hc-file.js', 'hc_file_member_register_response' );

    // sanitize the file id
    $file_id = sanitize_text_field( $_GET['file_id'] );

    // instance for the hc file class
    $hc = new HC_File();

    // check if file id exists
    $file = $hc->check_data_existence( $file_id, 'member-reg' );
    if ( !$file ) {
        wp_die( hc_get_msg( 'ERR-05' ) );
        exit();
    }

    // get all the config data
    $hc_config   = hc_config();

    // limit for the pagination
    $limit       = $hc_config['limit'];

    // get the page number
    $page        = isset( $_GET['paged'] ) ? sanitize_text_field( $_GET['paged'] ) : 1;

    // get the search
    $search      = isset( $_GET['search'] ) && strlen( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';

    // offset for current page
    $offset      = ( $page - 1 ) * $limit;

    // get all the members
    $member_list = $hc->get_file_member_list( $file_id, $offset, $limit, $search );

    // get the total item for all members
    $total_item = empty( $member_list[0] ) ? 0 : $member_list[1];

     // page number
    $pagenum    = empty( $member_list[0] ) ? 0 : $page;

    // total number of pages
    $total_page = empty( $member_list[0] ) ? 0 : ceil( $member_list[1] / $limit );

    // get the previous page for the current page
    $prev_page  = max( ( $page - 1 ), 0 );

    // get the next page for the current page
    $next_page  = $page + 1;
?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php echo esc_html( $file );?>とメンバー紐付け登録</h1>
        <hr class="wp-header-end"><br>
        <p><a href="<?php echo site_url();?>/wp-admin/admin.php?page=hc_file_member&file_id=<?php echo $file_id;?>">← <?php echo esc_html( $file );?>とメンバーの紐付け</a></p>
        <div class="hc-search">
            <form action="" method="GET">
                <input type="hidden" name="page" value="hc_file_member_register">
                <input type="hidden" name="file_id" value="<?php echo $file_id; ?>">
                <input type="text" class="hc-search-file-input" name="search" value="<?php echo esc_html( $search );?>">
                <input type="submit" class="button" value="検索">
            </form>
        </div>
        <input type="hidden" name="file_id" id="file_id" value="<?php echo $file_id;?>">
        <table class="hc-file-member-list wp-list-table widefat fixed striped pages">
            <thead>
                <tr>
                    <th><label>
                            <input type="checkbox" name="hc-member-check-all" value="">
                        </label>ログインID</th>
                    <th>メールアドレス</th>
                    <th width="50px"></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty( $member_list[0] ) ) :
                foreach( $member_list[0] as $member ):?>
                <tr>
                    <td>
                        <label><input type="checkbox" name="hc-member-check" value="<?php echo esc_html( $member->ID );?>"><?php echo esc_html( $member->loginid );?></label>
                    </td>
                    <td><?php echo esc_html( $member->email );?></td>
                    <td><a href="<?php echo admin_url() . add_query_arg( array( 'page' => 'hc_detail', 'mem_id' => $member->ID, 'from_pg' => 'hc_file_member_register-'.esc_html( $file_id )  ), 'admin.php' );?>">詳細</a></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        <span class="file_member_error hc-file-error-js"></span>
        <div class="hc-block">
            <div class="hc-field">
                <input type="button" class="hc-btn hc-btn-blue" id="file_member_register_btn" value="登録">
            </div>
            <div class="tablenav bottom">
                <div class="alignleft actions"></div>
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo $total_item; ?>個の項目</span>
                    <span class="pagination-links">
                    <!-- before page start -->
                    <?php if ( $prev_page > 0 ): ?>
                    <a href="<?php echo esc_url( admin_url() . add_query_arg( array( 'page' => 'hc_file_member_register', 'file_id' => $file_id ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">«</span>
                    </a>
                    <a href="<?php echo esc_url( admin_url() . add_query_arg( array( 'page' => 'hc_file_member_register', 'file_id' => $file_id, 'paged' => $prev_page, 'search' => $search ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">‹</span>
                    </a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <?php endif; ?>
                    <!-- before page end -->

                    <span id="table-paging" class="paging-input">
                        <span class="tablenav-paging-text"><?php echo $pagenum; ?> / <span class="total-pages"><?php echo $total_page; ?></span></span>
                    </span>

                    <!-- next page start -->
                    <?php if ( $total_page > $page ): ?>
                    <a class="next-page" href="<?php echo  esc_url( admin_url() . add_query_arg( array( 'page' => 'hc_file_member_register', 'file_id' => $file_id, 'paged' => $next_page, 'search' => $search ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="last-page" href="<?php echo  esc_url( admin_url() . add_query_arg( array( 'page' => 'hc_file_member_register', 'file_id' => $file_id, 'paged' => $total_page, 'search' => $search ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">»</span>
                    </a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                    <?php endif; ?>
                    <!-- next page end -->
                </span>
                </div>
                <br class="clear">
            </div>
        </div>
        <div id="loaderMemberModal" class="hc-modal">
            <!-- Modal content -->
            <div class="hc-modal-content">
               <img src="<?php echo plugin_dir_url( __FILE__ );?>assets/img/loading.gif">
            </div><!-- Modal content end -->
        </div><!-- The Modal end -->
        <div id="hc-hidden-form"></div>
        <div id="confirmMemberModal" class="hc-modal">
            <!-- Modal content -->
            <div class="hc-modal-content">
                <div class="hc-modal-content-inside">
                    <div class="hc-container">
                        <div class="hc-modal-container-inside hc-modal-body">登録してもよろしいですか？</div>
                        <div class="hc-modal-container-inside hc-modal-footer">
                            <button type="button" class="hc-btn-modal hc-btn-register file-member-btn">登録</button>
                            <button type="button" class="hc-btn-modal hc-btn-cancel">キャンセル</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal content end -->
        </div><!-- The Modal end -->
    </div>
<?php
}

/**
 * This is for the detail page of file.
 *
 * This function will call the file class
 * to get the data. Then call the hc_response
 * function to json encode it. And then passed it
 * back to the js file. This is for deleting the
 * data.
 */
function hc_file_member_register_response() {
    $load = new HC_File();
    $res  = $load->file();

    hc_response( $res );
}
add_action( 'wp_ajax_hc_file_member_register_response', 'hc_file_member_register_response' );

/**
 * This function holds the member
 * page for each image. This function
 * contains html code.
 *
 * @return html
 */
function hc_file_member() {
    // check if file_id param is set and not empty
    if ( !isset( $_GET['file_id'] ) && strlen( $_GET['file_id'] ) < 1 ) {
        wp_die( hc_get_msg( 'ERR-26' ) );
        exit();
    }

    wp_enqueue_style( 'hc', plugin_dir_url( __FILE__ ) . 'assets/css/hc.css', array(), false, 'all' );
    wp_enqueue_script( 'datepicker', plugin_dir_url( __FILE__ ) . 'assets/js/jquery-ui.min.js', array( 'jquery' ), '1.0', true );

    // instance for the hc file class
    $hc = new HC_File();
    $file_id = sanitize_text_field( $_GET['file_id'] );

    // check if file id exists
    $file = $hc->check_data_existence( $file_id );
    if ( !$file ) {
        wp_die( hc_get_msg( 'ERR-05' ) );
        exit();
    }

    // get all the config data
    $hc_config   = hc_config();

    // limit for the pagination
    $limit       = $hc_config['limit'];

    // get the page number
    $page        = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;

    // get the search
    $search      = isset( $_GET['search'] ) && strlen( $_GET['search'] ) ? $_GET['search'] : '';

    // offset for current page
    $offset      = ( $page - 1 ) * $limit;

    // get all the members
    $member_list = $hc->get_file_member( $file_id, $offset, $limit, $search );

    // get the total item for all members
    $total_item = empty( $member_list[0] ) ? 0 : $member_list[1];

     // page number
    $pagenum    = empty( $member_list[0] ) ? 0 : $page;

    // total number of pages
    $total_page = empty( $member_list[0] ) ? 0 : ceil( $member_list[1] / $limit );

    // get the previous page for the current page
    $prev_page  = max( ( $page - 1 ), 0 );

    // get the next page for the current page
    $next_page  = $page + 1;

    hc_ajax( 'hc-file.js', 'hc_file_member_delete_response' );
?>
    <div class="wrap">
        <div class="hc-file-member">
            <h1 class="wp-heading-inline"><?php echo esc_html( $file );?>とメンバーの紐付け</h1>
            <a href="<?php echo admin_url() . add_query_arg( array( 'page' => 'hc_file_member_register', 'file_id' => $file_id ), 'admin.php' );?>" class="page-title-action">新規追加</a>
            <hr class="wp-header-end"><br>
            <p><a href="<?php echo site_url();?>/wp-admin/admin.php?page=hc_file_list">← ファイルダウンロード一覧</a></p>
            <div class="hc-search">
                <form action="" method="GET">
                    <input type="hidden" name="page" value="hc_file_member">
                    <input type="hidden" name="file_id" id="file_id" value="<?php echo $file_id; ?>">
                    <input type="text" class="hc-search-file-input" name="search" value="<?php echo esc_html( $search );?>">
                    <input type="submit" class="button" value="検索">
                </form>
            </div>
            <div class="hc-server-error error"><p></p></div>
            <table class="hc-file-member-list wp-list-table widefat fixed striped pages">
                <thead>
                    <tr>
                        <th>
                            <label>
                                <input type="checkbox" name="hc-member-check-all" value="">
                            </label>ログインID</th>
                        <th>メールアドレス</th>
                        <th width="50px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( !empty( $member_list[0] ) ) :
                    foreach( $member_list[0] as $member ):?>
                    <tr>
                        <td>
                            <label><input type="checkbox" name="hc-member-check" value="<?php echo esc_html( $member->memid );?>"><?php echo esc_html( $member->loginid );?></label>
                        </td>
                        <td><?php echo esc_html( $member->email );?></td>
                        <td>
                            <a href="<?php echo admin_url() . add_query_arg( array( 'page' => 'hc_detail', 'mem_id' => esc_html( $member->memid ), 'from_pg' => 'hc_file_member-'.esc_html( $file_id ) ), 'admin.php' );?>">詳細</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
            <span class="file_member_error hc-file-error-js"></span>
            <div class="hc-block">
                <div class="hc-field">
                    <input type="button" class="hc-btn hc-btn-red" id="file_delete_member" value="削除">
                </div>
                <div class="tablenav bottom">
                    <div class="alignleft actions"></div>
                    <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo $total_item; ?>個の項目</span>
                    <span class="pagination-links">
                    <!-- before page start -->
                    <?php if ( $prev_page > 0 ): ?>
                    <a href="<?php echo esc_url( admin_url() . add_query_arg( array( 'page' => 'hc_file_member', 'file_id' => $file_id ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">«</span>
                    </a>
                    <a href="<?php echo esc_url( admin_url() . add_query_arg( array( 'page' => 'hc_file_member', 'file_id' => $file_id, 'paged' => $prev_page, 'search' => $search ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">‹</span>
                    </a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <?php endif; ?>
                    <!-- before page end -->

                    <span id="table-paging" class="paging-input">
                        <span class="tablenav-paging-text"><?php echo $pagenum; ?> / <span class="total-pages"><?php echo $total_page; ?></span></span>
                    </span>

                    <!-- next page start -->
                    <?php if ( $total_page > $page ): ?>
                    <a class="next-page" href="<?php echo  esc_url( admin_url() . add_query_arg( array( 'page' => 'hc_file_member', 'file_id' => $file_id, 'paged' => $next_page, 'search' => $search ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="last-page" href="<?php echo  esc_url( admin_url() . add_query_arg( array( 'page' => 'hc_file_member', 'file_id' => $file_id, 'paged' => $total_page, 'search' => $search ), 'admin.php' ) ); ?>">
                        <span aria-hidden="true">»</span>
                    </a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                    <?php endif; ?>
                    <!-- next page end -->
                </span>
                </div>
                    <br class="clear">
                </div>
            </div>
        </div>
        <div id="loaderMemberModal" class="hc-modal">
            <!-- Modal content -->
            <div class="hc-modal-content">
               <img src="<?php echo plugin_dir_url( __FILE__ );?>assets/img/loading.gif">
            </div><!-- Modal content end -->
        </div><!-- The Modal end -->
        <div id="hc-hidden-form"></div>
        <div id="confirmMemberModal" class="hc-modal">
            <!-- Modal content -->
            <div class="hc-modal-content">
                <div class="hc-modal-content-inside">
                    <div class="hc-container">
                        <div class="hc-modal-container-inside hc-modal-body">削除してもよろしいですか？</div>
                        <div class="hc-modal-container-inside hc-modal-footer">
                            <button type="button" class="hc-btn-modal file-member-delete-modal-btn hc-btn-register">削除</button>
                            <button type="button" class="hc-btn-modal hc-btn-cancel">キャンセル</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal content end -->
        </div><!-- The Modal end -->
    </div>
<?php
}

/**
 * This is for the detail page of file.
 *
 * This function will call the file class
 * to get the data. Then call the hc_response
 * function to json encode it. And then passed it
 * back to the js file. This is for deleting the
 * data.
 */
function hc_file_member_delete_response() {
    $load = new HC_File();
    $res  = $load->file();

    hc_response( $res );
}
add_action( 'wp_ajax_hc_file_member_delete_response', 'hc_file_member_delete_response' );





