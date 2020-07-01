<?php
/*
Plugin Name: 野球場・グラウンド カレンダー入力
Plugin URI: http://www.pripress.co.jp/products/web/02.html
Description: Use-このプラグインは、野球場のデータの入力用です。
Version: 1.0
Author: Pripress チーム
Author URI: http://www.pripress.co.jp/corporate/outline.html
*/
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This will put the 野球場 in the menu
 */
function my_admin_menu(){
    $current_user = wp_get_current_user();
    $roles = $current_user->roles;
    if ($roles[0] === 'administrator') {
        add_submenu_page(
        'edit.php?post_type=stadium'
        , __('カレンダー','stadium')
        , __('カレンダー','stadium')
        , 'read'
        , 'stadium_page'
        , 'stadium_page');
    } elseif ($roles[0] === 'contributor') {
        $user_type = get_user_meta($current_user->ID, 'restaurant_user_type');
        if ( empty( $user_type[0] ) ) {
            return;
        }

        $exp = explode(', ', $user_type[0]);
        if ( in_array('stadium_1' ,$exp) || in_array('stadium_2' ,$exp) ) {
            add_menu_page( '野球場カレンダー', '野球場カレンダー', 'read', 'stadium_page', 'stadium_page', 'dashicons-tickets-alt', 14 );
            return;
        }
    }
}
add_action('admin_menu', 'my_admin_menu');

/**
 * This will display the stadium page
 * into the admin page.
 */
function stadium_page() {
    global $pagenow;
    $page = empty($_GET['page']) ? '' : $_GET['page'] ;

    if ($page !== 'stadium_page') {
        return;
    }

    wp_enqueue_style( 'stadium.css', plugin_dir_url(__FILE__) . 'assets/css/stadium.css', array(), false, 'all' );
    wp_enqueue_style( 'bootstrap.css', plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css', array(), false, 'all' );
    wp_enqueue_style( 'bootstrap.min.css', plugin_dir_url(__FILE__) . 'assets/css/bootstrap-datepicker3.standalone.min.css', array(), false, 'all' );
    wp_enqueue_script( 'jquery', home_url('/') . 'common/js/jquery-2.1.3.min.js' , array('jQuery') );
    wp_enqueue_script( 'calendar', plugin_dir_url(__FILE__) . 'assets/js/stadium.js' );
    wp_enqueue_script( 'popper', plugin_dir_url(__FILE__) . 'assets/js/popper.min.js' );
    wp_enqueue_script( 'bootstrap', plugin_dir_url(__FILE__) . 'assets/js/bootstrap.min.js' );
    wp_enqueue_script( 'datepicker', plugin_dir_url(__FILE__) . 'assets/js/bootstrap-datepicker.min.js' );
    ?>
    <div class="wrap">
        <h1>野球場・グラウンド</h1>
        <div id="stadium">
            <div class="overlay display-none"></div>
            <div class="block" style="margin-bottom: 30px; width: 50%">
                <div class="blockpad search-box">
                    <h2>選んでください</h2>
                    <table class="table table-bordered calendar_input">
                        <tbody>
                            <tr>
                                <?php
                                    $current_user = wp_get_current_user();
                                    $roles        = $current_user->roles;
                                    $usermeta     = get_user_meta($current_user->ID, 'restaurant_user_type');
                                    $usertype     = !empty($usermeta[0]) ? explode(', ', $usermeta[0]) : '';
                                    if ($roles[0] === 'administrator' || empty($usermeta) || count($usertype) > 1):
                                ?>
                                <th>タイプ</th>
                                <td>
                                    <label class="radio-inline" style="margin-bottom:0px !important;">
                                        <input type="radio" name="stadium_type" value="1" checked="checked">
                                    拓北野球場
                                    </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <label class="radio-inline" style="margin-bottom:0px !important;">
                                        <input type="radio" name="stadium_type" value="2">
                                    川下グラウンド
                                    </label>
                                    <span class="error-type error"></span>
                                </td>
                            <?php else:?>
                                <th>タイプ</th>
                                <td>
                                    <?php
                                        $stadium_label = $usermeta[0] === 'stadium_1' ? '拓北野球場' : '川下グラウンド' ;
                                        $stadium_value = $usermeta[0] === 'stadium_1' ? '1' : '2' ;
                                        echo $stadium_label;
                                    ?>
                                    <input type="hidden" name="stadium_type" value="<?php echo $stadium_value;?>">
                                </td>
                            <?php endif;?>
                            </tr>
                            <tr>
                                <th>年月 <span class="require">必須</span></th>
                                <td>
                                    <input type="text" class="form-control" id="datepicker">
                                    <span class="error-date error"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="register-btn" style="text-align: right">
                        <input type="button" class="btn btn-info register" value="検索" style="width: 100px">
                    </div>
                </div>
            </div><!-- block end -->
            <div class="block calendar-display display-none"></div><!-- block end -->
            <div class="modal fade" id="crudModal" tabindex="-1" role="dialog" aria-labelledby="crudModalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document" style="margin-top: 150px">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">登録</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body"></div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
                    <button type="button" class="btn btn-primary crud">登録する</button>
                  </div>
                </div>
              </div>
            </div>
            </div>
            <!-- block end -->
    <input type="hidden" name="site_url" value="<?php echo site_url();?>">
    <input type="hidden" name="plugin_dir" value="<?php echo plugin_dir_url(__FILE__); ?>">
    <?php
}