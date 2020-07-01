<?php

/**
 * Check Mail Class
 *
 * 送信情報とメール設定をチェック
 */
class Check
{
    /**
     *　SMTPのインスタンス
     *
     * @var resource
     */
    private $smtp;

    /**
     * logのインスタンス
     *
     * @var resource
     */
    private $log;

    /**
     * このコンストラクタはインスタンスに使います。
     */
    public function __construct()
    {
        $this->smtp = new SMTP();

        // Config::fileinfo()はファイルパス。
        $this->log  = new Log(Config::fileinfo());
    }

    /**
     * 送信情報をチェック
     *
     * @return boolean $res
     */
    public function checkEmailParams($params)
    {
        // 送信情報は空です
        if (empty($params)) {
            $this->log->error('送信情報は空です。');
            return false;
        }

        // toとccが空であることをチェック
        if (empty($params['to']) && empty($params['cc'])) {
            $this->log->error('toとccは空です。');
            return false;
        }

        // fromが空であることをチェック
        if (empty($params['from'])) {
            $this->log->error('fromは空です。');
            return false;
        }

        // 受信(to)をチェック
        if (!empty($params['to']) && !$this->checkRecipient($params['to'], 'To')) {
            return false;
        }

        // 受信(cc)をチェック
        if (!empty($params['cc']) && !$this->checkRecipient($params['cc'], 'Cc')) {
            return false;
        }

        // 受信(bcc)をチェック
        if (!empty($params['bcc']) && !$this->checkRecipient($params['bcc'], 'Bcc')) {
            return false;
        }

        // 件名が空の場合は警告を出してください。
        if (empty($params['subject'])) {
            $this->log->warning('件名は空です。');
        }

        // 本文が空の場合は警告を出してください。
        if (empty($params['body'])) {
            $this->log->warning('本文は空です。');
        }

        return true;
    }

    /**
     * この機能は受信者をチェックします。
     *
     * @param  string or array  $recipient 受信者
     * @param  string           $type      メールの種類 (to, cc, bcc).
     * @return boolean
     */
    private function checkRecipient($recipient, $type)
    {
        if (!empty($recipient)) {
            if (!is_array($recipient) && !is_string($recipient)) {
                $this->log->error($type . 'は文字列か配列ではありません。');
                return false;
            } elseif (is_array($recipient)) {
                foreach ($recipient as $key => $value) {
                    if (!empty($value)) {
                        if (!$this->checkEmailFormat($value)) {
                            $this->log->warning($type.': '.$value. 'は形式ではありません。');
                        }
                    }
                }
            } elseif (is_string($recipient)) {
                if (!$this->checkEmailFormat($recipient)) {
                    $this->log->warning($type.': '.$recipient. 'は形式ではありません。');
                }
            }
        }

        return true;
    }

    /**
     * メール形式をチェック
     *
     * @param  string  $email
     * @return boolean
     */
    private function checkEmailFormat($email)
    {
        if (!preg_match('/^[^@]+@([^@^\.]+\.)+[^@^\.]+$/', $email)) {
            return false;
        }

        return true;
    }

    /**
     * 接続をチェック
     *
     * @param  array   $configParams      メール設定
     * @return boolean $res
     */
    public function checkConnection($configParams)
    {
        if (empty($configParams['host']) || empty($configParams['port'])) {
            $this->log->error('ホストかポートは空です。');
            return false;
        }

        if (!is_string($configParams['host']) || !is_int($configParams['port'])) {
            $this->log->error('ホストは文字列ではありません。ポートはintではありません。');
            return false;
        }

        if (!in_array($configParams['port'], Config::PORTS)) {
            $this->log->error('ポートが共通ポートリストに存在しません。');
            return false;
        }

        if (!$this->smtp->connect($configParams['host'], $configParams['port'])) {
            $this->log->error('ホストとポートの接続を確認してください。');
            return false;
        }

        // smtp認証チェックに必要
        if (!$this->smtp->hello($configParams['host'])) {
            $this->log->error('PhpmailerのHello機能にエラーがあります。');
            return false;
        }

        if ($configParams['SMTPAuth']) {
            if (empty($configParams['username']) || empty($configParams['password'])) {
                $this->log->error('ユーザー名かパスワードは空です。');
                return false;
            } elseif ($this->smtp->authenticate($configParams['username'], $configParams['password']) !== true) {
                $this->log->error('SMTPサーバーの認証が一致しません。');
                return false;
            }
        }

        if (!empty($configParams['smtpsecure']) && !is_string($configParams['smtpsecure'])) {
            $this->log->error('SMTPSecureは文字列でなければなりません。');
            return false;
        }

        if (!empty($configParams['smtpoptions']) && !is_array($configParams['smtpoptions'])) {
            $this->log->error('SMTPSecureは配列でなければなりません。');
            return false;
        }

        return $configParams;
    }
}
