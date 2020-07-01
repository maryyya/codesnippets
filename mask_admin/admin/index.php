<?php
/**
 * 注文予約一覧
 */

require_once '../originalform_app/app/Base.php';
$param = $_POST;
if(isset($param['btn_type']) && $param['btn_type'] == 'search') {
    $param['page_hidden'] = 1;
}
$res = (new Base)->getData($param);

// PAGINATIONのため
$pageHidden = (isset($param['btn_type']) && $param['btn_type'] === 'search')?1:(!empty($param['page_hidden'])?$param['page_hidden']:1);
$previousPageDisabled = $pageHidden == 1 || empty($param['page_hidden'])?'disabled':'';
$nextPageDisabled = $pageHidden == (int)$res['pages']?'disabled':'';
$previousPage = $pageHidden - 1;
$nextPage = $pageHidden + 1;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>注文予約一覧</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bulma.min.css">
    <link rel="stylesheet" href="assets/css/bulma-calendar.min.css">
    <script defer src="assets/js/fontawesome.js"></script>
    <script src="assets/js/bulma-calendar.min.js"></script>
    <script src="assets/js/jquery-3.5.0.min.js"></script>
    <script src="assets/js/custom.js"></script>
</head>
<body>
<section class="hero is-primary is-bold">
    <div class="hero-body">
        <div class="container">
            <h1 class="title">注文予約一覧</h1>
        </div>
    </div>
</section>
<section class="search">
    <form method="post">
        <div class="is-ancestor">
            <div class="box">
                <div class="columns search-block">
                    <div class="column is-2"><p class="search-label">受付番号</p></div>
                    <div class="column">
                        <div class="control">
                            <input id="fromReceiptNo" name="fromReceiptNo" class="input" type="number" placeholder="FROM" min="0" max="9999999" value="<?php echo isset($param['fromReceiptNo'])?$param['fromReceiptNo']:'';?>">
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="control">
                            <input id="toReceiptNo" name="toReceiptNo" class="input" type="number" placeholder="TO" min="0" max="9999999" value="<?php echo isset($param['toReceiptNo'])?$param['toReceiptNo']:'';?>">
                        </div>
                    </div>
                    <div class="column"><p class="search-label">受付日時</p></div>
                    <div class="column is-2">
                        <div class="control">
                            <input id="fromDate" name="fromDate" class="input" type="date" value="<?php echo isset($param['fromDate'])?$param['fromDate']:'';?>">
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="control">
                            <input id="toDate" name="toDate" class="input" type="date" value="<?php echo isset($param['toDate'])?$param['toDate']:'';?>">
                        </div>
                    </div>
                </div>
                <div class="columns search-block">
                    <div class="column is-2"><p class="search-label">フォームタイプ</p></div>
                    <div class="column is-4">
                        <div class="select">
                            <select name="type">
                                <?php
                                foreach ($res['formTypeList'] as $key => $form) {
                                    $selected = isset($param['type']) && $key === (int)$param['type']?'selected':'';
                                    echo '<option value="'.$key.'" '.$selected.'>'.$form['label'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column"></div>
                    <div class="column">
                        <button type="button" class="button is-fullwidth clear-btn">クリア</button>
                    </div>
                    <div class="column">
                        <button type="submit" name="btn_type" value="search" class="button is-primary is-outlined is-fullwidth">検索</button>
                    </div>
                    <div class="column">
                        <button type="submit" name="btn_type" value="csv" class="button is-danger is-outlined is-fullwidth">CSVダウンロード</button>
                    </div>
                    <div class="column"></div>
                </div>
            </div>
            <div class="box">
                <nav class="pagination margin-bottom-06 margin-left-01 is-right" role="navigation" aria-label="pagination">
                    <p class="form_type_title"><?php echo $res['formType'];?></p>
                    <ul class="pagination-list">
                        <li class="count"><p><?php echo $res['count'];?>個の項目</p></li>
                        <li><button type="submit" name="page" value="1" class="pagination-btn pagination-previous" <?php echo $previousPageDisabled;?>>«</button></li>
                        <li><button type="submit" name="page" value="<?php echo $previousPage; ?>" class="pagination-btn pagination-previous" <?php echo $previousPageDisabled;?>>‹</button></li>
                        <li><input type="text" name="page_text_top" value="<?php echo $pageHidden;?>" class="input page-input" value="<?php echo $pageHidden;?>"> <span class="page-count">/ <?php echo $res['pages'];?></span></li>
                        <li><button type="submit" name="page" value="<?php echo $nextPage;?>" class="pagination-btn pagination-next" <?php echo $nextPageDisabled;?>>›</button></li>
                        <li><button type="submit" name="page" value="<?php echo $res['pages'];?>" class="pagination-btn pagination-next" <?php echo $nextPageDisabled;?>>»</button></li>
                    </ul>
                </nav>
                <div class="table-container">
                    <table id="result" class="table is-bordered is-striped is-hoverable">
                        <thead>
                        <tr>
                            <?php
                                foreach ($res['names'] as $name) {
                                    echo '<th class="search-table-th">'.$name.'</th>';
                                }
                            ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($res['data'] as $items) {
                            echo '<tr>';
                            foreach ($items as $val) {
                                echo '<td class="search-table-td">'.$val.'</td>';
                            }
                            echo '</tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <nav class="pagination is-right" role="navigation" aria-label="pagination">
                    <ul class="pagination-list">
                        <li class="count"><p><?php echo $res['count'];?>個の項目</p></li>
                        <li><button type="submit" name="page" value="1" class="pagination-btn pagination-previous" <?php echo $previousPageDisabled;?>>«</button></li>
                        <li><button type="submit" name="page" value="<?php echo $previousPage; ?>" class="pagination-btn pagination-previous" <?php echo $previousPageDisabled;?>>‹</button></li>
                        <li><input type="text" name="page_text_bottom" value="<?php echo $pageHidden;?>" class="input page-input" value="<?php echo $page;?>"> <span class="page-count">/ <?php echo $res['pages'];?></span></li>
                        <li><button type="submit" name="page" value="<?php echo $nextPage;?>" class="pagination-btn pagination-next" <?php echo $nextPageDisabled;?>>›</button></li>
                        <li><button type="submit" name="page" value="<?php echo $res['pages'];?>" class="pagination-btn pagination-next" <?php echo $nextPageDisabled;?>>»</button></li>
                    </ul>
                </nav>
                <input type="hidden" name="page_hidden" value="<?php echo $pageHidden;?>">
            </div>
        </div>
    </form>
</section>
<footer>
    <script type="text/javascript">
        // default options
        var options = {
            lang: 'ja',
            displayMode: 'default',
            dateFormat: 'YYYY-MM-DD',
            showHeader: false,
            cancelLabel: 'キャンセル',
            cancelLabel: 'キャンセル',
            clearLabel: 'クレア',
            clearLabel: 'クリア',
            todayLabel: '今日',
        };

        // 受付日時FROM
        var fromDate = $('#fromDate').val();
        options.startDate = fromDate;
        var fromCalendar = bulmaCalendar.attach('[name="fromDate"]', options);

        // 受付日時TO
        var toDate = $('#toDate').val();
        options.startDate = toDate;
        var toCalendar = bulmaCalendar.attach('[name="toDate"]', options);
    </script>
</footer>
</body>
</html>
