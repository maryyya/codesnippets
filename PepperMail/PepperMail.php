<?php

require_once 'PHPMailer/PHPMailerAutoload.php';
require_once 'Log.php';
require_once 'Config.php';
require_once 'Check.php';
/**
 * Mailクラス
 *
 * 使い方:
 * 1. smtp設定あり
 * sendMail($emailParams, $configParams);
 *
 * 2. smtp設定なし
 * sendMail($emailParams);
 *
 * --- メール設定 ---
 * $configParams = array(
 *   'host' => '',
 *   'username' => '',
 *   'password' => '',
 *   'port' => 587,
 *   'smtpoptions' => array() ---> 下のサンプル
 * );
 *
 * --- カスタム接続オプション ---
 * $smptoptions = array (
 *   'ssl' => array(
 *   'verify_peer'  => true,
 *   'verify_depth' => 3,
 *   'allow_self_signed' => true,
 *   'peer_name' => 'smtp.example.com',
 *   'cafile' => '/etc/ssl/ca_cert.pem',
 *  )
 * );
 *
 * --- メールの内容を設定 ---
 * $emailParams = array(
 *   'subject' => string - 空でもよい,
 *   'body' => string - 空でもよい,
 *   'from' => string - 空でもよい、　でもデフォルト値がある,
 *   'fromName' => string - 空でもよい、　でもデフォルト値がある,
 *   'returnPath' => string - 空でもよい,
 *   'to' => stringかarray - 任意,
 *   'cc' => stringかarray - 任意,
 *   'bcc' => stringかarray - 任意,
 * );
 */
class PepperMail
{
    /**
     * phpmailerのインスタンス
     *
     * @var string
     */
    private $mail;

    /**
     * SMTPのインスタンス
     *
     * @var string
     */
    private $smtp;

    /**
     * logのインスタンス
     *
     * @var string
     */
    private $log;

    /**
     * パラメータをチェックのインスタンス
     *
     * @var string
     */
    private $check;

    /**
     * このコンストラクタはインスタンスに使います。
     */
    public function __construct()
    {
        $this->mail  = new PHPMailer();
        $this->smtp  = new SMTP();
        $this->check = new Check();

        // ファイルパスを設定
        $this->log   = new Log(Config::fileinfo());
    }

    /**
     * メール送信
     *
     * @param  array   $emailParams     送信情報
     * @param  array   $configParams    メール設定
     * @return boolean $res             送信場合はtrue、そうでない場合はfalse
     */
    public function sendMail($emailParams, $configParams = array())
    {
        if (!empty(Config::TIMEZONE)) {
            date_default_timezone_set(Config::TIMEZONE); // phpmailerのタイムゾーンを設定
        }

        // 送信情報をチェック
        if (!$this->check->checkEmailParams($emailParams)) {
            $this->log->error('メールにエラーがあります。');
            return false;
        }

        // メール設定を取得
        $mailConfig = $this->getMailConfig($configParams);

        // メール設定をチェック
        if (!$this->check->checkConnection($mailConfig, $configParams)) {
            $this->log->error('メール設定パラメータにエラーがあります。');
            return false;
        }

        // メール設定をセット
        $this->setMailConn($mailConfig, $emailParams);

        // メール送る
        if (!$this->mail->send()) {
            $this->log->error('メールの送信にエラーがあります。');
            return false;
        }

        $this->log->info('メールが正常に送信されました。');
        return true;
    }

    /**
     * メールの設定を取得します。
     * この機能はメールの送信に使用する
     * サーバーを決定します。
     *
     * @param  array $params        ユーザーによって与えられました。
     * @return array $configParams  メール設定の最終パラメータ
     */
    public function getMailConfig($params)
    {
        $configParams = array(
            'host'        => !empty($params['host']) ? $params['host'] : Config::HOST,
            'port'        => !empty($params['port']) ? $params['port'] : Config::PORT,
            'username'    => !empty($params['username']) ? $params['username'] : Config::USERNAME,
            'password'    => !empty($params['password']) ? $params['password'] : Config::PASSWORD,
            'smtpsecure'  => !empty($params['smtpsecure']) ? $params['smtpsecure'] : Config::SMTPSECURE,
            'smtpoptions' => !empty($params['smtpoptions']) ? $params['smtpoptions'] : Config::SMTPOPTIONS,
            'SMTPAuth'    => !empty($params) && !empty($params['username']) && !empty($params['password']) ? true : Config::SMTPAUTH,
        );

        return $configParams;
    }

    /**
     * メール設定をセーと
     *
     * @param array $params       メール設定の最終パラメータ
     * @param array $emailParams  送信情報
     */
    private function setMailConn($params, $emailParams)
    {
        $this->mail->isSMTP();
        $this->mail->setLanguage(Config::LANGUAGE, 'PHPMailer/language/');
        $this->mail->CharSet  = Config::CHARSET;
        $this->mail->SMTPAuth = $params['SMTPAuth'];

        // SMTP認証がある場合に設定
        if (!empty($params['username']) && !empty($params['password'])) {
            $this->mail->Username = $params['username'];
            $this->mail->Password = $params['password'];
        }

        // Phpmailerのsmtpsecureをセーと
        if (!empty($params['smtpsecure'])) {
            $this->mail->SMTPSecure = $params['smtpsecure'];
        }

        // Phpmailerのsmtpoptionsをセーと
        if (!empty($params['smtpoptions'])) {
            $this->mail->SMTPOptions = $params['smtpoptions'];
        }

        $this->mail->Host = $params['host'];
        $this->mail->Port = $params['port'];

        if (empty($emailParams['fromName'])) {
            $emailParams['fromName'] = '';
        }

        // fromNameとfromをセーと
        $this->mail->setFrom($emailParams['from'], $emailParams['fromName']);

        // toをセーと
        $this->setRecipient($emailParams['to'], 'to');

        // ccをセーと
        $this->setRecipient($emailParams['cc'], 'cc');

        // bccをセーと
        $this->setRecipient($emailParams['bcc'], 'bcc');

        // returnPathをセーと
        if (isset($emailParams['returnPath']) && strlen($emailParams['returnPath']) !== 0) {
            $this->mail->AddReplyTo($emailParams['returnPath']);
        }

        // 主題と目的
        $this->mail->Subject = isset($emailParams['subject']) ? $emailParams['subject'] : '';
        $this->mail->Body    = isset($emailParams['body']) ? $emailParams['body'] : '';

        // メール本文と件名を空
        $this->mail->AllowEmpty = true;
    }

    /**
     * メールの受信者をセーと。
     * この機能はメールが文字列
     * か配列をチェックします。
     *
     * @param string | array $email メールの受信者
     * @param string         $type  メールの種類 (to, cc, bcc).
     */
    private function setRecipient($email, $type)
    {
        if (isset($email)) {
            if (is_array($email)) {
                foreach ($email as $key => $value) {
                    $this->addRecipient($value, $type);
                }
            } else if (is_string($email)) {
                    $this->addRecipient($email, $type);
            }
        }
    }

    /**
     * メールの受信者を追加
     * (to, cc, bcc)
     *
     * @param string $email メールの受信者
     * @param string $type  メールの種類 (to, cc, bcc).
     */
    private function addRecipient($email, $type)
    {
        switch ($type) {
            case 'to':
                $this->mail->addAddress($email);
                break;
            case 'cc':
                $this->mail->addCC($email);
                break;
            case 'bcc':
                $this->mail->addBCC($email);
                break;
            default:
                $this->mail->addAddress($email);
                break;
        }
    }
}
