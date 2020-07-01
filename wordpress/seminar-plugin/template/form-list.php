<?php

/**
 * Template for displaying
 * the list page for
 * each お申し込みデータ。
 */
?>
<div class="wrap sie-body">
    <h1 class="wp-heading-inline">お申し込みデータ</h1>
    <div class="card">
        <h2 class="title"><a href="<?php echo get_edit_post_link($seminar_id) ?>"><?php echo get_the_title($seminar_id); ?></a>　<?php echo $this->helper->status_html($seminar_id); ?></h2>
        <?php if (!empty($term[0])) : ?>
            <h3 class="u_h3style_semi" id=""><span><?php echo $term[0]->name; ?></span></h3>
        <?php endif; ?>
        <p><?php echo $sched; ?></p>
        <!-- <div class="ppc_form_kaijou"><?php // nl2br(strip_tags(get_field('kaijou', $seminar_id))); 
                                            ?></div> -->
    </div>
    <form method="post" action="">
        <input type="hidden" name="action" value="export">
        <input type="hidden" name="type" value="seminar_list">
        <input type="hidden" name="seminar_id" value="<?php echo $seminar_id; ?>">
        <p><input type="submit" id="search-submit" class="button" value="エクスポートデータ"></p>
    </form>
    <form method="get" action="">
        <div class="tablenav top">
            <h2 class="screen-reader-text">投稿リストナビゲーション</h2>
            <?php $this->pagination($records, $page, $seminar_id); ?>
            <br class="clear">
        </div>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column"></td>
                    <th scope="col" class="manage-column column-form_data ppc_form_company_name">貴社名</th>
                    <th scope="col" class="manage-column column-form_data ppc_form_applicant">お申込み手続者</th>
                    <th scope="col" class="manage-column column-form_status">参加者総数</th>
                    <th scope="col" class="manage-column column-form_status">請求金額</th>
                    <th scope="col" class="manage-column column-form_status ppc_form_type">種別</th>
                    <th scope="col" class="manage-column column-form_status ppc_form_post_date">登録日</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $val) : ?>
                    <tr class="iedit author-self type-seminar status-publish hentry seminar_cat-fresh">
                        <td class="title column-title has-row-actions column-primary page-title" data-colname="タイトル"></td>
                        <td class="form_data column-form_data">
                            <a href="<?php echo get_admin_url(); ?>admin.php?page=ppcformdetaildata&application_id=<?php echo $val->ID; ?>"><?php echo $val->company_name; ?></a>
                        </td>
                        <td><?php echo $val->title . '　' . $val->sei . $val->mei; ?></td>
                        <td><?php echo $val->participants; ?></td>
                        <td><?php echo '￥' . number_format($val->amount); ?></td>
                        <td><?php echo ppc_get_seminar_type($val->type); ?>
                        </td>
                        <td class="date column-date" data-colname="日付"><?php echo date('Y年m月d日 H時i分', strtotime($val->post_date)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="tablenav bottom">
            <?php $this->pagination($records, $page, $seminar_id); ?>
        </div>
        <input type="hidden" name="page" value="ppcformdata">
        <input type="hidden" name="seminar_id" value="<?php echo $seminar_id; ?>">
    </form>
</div>