<?php

/**
 * Include config
 */
require_once('config.php');

/**
 * ダウンロードCSVクラス
 */
class DownloadCsvFile
{
    /**
     * Download file execution
     */
    public function download() {
        $path = '';

        // downloading log file
        if ( !empty( $_POST['log_file'] ) ) {
            $filepath = $_POST['log_file'];
            $filename = basename( $_POST['log_file'] );
            $path = $filepath;
        } elseif ( !empty( $_POST['custom-sched-file'] ) ) {
            // for creating the csv file for exporting the カスタム営業時間
            $filepath = 'tmp/'.$_POST['custom-sched-file'];
            $filename = 'shop-'.SHOP_SITE_TYPE.'-sched-data-'.date( 'YmdHis' ).'.csv';
            $path = SHOP_PLUGIN_PATH_MAIN.'/'.$filepath;
        } else {
            $filepath = SHOP_CSV_FILEPATH;
            $filename = 'shop-'.SHOP_SITE_TYPE.'-data-'.date( 'YmdHis' ).'.csv';
            $path = SHOP_PLUGIN_PATH_MAIN.'/'.$filepath;
        }

        if ( !file_exists( $path ) ) {
            exit;
        }

        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="'.$filename.'"' );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Content-Length: '.filesize( $filepath ) );
        ob_clean();
        flush();
        readfile( $filepath );
        unlink( $path );
    }
}

$exec = new DownloadCsvFile();
$exec->download();
