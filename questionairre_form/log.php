<?php

/**
 * To add log
 */
class Log
{
    /**
     * Log directory
     */
    const LOG_DIR = '/www/log';

    /**
     * Access and error log
     * 使い方: $this->log('message', __LINE__);
     *
     * @param  string $msg  Error or access log
     * @param  string $line Line number
     * @param  string $file Which file is it from
     */
    public function write($msg, $line, $file) {
        $date = date('Y-m-d');
        $fullDate =date('Y-m-d H:i:s');
        $fullpath = self::LOG_DIR.'/'.$date.'.log';
        try {
            if (!file_exists(self::LOG_DIR)) {
                mkdir(self::LOG_DIR, 0777, true);
            }

             // ファイルが存在を確認します。
            $mode = !file_exists($fullpath)?'w':'a';

            $fp = fopen($fullpath, $mode);

            fwrite($fp, $fullDate . ' ' . str_pad('LINE#' . $line, 13) . str_pad('FILE: ' . $file, 20) . ' ' . $msg . PHP_EOL);

            fclose($fp);
        } catch (\Exception $e) {

        }
    }
}
