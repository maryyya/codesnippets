<?php

/**
 * This is to save the data
 */
require_once 'save-data.php';

/**
 * Mail Setting
 */
class Mail extends Save_data
{
    /**
     * セミナー定員の80％を超えたものについては運営者にメール等でその状況を伝達する。
     */
    const CAPACITY_PERCENTAGE = 80;
    /**
     * Taxonomy for seminar
     */
    const TAXONOMY = 'seminar_cat';

    /**
     * If there's error in お申し込みフォーム
     *
     * @var boolean
     */
    public $form_error = true;

    /**
     * Set the sending of email
     * ppc_error_log is in
     * themes/functions.php
     *
     * @param object  $Mail Default mail setting
     * @param array   $data Input or post data
     * @return object $Mail New mail setting
     */
    public function send_mail_main($Mail, $data)
    {
        $seminar_id = $data['seminar_id'];
        $form_id    = $data['mw-wp-form-form-id'];
        $term       = get_the_terms($seminar_id, self::TAXONOMY);

        $Mail->attachments = [];

        if (empty($term[0])) {
            $msg = 'セミナーはデータがありません。';
            ppc_error_log([
                'msg' => 'Error sending mail in お申し込み. It occured during the $Mail->send() function. The seminar has empty data.',
                'file' => __FILE__,
                'line' => __LINE__,
            ]);

            $this->send_mail_error($Mail, $seminar_id, $form_id, $msg, $data['applicant_mail']);
            return $Mail;
        }

        // for the calculating of invoice
        if (!isset($data['type'])) {
            $data['type'] = 'general';
        }

        // 参加者
        $participants = $this->get_participants($data);
        if (!$participants) {
            $msg = '参加者がありません。';
            ppc_error_log([
                'msg' => $msg,
                'file' => __FILE__,
                'line' => __LINE__,
            ]);

            $this->send_mail_error($Mail, $seminar_id, $form_id, $msg, $data['applicant_mail']);
            return $Mail;
        }

        // this function is in seminar.php
        $participant_total = ppc_get_participant_total($seminar_id);

        // セミナー定員の80％を超えたものについては運営者にメール等でその状況を伝達する。
        if (ppc_get_capacity_status($seminar_id, self::CAPACITY_PERCENTAGE)) {
            $this->send_mail_notice($Mail, $seminar_id, $form_id, $participant_total);
        }

        // 定員の90％あるいは80％を超えたものについてはHPでの受付を停止する。
        if (ppc_get_capacity_status($seminar_id)) {
            $this->send_mail_info($Mail, $seminar_id, $form_id, $participant_total);
        }

        $last_application_id = $this->get_last_application_id();
        $last_participant_id = $this->get_last_participant_id($seminar_id, $form_id);
        $invoice_key         = $this->create_invoice_key($seminar_id, $term[0]->term_id, $form_id, $last_application_id);

        // メール内容
        $mail_content = get_field('ppc_category_mail_content', self::TAXONOMY . '_' . $term[0]->term_id);
        $Mail->body   = $this->bind_mail($mail_content, $data, $seminar_id, $last_participant_id, $invoice_key, $participants);

        // 送信先
        $Mail->to = $data['applicant_mail'];

        // 送信元
        $Mail->from = PPC_FORM_MAIL_FROM;

        // 送信者
        $Mail->sender = get_bloginfo('name');

        // 件名
        $seminar_title = get_the_title($seminar_id);
        $Mail->subject = $seminar_title . 'お申し込みを受付いたしました';

        try {
            // save the data inputted
            $save_data = $this->save_application_data($data, $seminar_id, $term[0], $form_id, $participants, $invoice_key);
            if (!$save_data) {
                $msg = '入力されたデータにはエラーがあります。';
                ppc_error_log([
                    'msg' => 'Error saving data in お申し込み. So cannot send mail.',
                    'file' => __FILE__,
                    'line' => __LINE__,
                ]);
                $this->send_mail_error($Mail, $seminar_id, $form_id, $msg, $data['applicant_mail']);
                return $Mail;
            }

            // error sending email
            if (!$Mail->send()) {
                $msg = '自動メールを送信できません。';
                ppc_error_log([
                    'msg' => 'Error sending mail in お申し込み. It occured during the $Mail->send() function.',
                    'file' => __FILE__,
                    'line' => __LINE__,
                ]);
                $this->send_mail_error($Mail, $seminar_id, $form_id, $msg, $data['applicant_mail']);
                return $Mail;
            } else {
                update_post_meta($form_id, 'ppc_form_error_msg', '');
                ppc_mail_log([
                    'seminar_title' => $seminar_title,
                    'seminar_id'    => $seminar_id,
                    'form_id'       => $form_id,
                    'email'         => $data['applicant_mail'],
                    'file'          => __FILE__,
                    'line'          => __LINE__,
                ]);
            }
        } catch (\Exception $e) {
            $msg = 'エラーがあります。';
            ppc_error_log([
                'msg' => 'Error sending mail in お申し込み. It occured during the $Mail->send() function.' . "\n" . $e->getMessage(),
                'file' => __FILE__,
                'line' => __LINE__,
            ]);
            $this->send_mail_error($Mail, $seminar_id, $form_id, $msg, $data['applicant_mail']);

            return $Mail;
        }

        $Mail->to = PPC_FORM_ADMIN_MAIL_TO;
        ppc_mail_log([
            'seminar_title' => $seminar_title,
            'seminar_id'    => $seminar_id,
            'form_id'       => $form_id,
            'email'         => PPC_FORM_ADMIN_MAIL_TO,
            'file'          => __FILE__,
            'line'          => __LINE__,
        ]);
        return $Mail;
    }

