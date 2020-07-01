<?php

/**
 * Template for displaying
 * the detail page for
 * each お申し込みデータ。
 */
?>
<div class="wrap sie-body">
    <h1 class="wp-heading-inline">お申し込み詳細</h1>
    <?php if (!empty($res[0])) :
        $seminar_id    = $res[0]['seminar_id'];
        $seminar_start = get_field('seminar_start', $seminar_id);
        $seminar_end   = get_field('seminar_end', $seminar_id);
        $jukouryou     = get_field('jukouryou', $seminar_id);
        $member_fee    = get_field('member_fee', $seminar_id);
        $general_fee   = get_field('general_fee', $seminar_id);
        $sched = ppc_get_seminar_date($seminar_start, $seminar_end);
        $term = get_the_terms($seminar_id, 'seminar_cat');

    ?>
        <p><a href="<?php echo get_admin_url(); ?>admin.php?page=ppcformdata&seminar_id=<?php echo $seminar_id; ?>&paged=">一覧へ戻る</a></p>
        <div class="card">
            <h2 class="title"><a href="<?php echo get_edit_post_link($seminar_id) ?>"><?php echo get_the_title($seminar_id); ?></a>　<?php echo $this->helper->status_html($seminar_id); ?></h2>
            <?php if (!empty($term[0])) : ?>
                <h3 class="u_h3style_semi" id=""><span><?php echo $term[0]->name; ?></span></h3>
            <?php endif; ?>
            <p><?php echo $sched; ?></p>
            <!-- <div class="ppc_form_kaijou"><?php //echo get_field('kaijou', $seminar_id); 
                                                ?></div> -->
            <br>
            <p><strong>受講料</strong>：<?php echo $jukouryou['label']; ?></p>
            <p><strong>会員</strong>　：<?php echo number_format($member_fee); ?></p>
            <p><strong>一般</strong>　：<?php echo number_format($general_fee); ?></p>
        </div>
        <div class="card">
            <h2 class="title ppc_form_amount">請求金額 ￥ <?php echo number_format($res[0]['amount']); ?> ― （消費税込）</h2>
        </div>
        <table class="form-table-detail wp-list-table" role="presentation">
            <tbody>
                <tr class="main-th">
                    <th>ラベル</th>
                    <th>値</th>
                </tr>
                <?php foreach ($res as $data):
                    foreach ($data as $key => $val):
                        if ($key === 'seminar_id' || $key === 'amount' || empty($val)) {
                            continue;
                        }
                    ?>
                    <tr>
                        <th><label><?php echo $key;?></label></th>
                        <td><?php echo $val; ?></td>
                    </tr>
                <?php endforeach;
                endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>データがないです。</p>
    <?php endif; ?>
</div>