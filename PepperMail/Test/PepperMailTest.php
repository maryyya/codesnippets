<?php

require 'Test.php';
/**
* PepperMailTest Class
*/
class PepperMailTest extends PHPUnit_Framework_TestCase
{
    /**
     * TestClassのインスタンス
     */
    public $test;

    /**
     * PepperMailのインスタンス
     *
     * @var resource
     */
    public $peppermail;

    /**
     * このコンストラクタはインスタンスに使います。
     */
    public function __construct()
    {
        $this->test = new Test();
        $this->peppermail = new PepperMail();
    }

    /**
     * sendMail機能をテストします。
     */
    public function testSendMail()
    {
        // 送信情報は空です
        $params = array();
        $res = $this->peppermail->sendMail($params);
        $this->assertFalse($res);

        // ポートが間違っています
        $emailParams = array(
            'subject'    => 'sample subject',
            'body'       => 'sample body',
            'from'       => 'padon.mary@gmail.com',
            'fromName'   => '一般財団法人 ピンネ農業公社',
            'returnPath' => 'padon.mary@gmail.com',
            'to'         => array(
                'mary.angeleque.padon@pripress.co.jp',
            ),
        );

        $configParams = array(
            'SMTPAuth' => false,
            'host' => '',
            'port' => 5876
        );
        $res = $this->peppermail->sendMail($emailParams, $configParams);
        $this->assertFalse($res);

        // ホストが間違っています
        $emailParams = array(
            'subject'    => 'sample subject',
            'body'       => 'sample body',
            'from'       => 'padon.mary@gmail.com',
            'fromName'   => '一般財団法人 ピンネ農業公社',
            'returnPath' => 'padon.mary@gmail.com',
            'to'         => array(
                'mary.angeleque.padon@pripress.co.jp',
            ),
        );

        $configParams = array(
            'SMTPAuth' => true,
            'host' => 's',
            'username' => 'padon.mary@gmail.com',
            'password' => 'pr2726670',
            'port' => 587
        );
        $res = $this->peppermail->sendMail($emailParams, $configParams);
        $this->assertFalse($res);

        // すべてのパラメータは大丈夫です。
        $configParams = array(
            'host' => '',
            'username' => 'padon.mary@gmail.com',
            'password' => 'pr2726670',
            'port' => 587
        );
        $emailParams = array(
            'subject'    => 'sample subject',
            'body'       => 'sample body',
            'from'       => 'padon.mary@gmail.com',
            'fromName'   => '一般財団法人 ピンネ農業公社',
            'returnPath' => 'padon.mary@gmail.com',
            'to'         => array(
                'mary.angeleque.padon@pripress.co.jp',
            ),
        );

        $res = $this->peppermail->sendMail($emailParams, $configParams);
        $this->assertTrue($res);
    }

    /**
     * getMailConfig機能をテストします。
     */
    public function testMailConfig()
    {
        // ホストはありません。
        $params = array(
            'username' => 'padon.mary@gmail.com',
            'password' => '',
            'port'     => 25,
        );
        $res = $this->test->invokeMethod($this->peppermail, 'getMailConfig', $params);
        $expected = array(
            'host'        => '',
            'port'        => 25,
            'username'    => '',
            'password'    => '',
            'smtpsecure'  => '',
            'smtpoptions' => array(),
            'SMTPAuth'    => false
        );
        $this->assertEquals($expected, $res);

        // ポートはありません。
        $params = array(
            'host'     => '',
            'username' => 'padon.mary@gmail.com',
            'password' => '',
        );
        $res = $this->test->invokeMethod($this->peppermail, 'getMailConfig', $params);
        $expected = array(
            'host'        => '',
            'port'        => 25,
            'username'    => '',
            'password'    => '',
            'smtpsecure'  => '',
            'smtpoptions' => array(),
            'SMTPAuth'    => false
        );
        $this->assertEquals($expected, $res);

        // ユーザー名かパスワードはありません。
        $params = array(
            'host'     => '',
            'port'     => 25,
        );
        $res = $this->test->invokeMethod($this->peppermail, 'getMailConfig', $params);
        $expected = array(
            'host'        => '',
            'port'        => 25,
            'username'    => '',
            'password'    => '',
            'smtpsecure'  => '',
            'smtpoptions' => array(),
            'SMTPAuth'    => false
        );
        $this->assertEquals($expected, $res);

        // ユーザー名かパスワードはあります。
        $params = array(
            'host'     => '',
            'username' => '',
            'password' => '',
            'port'     => 25,
        );
        $res = $this->test->invokeMethod($this->peppermail, 'getMailConfig', $params);
        $expected = array(
            'host'        => '',
            'port'        => 25,
            'username'    => '',
            'password'    => '',
            'smtpsecure'  => '',
            'smtpoptions' => array(),
            'SMTPAuth'    => false
        );
        $this->assertEquals($expected, $res);
    }
}
