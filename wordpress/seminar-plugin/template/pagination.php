<?php

/**
 * form-list.phpのページネーション
 */
?>
<div class="tablenav-pages"><span class="displaying-num"><?php echo $records; ?>個の項目</span>
    <span class="pagination-links">
        <?php if (1 === (int) $page) : ?>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
        <?php else : ?>
            <a class="next-page button" href="<?php echo site_url('/'); ?>wp-admin/admin.php?page=ppcformdata&seminar_id=<?php echo $seminar_id; ?>&paged=1">
                <span class="screen-reader-text">次ページへ</span>
                <span aria-hidden="true">«</span>
            </a>
            <a class="next-page button" href="<?php echo site_url('/'); ?>wp-admin/admin.php?page=ppcformdata&seminar_id=<?php echo $seminar_id; ?>&paged=<?php echo $prvpg; ?>">
                <span class="screen-reader-text">次ページへ</span>
                <span aria-hidden="true">‹</span>
            </a>
        <?php endif; ?>

        <span class="paging-input">
            <label for="current-page-selector" class="screen-reader-text">現在のページ</label>
            <input class="current-page" id="current-page-selector" type="text" name="paged" value="<?php echo $page; ?>" size="1" aria-describedby="table-paging">
            <span class="tablenav-paging-text"> / <span class="total-pages"><?php echo $pages; ?></span></span>
        </span>

        <?php if ((int) $pages === (int) $page) : ?>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
        <?php else : ?>
            <a class="next-page button" href="<?php echo site_url('/'); ?>wp-admin/admin.php?page=ppcformdata&seminar_id=<?php echo $seminar_id; ?>&paged=<?php echo $nxtpg; ?>">
                <span class="screen-reader-text">次ページへ</span>
                <span aria-hidden="true">›</span>
            </a>
            <a class="last-page button" href="<?php echo site_url('/'); ?>wp-admin/admin.php?page=ppcformdata&seminar_id=<?php echo $seminar_id; ?>&paged=<?php echo $pages; ?>">
                <span class="screen-reader-text">最後のページ</span>
                <span aria-hidden="true">»</span>
            </a>
        <?php endif; ?>
    </span>
</div>