    /**
     * 定員80パーセントを超えました
     *
     * @param object $Mail       Mail default object
     * @param int    $seminar_id セミナーID OR 投稿ID
     * @param int    $form_id    お申し込みフォームID OR 投稿ID
     * @param int    $count　　　 total number of participants
     * @return
     */
    public function send_mail_notice($Mail, $seminar_id, $form_id, $count)
    {
        // セミナータイトル
        $seminar_title = get_the_title($seminar_id);

        // セミナーコード
        $seminar_code = get_field('ppc_seminar_code', $seminar_id);

        // 定員
        $limit = get_field('teiin', $seminar_id);

        // 申込終了予定日時
        $application_end = ppc_get_seminar_date(get_field('application_end', $seminar_id));

        // セミナー実施日時
        $seminordatetime = ppc_get_seminar_date(get_field('seminar_start', $seminar_id), get_field('seminar_end', $seminar_id));

        $msg  = <<<MSG
{$seminar_code}
{$seminar_title}

定員　: {$limit}
申込数: {$count}

申込終了予定日時: {$application_end}
セミナー実施日時: {$seminordatetime}
MSG;
        $Mail->body = $msg;

        // 送信先
        $Mail->to = PPC_FORM_MAIL_TO;

        // 送信元
        $Mail->from = PPC_FORM_MAIL_FROM;

        // 送信者
        $Mail->sender = get_bloginfo('name');

        // 件名
        $Mail->subject = '[通知]' . $seminar_title . '定員80パーセントを超えました';

        try {
            if (!$Mail->send()) {
                ppc_error_log([
                    'msg' => 'Error sending mail notice in お申し込み. It occured during the $Mail->send() function.',
                    'file' => __FILE__,
                    'line' => __LINE__,
                ]);
            } else {
                ppc_mail_log([
                    'seminar_title' => $seminar_title,
                    'seminar_id'    => $seminar_id,
                    'form_id'       => $form_id,
                    'email'         => PPC_FORM_MAIL_TO,
                    'file'          => __FILE__,
                    'line'          => __LINE__,
                ]);
            }
        } catch (\Exception $e) {
            ppc_error_log([
                'msg' => 'Error sending mail in notice お申し込み. It occured during the $Mail->send() function.' . "\n" . $e->getMessage(),
                'file' => __FILE__,
                'line' => __LINE__,
            ]);
        }
    }

