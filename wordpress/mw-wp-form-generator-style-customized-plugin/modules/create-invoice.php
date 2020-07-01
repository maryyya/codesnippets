<?php

/**
 * 請求作成
 */
class Create_invoice
{
    /**
     * カテゴリー略語
     *
     * This is used in receipt no 受付番号
     */
    const CAT_ABV = [
        'fresh'      => 'fr',   // 新入社員研修会
        'public'     => 'pu',   // 公開セミナー
        'lilac'      => 'li',   // 道銀らいらっく会ビジネス研修会
        'management' => 'ma',   // 道銀らいらっく会ビジネス研修会
    ];

    /**
     * 請求書のメール内容
     *
     * @param  array  $data         Input data from お申し込み
     * @param  int    $seminar_id   セミナーID or 投稿ID
     * @param  array  $participants 参加者
     * @return string $content      請求書メール内容
     */
    protected function get_invoice_mail_content($data, $seminar_id, $participants, $invoice_key)
    {
        $type = isset($data['type']['data']) ? $data['type']['data'] : $data['type'];

        // confirm if 種別 has value meaning if it was checked
        if (empty($type)) {
            return '';
        }

        $amount      = number_format($this->calculate_invoice($seminar_id, $type, $participants));  // total amount
        $invoice_url = home_url() . '/invoice/' . $invoice_key;                                     // 請求書URL

        $content = <<<STR
※お振込は下記口座へ翌月末日までにお願いいたします。
（恐れ入りますが、お振込手数料は貴社でご負担願います。）
請求金額　　￥{$amount}

請求書はこちらでダウンロードお願いいたします。
{$invoice_url}

STR;

        return $content;
    }

    /**
     * Create 受付番号
     *
     * @param int     $seminar_id セミナーID
     * @param int     $junban_id  順番ID
     * @return string $receipt_no 受付ID
     */
    protected function get_receipt_no($seminar_id, $junban_id)
    {
        $seminar_code = get_field('ppc_seminar_code', $seminar_id);
        $receipt_no = $seminar_code . '-' .  sprintf('%03d', $junban_id);
        return $receipt_no;
    }

    /**
     * This is to calculate the 請求書
     *
     * @param  int    $seminar_id   セミナーID or 投稿ID
     * @param  string $input type   種別, This is in checkbox form. Can be 'member_1, member_2' or 'general'
     * @param  array  $participants 参加者
     * @return int    $amount       Total amount to pay
     */
    protected function calculate_invoice($seminar_id, $input_type, $participants)
    {
        $member_fee  = get_field('member_fee', $seminar_id);   // 会員
        $general_fee = get_field('general_fee', $seminar_id);  // 一般

        $fee = mb_strpos($input_type, 'member') === false ? $general_fee : $member_fee;
        $amount = (int) $fee * (int) count($participants);

        return $amount;
    }

    /**
     * 請求書キー
     * This will be used in the
     * invoice url.
     * SECURE_AUTH_SALT is in wp-config.php
     * defined.
     *
     * @param  int    $seminar_id           セミナーID OR 投稿ID
     * @param  int    $term_id              カテゴリーID
     * @param  int    $form_id              お申し込みID OR 投稿ID
     * @param  int    $last_application_id  last application id inserted
     * @return string $hash
     */
    protected function create_invoice_key($seminar_id, $term_id, $form_id, $last_application_id)
    {
        $year = date('Y');
        $new_application_id = $last_application_id + 1;
        $hash = hash('gost', $year . SECURE_AUTH_SALT . $seminar_id . $term_id . $form_id . $new_application_id);
        return $hash;
    }
}
