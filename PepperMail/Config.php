<?php

/**
 * メール設定 Class
 */
class Config
{
    /**************************************************/
    /****************** 変更可能 **********************/
    /***** この下にPepperMail.phpのためであります。****/
    /**************************************************/

    /**
     * DEFAULT DEBUG FOR PHPMAILER
     * means no view
     */
    const DEBUG = 0;

    /**
     * 0: no display
     * 1: show messages sent by client
     * 2: client and server; will add server messages, it’s the recommended setting.
     * 3: client, server, and connection; will add information about the initial information, might be useful for discovering STARTTLS failures
     * 4: low-level information.
     */
    const DEBUGLIST = array(0, 1, 2, 3, 4);

    /**
     * ホストセット
     *
     * @var string
     */
    const HOST = '';

    /**
     * ポートセット
     *
     * @var int
     */
    const PORT = 25;

    /**
     * 認証がある場合はtrue
     * それ以外の場合はfalse
     *
     * @var boolean
     */
    const SMTPAUTH = false;

    /**
     * ユーザー名セット
     *
     * @var string
     */
    const USERNAME = '';

    /**
     * パスワードセット
     *
     * @var string
     */
    const PASSWORD = '';

    /**
     * Smtpsecureセット
     * TLS暗号化を有効にする, 'ssl'も受け入れられました
     *
     * @var string
     */
    const SMTPSECURE = '';

    /**
     * smtpoptionsセット
     * Ex. array (
     *       'ssl' => array(
     *          'verify_peer'  => true,
     *          'verify_depth' => 3,
     *          'allow_self_signed' => true,
     *          'peer_name' => 'smtp.example.com',
     *          'cafile' => '/etc/ssl/ca_cert.pem',
     *       )
     *     )
     *
     * @var array
     */
    const SMTPOPTIONS = array();

    /*******************************************/
    /***** この下にLog.phpのためであります。****/
    /*******************************************/

    /**
     * ディレクトリ名セット
     *
     * @var string
     */
    const LOGDIR = '';

    /**
     * ロギングが有効な場合
     *
     * @var boolean
     */
    const ENABLE_LOG = true;

    /**
     * 出力するログの種類
     *
     * self::INFO      - 情報ログ
     * self::WAR       - 警告ログ
     * self::ERR       - エラーログ
     * self::INFO_ERR  - 情報とエラーログ
     * self::WAR_ERR   - 警告とエラーログ
     * self::ALL       - すべてのログ
     *
     * @var int
     */
    const LOG = self::ALL;

    /********************************************/
    /*************** 変更不可能な ***************/
    /********************************************/

    /**
     * 言語セット(Phpmailer)
     *
     * @var string
     */
    const LANGUAGE = 'ja';

    /**
     * タイムゾーンセット(Phpmailer)
     *
     * @var string
     */
    const TIMEZONE = 'Asia/Tokyo';

    /**
     * 文字セット(Phpmailer)
     *
     * @var string
     */
    const CHARSET = 'UTF-8';

    /**
     * 共通SMTPポートリスト
     *
     * @var array
     */
    const PORTS = array(25, 587, 465, 2526, 2525);

    /*******************************************/
    /***** この下にLog.phpのためであります。****/
    /*******************************************/

    /**
     * LOG INFO
     *
     * @var int
     */
    const INFO = 1;

    /**
     * LOG WARNING
     *
     * @var int
     */
    const WAR = 2;

    /**
     * LOG ERROR
     *
     * @var int
     */
    const ERR = 4;

    /**
     * LOG INFO AND ERROR
     *
     * @var int
     */
    const INFO_ERR = 5;

    /**
     * LOG WARNING ERROR
     *
     * @var int
     */
    const WAR_ERR = 6;

    /**
     * INFO, WARNING, AND ERROR
     */
    const ALL = 7;

    /**
     * この機能はディレクトリと
     * ファイル名をreturnします。
     *
     * @return array
     */
    public static function fileinfo()
    {
        $dir      = (!empty(self::LOGDIR) && file_exists(self::LOGDIR)) ? self::LOGDIR : dirname(__FILE__);
        $filepath = $dir . '/Logs/' . date('Y') . '/' . date('m');
        $filename = date('Ymd') . '.log';

        return array($filepath, $filename);
    }
}