    /**
     * 定員{percent}を超えたため受付を停止しました
     *
     * @param object $Mail       Mail default object
     * @param int    $seminar_id セミナーID OR 投稿ID
     * @param int    $form_id    お申し込みフォームID OR 投稿ID
     * @param int    $count　　　 total number of participants
     * @return
     */
    public function send_mail_info($Mail, $seminar_id, $form_id, $count)
    {
        // セミナータイトル
        $seminar_title = get_the_title($seminar_id);

        // セミナーコード
        $seminar_code = get_field('ppc_seminar_code', $seminar_id);

        // 定員
        $limit = get_field('teiin', $seminar_id);

        // 申込終了予定日時
        $application_end = ppc_get_seminar_date(get_field('application_end', $seminar_id));

        // セミナー実施日時
        $seminordatetime = ppc_get_seminar_date(get_field('seminar_start', $seminar_id), get_field('seminar_end', $seminar_id));

        // 定員設定
        $percentage = get_field('seat_setting', $seminar_id);

        $msg  = <<<MSG
{$seminar_code}
{$seminar_title}

定員　: {$limit}
申込数: {$count}

申込終了予定日時: {$application_end}
セミナー実施日時: {$seminordatetime}
MSG;
        $Mail->body = $msg;

        // 送信先
        $Mail->to = PPC_FORM_MAIL_TO;

        // 送信元
        $Mail->from = PPC_FORM_MAIL_FROM;

        // 送信者
        $Mail->sender = get_bloginfo('name');

        // 件名
        $Mail->subject = '[受付停止]' . $seminar_title . '定員' . $percentage['value'] . '%を超えたため受付を停止しました';

        try {
            if (!$Mail->send()) {
                ppc_error_log([
                    'msg' => 'Error sending mail notice in お申し込み. It occured during the $Mail->send() function.',
                    'file' => __FILE__,
                    'line' => __LINE__,
                ]);
            } else {
                ppc_mail_log([
                    'seminar_title' => $seminar_title,
                    'seminar_id'    => $seminar_id,
                    'form_id'       => $form_id,
                    'email'         => PPC_FORM_MAIL_TO,
                    'file'          => __FILE__,
                    'line'          => __LINE__,
                ]);
            }
        } catch (\Exception $e) {
            ppc_error_log([
                'msg' => 'Error sending mail in notice お申し込み. It occured during the $Mail->send() function.' . "\n" . $e->getMessage(),
                'file' => __FILE__,
                'line' => __LINE__,
            ]);
        }
    }

    /**
     * This will send mail that has
     * errors. So this will be
     * sent to PPC_FORM_MAIL_FROM which
     * is in wp-config.php.
     * To inform that there was
     * something wrong in sending the
     * form.
     *
     * @param object $Mail       mw wp form mail data
     * @param int    $seminar_id セミナーID
     * @param int    $form_id    お申し込みID
     * @param string $msg        エラーメッセージ
     * @param string $applicant  連絡者メール
     * @return
     */
    public function send_mail_error($Mail, $seminar_id, $form_id, $msg, $mail_to = '')
    {
        $seminar_title = get_the_title($seminar_id);
        $date = date('Y/m/d H:i:s');
        $Mail->body = <<<MSG
ご担当者様

エラーが発生しました。
お申し込みフォームを送信できません。
{$date}に発生しました。

セミナーID　　　　　：{$seminar_id}
セミナータイトル　　：{$seminar_title}
お申し込みフォームID：{$form_id}
エラーメッセージ　　：
{$msg}

ログファイルにご確認ください。
ログパス：/manager/wp-content/debug
MSG;

        // 送信先
        $Mail->to = PPC_FORM_ADMIN_MAIL_TO;

        // 送信元
        $Mail->from = PPC_FORM_MAIL_FROM;

        // 送信者
        $Mail->sender = get_bloginfo('name');

        // 件名
        $seminar_title = get_the_title($seminar_id);
        $Mail->subject = $seminar_title . 'お申し込みエラー';

        $err_msg = <<<MSG
申し訳ございません。エラーが発生しました。<br />
お申し込みフォームを送信できません。<br />
{$msg}<br />
<br />
お手数ではございますがお電話にてご確認をお願いいたします。<br />
TEL：011-233-3561
MSG;
        // add error message on the complete page
        update_post_meta($form_id, 'ppc_form_error_msg', $err_msg);

        // add mail log
        ppc_mail_log([
            'seminar_title' => $seminar_title,
            'seminar_id'    => $seminar_id,
            'form_id'       => $form_id,
            'email'          => empty($mail_to) ? PPC_FORM_ADMIN_MAIL_TO : $mail_to,
            'file'          => __FILE__,
            'line'          => __LINE__,
        ]);

        return $Mail;
    }

