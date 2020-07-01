<?php

namespace App\Modules;

/**
 * Class Log
 * エラー、警告、および情報ログの場合。
 *
 * @package App\Modules
 */
class Log
{
    /**
     * エラーログ
     *
     * @param string $msg ログのメッセージ
     */
    public function error($msg)
    {
        return $this->addLog('ERROR', json_encode($msg, JSON_UNESCAPED_UNICODE));
    }

    /**
     * お知らせログ
     *
     * @param string $msg ログのメッセージ
     */
    public function info($msg)
    {
        return $this->addLog('INFO', json_encode($msg, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 検索ログ
     * 主にFormListで使用されます
     *
     * @param string $msg ログのメッセージ
     */
    public function search($msg)
    {
        return $this->addLog('SEARCH', json_encode($msg, JSON_UNESCAPED_UNICODE));
    }

    /**
     * ログを書きます。
     *
     * @param string $type     エラーの種類(INFO, WARNING, ERROR)
     * @param string $msg      メッセージ
     */
    private function addLog($type, $msg)
    {
        $time     = date('[Y/m/d H:i:s]');
        $fileType = strtolower($type);
        $filepath = LOG_FILE_DIR . date('Ymd') . '-' . $fileType . '.log';

        try {
            // ファイルが存在を確認します。
            $mode = !file_exists($filepath) ? 'w' : 'a';

            $fp = fopen($filepath, $mode);

            // 何を出力するかを確認
            fwrite($fp, $time . ' [' . $type . '] ' . $msg . PHP_EOL);

            fclose($fp);
        } catch (Exception $e) {
            return;
        }
    }
}
