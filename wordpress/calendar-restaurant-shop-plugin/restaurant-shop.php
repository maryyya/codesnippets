<?php
/*
Plugin Name: 食堂・売店施設カレンダー入力
Plugin URI: http://www.pripress.co.jp/products/web/02.html
Description: Use-このプラグインは、食堂・売店施設のデータの入力用です。
Version: 1.0
Author: Pripress チーム
Author URI: http://www.pripress.co.jp/corporate/outline.html
*/
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This function will add the 食堂売店 as a submenu
 */
function add_submenu_page_cpt(){
    add_submenu_page(
    'edit.php?post_type=restaurant_shop'
    , __('カレンダーメニュー表','menu-test')
    , __('カレンダーメニュー表','menu-test')
    , 'read'
    , 'calendar_page'
    , 'calendar_page');
}
add_action('admin_menu', 'add_submenu_page_cpt');

/**
 * To get all the categories for post type
 * restaurant_cat
 *
 * @return object List of categories
 */
function get_all_cat() {
  $cat = get_terms( array(
      'taxonomy'   => 'restaurant_cat',
      'hide_empty' => false,
      'orderby'    => 'id',
      'order'      => 'asc',
  ) );

  return $cat;
}

/**
 * This function will display the
 * crud-ing of the calendar.
 *
 * 54 - 市役所本庁舎
 */
function calendar_page() {
    if ($_GET['page'] !== 'calendar_page') {
        site_url();
    }

    wp_enqueue_style( 'calendar.css', plugin_dir_url(__FILE__) . 'assets/css/calendar.css', array(), false, 'all' );
    wp_enqueue_style( 'bootstrap.css', plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css', array(), false, 'all' );
    wp_enqueue_style( 'bootstrap.min.css', plugin_dir_url(__FILE__) . 'assets/css/bootstrap-datepicker3.standalone.min.css', array(), false, 'all' );
    wp_enqueue_script( 'jquery', home_url('/') . 'common/js/jquery-2.1.3.min.js' , array('jQuery') );
    wp_enqueue_script( 'calendar', plugin_dir_url(__FILE__) . 'assets/js/calendar.js' );
    wp_enqueue_script( 'popper', plugin_dir_url(__FILE__) . 'assets/js/popper.min.js' );
    wp_enqueue_script( 'bootstrap', plugin_dir_url(__FILE__) . 'assets/js/bootstrap.min.js' );
    wp_enqueue_script( 'datepicker', plugin_dir_url(__FILE__) . 'assets/js/bootstrap-datepicker.min.js' );
?>
  <div class="wrap">
    <h1>カレンダーメニュー表</h1>
    <div id="restaurant_shop">
      <div class="overlay display-none"></div>
      <div class="block" style="margin-bottom:30px; width: 50%">
        <div class="blockpad select-action">
          <h2>選んでください</h2>
          <div class="">
            <table class="table table-bordered calendar_input">
              <tbody>
                <tr>
                  <th scope="row">カテゴリー<span class="require">必須</span></th>
                  <td>
                    <?php
                    $current_user = wp_get_current_user();
                    $roles        = $current_user->roles;
                    $usermeta     = get_user_meta($current_user->ID, 'restaurant_user_type');
                    if ($roles[0] === 'administrator' || empty($usermeta)):
                    ?>
                    <select class="form-control" id="term-search">
                      <option></option>
                      <?php
                          foreach(get_all_cat() as $terms):
                          if (strpos($terms->name, '売店') === false || (int)$terms->term_id === 54) :

                          if ($terms->parent > 0):
                      ?>
                      <option value="<?php echo $terms->term_id;?>"><?php echo custom_get_cat_name($terms->parent).$terms->name;?></option>
                      <?php endif;endif;endforeach;?>
                    </select>
                    <span class="error-term error"></span>
                    <?php else:
                    $exp = explode(', ', $usermeta[0]);
                      if (count($exp) > 1) {
                        echo '<select class="form-control" id="term-search">';
                        echo '<option></option>';
                        foreach ($exp as $key => $value) {
                          $dtl = get_term_by('id', $value, 'restaurant_cat');
                          $parentdtl = get_term_by('id', $dtl->parent, 'restaurant_cat');
                          if (strpos($dtl->name, '売店') === false || (int)$value === 54) {

                        ?>
                          <option value="<?php echo $value?>"><?php echo $parentdtl->name.$dtl->name;?></option>
                        <?php }}
                        echo '</select>';
                      } else {
                      $parent = get_term_by('id', $usermeta[0], 'restaurant_cat');
                      $mom = get_term_by('id', $parent->parent, 'restaurant_cat');
                      echo $mom->name.$parent->name;
                    ?><input type="hidden" id="term_id" value="<?php echo $usermeta[0];?>"><input type="hidden" id="term" value="<?php echo $mom->name.$parent->name?>">
                    <?php } endif;?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="search-div display-none" style="text-align: right">
            <input type="button" class="btn btn-info search" value="検索" style="width: 100px">
          </div>
          <div class="search-div" style="text-align: right">
            <!-- <input type="button" class="btn btn-info edit" value="登録" style="width: 100px"> -->
            <input type="button" class="btn btn-info edit" value="検索" style="width: 100px">
          </div>
        </div>
        <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="margin-top: 150px">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">確認</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body"></div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
                <button type="button" class="btn btn-primary add">追加する</button>
              </div>
            </div>
          </div>
        </div>
        <!-- Modal end -->
        <div class="modal fade" id="successModal" style="margin-top: 150px;">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-body">
                <p>登録しました。</p>
              </div>
              <div class="modal-footer" style="text-align: right">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- block end -->
      <div class="block calendar-display display-none"></div>
      <!-- block end -->
    </div>
    <input type="hidden" name="site_url" value="<?php echo site_url();?>">
    <input type="hidden" name="plugin_dir" value="<?php echo plugin_dir_url(__FILE__); ?>">
  </div>
<?php
}