    /**
     * This is to bind the mail
     *
     * @param string  $content              Unbinded mail content
     * @param array   $data                 Post or input data
     * @param string  $seminar_id           Seminar id or 投稿ID
     * @param string  $last_participant_id  The last 参加者ID. This ID is from the database according to seminar_id and form_id
     * @param string  $invoice_key          請求書キー
     * @param array   $participant          参加者一覧
     * @return string $new_content Binded mail content
     */
    private function bind_mail($content, $data, $seminar_id, $last_participant_id, $invoice_key, $participants)
    {
        $new_content = '';
        $jukouryou = get_field('jukouryou', $seminar_id);  // 受講料

        if (isset($data['ppc_sei_applicant']) && isset($data['ppc_mei_applicant'])) {
            // 氏名
            $new_content = mb_ereg_replace('{\$applicant}', $data['ppc_sei_applicant'] . $data['ppc_mei_applicant'], $content);
        }

        // セミナータイトル
        $title = get_the_title($seminar_id);
        $new_content = mb_ereg_replace('{\$seminar_name}', $title, $new_content);

        // 貴社名
        if (isset($data['company_name'])) {
            $new_content = mb_ereg_replace('{\$company_name}', $data['company_name'], $new_content);
        }

        // 参加者
        $paricipant_content = $this->get_participants_mail_content($data, $seminar_id, $last_participant_id, $participants);
        $new_content = mb_ereg_replace('{\$participant}', $paricipant_content, $new_content);

        // 請求書
        if ($jukouryou['value'] === 'paid' && count($participants) > 0) {
            $invoice_content = $this->get_invoice_mail_content($data, $seminar_id, $participants, $invoice_key);
            $new_content = mb_ereg_replace('{\$invoice}', $invoice_content, $new_content);
        } else {
            $new_content = mb_ereg_replace('{\$invoice}', '', $new_content);
        }
        return $new_content;
    }

    /**
     * Get participants
     *
     * @param array   $data                 Post or input data
     * @param string  $seminar_id           Seminar id or 投稿ID
     * @param string  $last_participant_id  The last 参加者ID. This ID is from the database according to seminar_id and form_id
     * @param array   $participants         参加者
     * @return string
     */
    private function get_participants_mail_content($data, $seminar_id, $last_participant_id, $participants)
    {
        $content = '';

        $title  = get_the_title($seminar_id);                    // セミナータイトル
        $start  = get_field('seminar_start', $seminar_id);       // 開始日時
        $end    = get_field('seminar_end', $seminar_id);         // 終了日時（開始日時と同日であれば入力不要）
        $date   = ppc_get_seminar_date($start, $end);            // スケジュール
        $kaijou = html_entity_decode(strip_tags(get_field('kaijou', $seminar_id)));  // 会場
        $google = get_field('google_map_url', $seminar_id);      // GoogleマップのURL

        if (!empty($participants)) {
            foreach ($participants as $key => $val) {
                $cnt = ($key + 1) + $last_participant_id;
                $receipt_no = $this->get_receipt_no($seminar_id, $cnt);
                $name = $val['sei'] . $val['mei'];
                $content .= <<<STR
受講票No    {$receipt_no}
セミナー名　『{$title}』
開催日時　　 {$date}　(会場は30分前)
ご参加者名　 {$data['company_name']}　{$name}様
会　　場
{$kaijou}

GoogleマップURL
{$google}

※ セミナー当日は、受講票をご持参願います。
（お車での来場はご遠慮願います）
当日、会場でお待ちしております。
-------------------------------------------------------------------------------------------------------------

STR;
            }
        }

        return $content;
    }
}
