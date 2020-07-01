<?php

namespace App\Modules;

/**
 * Log Class
 *
 * エラー、警告、および情報ログの場合。
 */
class Log
{
    /**
     * ファイルパスを持つ
     *
     * @var array
     */
    private $fileinfo;

    /**
     * ファイルポインタを持つ
     *
     * @var boolean or ファイルポインタ
     */
    private $fp;

    /**
     * このコンストラクタはファイルパス
     * を取得するためであります。
     *
     * LOG_FILE_PATHは../Config.phpにあります
     */
    public function __construct()
    {
        $this->fileinfo = LOG_FILE_PATH;
    }

    /**
     * エラーログ
     *
     * @param  string $msg  ログのメッセージ
     */
    public function error($msg)
    {
        if (!Config::ENABLE_LOG) {
            return;
        }

        return $this->addLog('ERROR', Config::ERR, $msg);
    }

    /**
     * 情報ログ
     *
     * @param  string $msg  ログのメッセージ
     */
    public function info($msg)
    {
        if (!Config::ENABLE_LOG) {
            return;
        }

        return $this->addLog('INFO', Config::INFO, $msg);
    }

    /**
     * 警告ログ
     *
     * @param  string $msg  ログのメッセージ
     */
    public function warning($msg)
    {
        if (!Config::ENABLE_LOG) {
            return;
        }

        return $this->addLog('WARNING', Config::WAR, $msg);
    }

    /**
     * ログを書きます。
     *
     * @param  string $type エラーの種類(INFO, WARNING, ERROR)
     * @param  string $lvl  ログレベル
     */
    private function addLog($type, $lvl, $msg)
    {
        if (!empty(Config::TIMEZONE)) {
            date_default_timezone_set(Config::TIMEZONE);
        }

        $time = date('[Y/m/d H:i:s]');
        $dirPath = $this->fileinfo[0];
        $filename = $this->fileinfo[1];
        $fullpath = $dirPath.'/'.$filename;

        try {
            // ディレクトリが存在を確認します。
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0777, true);
            }

            // ファイルが存在を確認します。
            $mode = !file_exists($fullpath)?'w':'a';

            $this->fp = fopen($fullpath, $mode);

            // 何を出力するかを確認
            if (($lvl & Config::LOG) > 0) {
                fwrite($this->fp, $time . ' [' . $type . '] ' . $msg . PHP_EOL);
            }

            fclose($this->fp);
        } catch (Exception $e) {
            return;
        }
    }
}
