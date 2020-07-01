<?php

/**
 * Template for displaying
 * the list page for
 * each お申し込みデータ。
 */
?>
<div class="wrap sie-body">
    <h1 class="wp-heading-inline">お申し込み全データ</h1>
    <form method="post" action="" class="padding-top25">
        <div class="search_list top wp-list-table">
            <h2 class="screen-reader-text">投稿リストナビゲーション</h2>
            <table>
                <tr class="radio_th">
                    <th>セミナーカテゴリー</th>
                    <td>
                        <ul><?php foreach (array_slice($category_list, 0, 3) as $cat) {
                                $checked = isset($post_param['category']) && (int) $post_param['category'] === (int) $cat->term_id ? 'checked' : '';
                                echo "<li><label><input type='radio' name='category' value='{$cat->term_id}' {$checked}>{$cat->name}</label></li>";
                            } ?>
                        </ul>
                    </td>
                    <td colspan="2">
                        <ul><?php foreach (array_slice($category_list, 3) as $cat) {
                                $checked = isset($post_param['category']) && (int) $post_param['category'] === (int) $cat->term_id ? 'checked' : '';
                                echo "<li><label><input type='radio' name='category' value='{$cat->term_id}' {$checked}>{$cat->name}</label></li>";
                            } ?>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>お申し込み受付開始時刻</th>
                    <td><input type="text" id="form_start_date" name="form_start_date" value="<?php echo $form_start_date; ?>">　～　</td>
                    <th>お申し込み受付終了時刻</th>
                    <td><input type="text" id="form_end_date" name="form_end_date" value="<?php echo $form_end_date; ?>"></td>
                </tr>
                <tr>
                    <th>セミナー開始日時</th>
                    <td><input type="text" id="seminar_start_date" name="seminar_start_date" value="<?php echo $seminar_start_date; ?>">　～　</td>
                    <th>セミナー終了日時</th>
                    <td><input type="text" id="seminar_end_date" name="seminar_end_date" value="<?php echo $seminar_end_date; ?>"></td>
                </tr>
                <tr>
                    <th>受講料</th>
                    <td>
                        <label class="margin-right10"><input type="radio" name="jukouryou" value="free" <?php echo $free; ?>>無料</label>
                        <label><input type="radio" name="jukouryou" value="paid" <?php echo $paid; ?>>有料</label>
                    </td>
                    <th>定員</th>
                    <td>
                        <input type="number" id="teiin" name="teiin" value="<?php echo $teiin; ?>" min="1" step="any">
                    </td>
                </tr>
                <tr>
                    <th>セミナーコード</th>
                    <td colspan="3"><input type="text" id="seminar_code" name="seminar_code" value="<?php echo $seminar_code; ?>"></td>
                </tr>
                <tr class="paid_type <?php echo $jukouryou === 'paid' ? '' : 'display-none'; ?>">
                    <th>会員</th>
                    <td>
                        <input type="number" id="member" name="member" value="<?php echo $member; ?>" min="1" step="any">
                    </td>
                    <th>一般</th>
                    <td>
                        <input type="number" id="general" name="general" value="<?php echo $general; ?>" min="1" step="any">
                    </td>
                </tr>
                <tr>
                    <td class=" padding-top25" colspan="2">
                        <a class="preview button btn-wid115" href="javascript:void(0);" id="clear">クリア</a>
                    </td>
                    <td class="padding-top25" colspan="2"><input type="submit" class="btn-wid115 button button-primary button-large" id="search" value="検索"></td>
                </tr>
                <tr>
                    <td class="padding-top25 text-align-center" colspan="4">
                        <input type="submit" name="sankasha" id="export1" class="button" value="参加者リストCSV">
                        <input type="submit" name="seikyuyo" id="export2" class="button margin-left-right10" value="請求用CSV">
                        <input type="submit" name="keiriyo" id="export3" class="button" value="経理用CSV">
                    </td>
                </tr>
            </table>
            <br class=" clear">
        </div>
        <div class="tablenav top">
            <?php $this->pagination($records, $page, 0, 'all'); ?>
            <br class="clear">
        </div>
        <table class="wp-list-table widefat fixed striped posts margin-top25">
            <thead>
                <tr>
                    <th class="" width="100px">セミナーコード</th>
                    <th class="" width="70px">受講票No</th>
                    <th class="" width="150px">申込日</th>
                    <th class="" width="100px">会社名</th>
                    <th class="" width="100px">参加者</th>
                    <th class="" width="100px">連絡担当者</th>
                    <th class="" width="100px">コース</th>
                    <th class="" width="120px">住所</th>
                    <th class="" width="80px">電話番号</th>
                    <th class="" width="80px">FAX</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $key => $val) : $cnt = $key + 1; ?>
                    <tr>
                        <td><?php echo $val->ppc_seminar_code; ?></td>
                        <td><?php echo $val->seminar_code; ?></td>
                        <!-- <td><?php // echo $val->term_name; ?></td> -->
                        <td><?php echo ppc_get_seminar_date($val->post_date); ?></td>
                        <td><?php echo $val->company_name; ?></td>
                        <td><?php echo $val->participant_sei . $val->participant_mei; ?></td>
                        <td><?php echo $val->applicant_sei . $val->applicant_mei; ?></td>
                        <td><?php echo $val->post_title; ?></td>
                        <td><?php echo $this->get_place($val->application_data); ?></td>
                        <td><?php echo $this->get_tel_fax_data($val->tel); ?></td>
                        <td><?php echo $this->get_tel_fax_data($val->fax); ?></td>
                    </tr>
                <?php endforeach; ?>
            <tbody>
        </table>
        <div class="tablenav bottom">
            <?php $this->pagination($records, $page, 0, 'all'); ?>
        </div>
    </form>
</div>