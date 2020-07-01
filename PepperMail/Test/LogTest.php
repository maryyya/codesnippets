<?php

require 'Test.php';
/**
* Log Class Test
*/
class LogTest extends PHPUnit_Framework_TestCase
{
    /**
     * TestClassのインスタンス
     */
    public $test;

    /**
     * Logのインスタンス
     *
     * @var resource
     */
    public $log;

    /**
     * このコンストラクタはインスタンスに使います。
     */
    public function __construct()
    {
        $this->test = new Test();
        // ファイルパスを設定
        $this->log = new Log(Config::fileinfo());
    }

    /**
     * log機能をテストします。
     */
    public function testLog()
    {
         // エラーログ
        $params = array(
            'type' => 'ERROR',
            'lvl'  => Config::ERR,
            'msg'  => 'Sample Error'
        );

        $res = $this->test->invokeMethod($this->log, 'addLog', $params);

         // 警告ログ
        $params = array(
            'type' => 'WARNING',
            'lvl'  => Config::ERR,
            'msg'  => 'Sample Warning'
        );

        $res = $this->test->invokeMethod($this->log, 'addLog', $params);

        // 情報ログ
        $params = array(
            'type' => 'INFO',
            'lvl'  => Config::INFO,
            'msg'  => 'Sample INFO'
        );

        $res = $this->test->invokeMethod($this->log, 'addLog', $params);
    }
}
