<?php

require_once 'ppc_tcpdf.php';

/**
 * 請求書をダウンロードのクラス
 * This is mainly used in
 * downloading invoice.
 * This is called in
 * /themes/xxx/page-invoice.php
 *
 * ppc_reiwa() is in themes/xx/functions.php
 */
class Download_invoice
{
    /**
     * This is the 投稿ID.
     * This can be found in
     * 固定ページ. The title
     * is 請求書作成.
     */
    const INVOICE_PAGE_ID = 615;

    /**
     * Temporary filename
     *
     * @var string
     */
    private $pdf_filename;

    /**
     * TCPDF path
     *
     * @var string
     */
    public $tcpdf_path;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pdf_filename = '請求書_' . date('YmdHis') . '.pdf';

        // This is the plugin used to download the pdf
        $this->tcpdf_path = WP_CONTENT_DIR . '/plugins/html-pdf-generator';
    }

    /**
     * Check the key first if it exists
     *
     * @param string $invoice_key
     * @return boolean false if given key has no data otherwise true if there's data
     */
    public function check_key($invoice_key)
    {
        global $wpdb;

        $param = [sanitize_text_field($invoice_key)];
        $sql = <<<SQL
        SELECT
        count(*) as cnt
        FROM {$wpdb->prefix}seminar_application
        WHERE
        invoice_key = %s
        SQL;

        $prepare = $wpdb->prepare($sql, $param);
        $res = $wpdb->get_results($prepare);

        return (int) $res[0]->cnt < 1 ? false : true;
    }

    /**
     * Download the pdf
     *
     * @param object $invoice_key This is the parameter from the url the invoice key.
     * @return
     */
    public function download_pdf($invoice_key)
    {
        $content = $this->bind_data($invoice_key);
        $this->create_pdf($content);
    }

    /**
     * To create the pdf data
     *
     * @param string $content      Invoice content
     * @param string $pdf_filename It's declared in download-invoice.php
     */
    public function create_pdf($content)
    {
        $pdf = new PPC_TCPDF;

        // set document information
        $pdf->SetCreator(get_bloginfo('name'));

        $pdf->SetTitle(get_bloginfo('name') . '請求書');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------
        $pdf->AddPage('P', 'A4');
        $font = TCPDF_FONTS::addTTFfont($this->tcpdf_path . '/includes/fonts/msttf/MSMINCHO.TTF', 'TrueTypeUnicode', 32);
        // set font
        $pdf->SetFont($font, '', 10);
        // $pdf->SetFont('kozgopromedium', '', 10);
        $pdfHtml = <<<EOD
<style>
.border-top {
    border-top: 1px solid black;
    border-left: 1px solid black;
    border-right: 1px solid black;
}
.border-center {
    border-left: 1px solid black;
    border-right: 1px solid black;
}

.border-bottom {
    border-left: 1px solid black;
    border-right: 1px solid black;
    border-bottom: 1px solid black;
}
</style>
{$content}
EOD;

        $pdf->writeHTML($pdfHtml, true, false, true, false, '');

        //Close and output PDF document
        $pdf->Output($this->pdf_filename, 'D'); // download pdf
        // $pdf->Output($this->pdf_filename, 'I'); // display pdf
    }

    /**
     * Bind the invoice content
     *
     * @param  object $invoice_key This is the parameter from the url the invoice key.
     * @return string $content     This is the content for the invoice or the 請求書.
     */
    private function bind_data($invoice_key)
    {
        $post_content = get_post(self::INVOICE_PAGE_ID);
        $tmp_content  = $post_content->post_content;
        $invoice_data = $this->get_invoice_data_needed($invoice_key);

        // 日付
        $content = mb_ereg_replace('{{\$date}}', ppc_reiwa($invoice_data[0]->post_date), $tmp_content);

        // 貴社名
        $comp_name = empty($invoice_data[0]->company_name) ? '' : $invoice_data[0]->company_name;
        $content   = mb_ereg_replace('{{\$company_name}}', $comp_name, $content);

        // Total amount
        $amount  = empty($invoice_data[0]->amount) ? 0 : $invoice_data[0]->amount;
        $content = mb_ereg_replace('{{\$amount}}', $amount, $content);

        // セミナー名
        $seminar_name  = empty($invoice_data[0]->seminar_name) ? 0 : $invoice_data[0]->seminar_name;
        $content = mb_ereg_replace('{{\$seminar_name}}', $seminar_name, $content);

        // 支払期限日
        $deadline = empty($invoice_data[0]->payment_deadline) ? '' : $invoice_data[0]->payment_deadline;
        $content  = mb_ereg_replace('{{\$payment_deadline}}', $deadline, $content);

        // 単価
        $type = empty($invoice_data[0]->type) ? 'general' : mb_strpos($invoice_data[0]->type, 'general') === false ? 'member' : 'general';
        $unit_price = $type === 'general' ? $invoice_data[0]->general_fee : $invoice_data[0]->member_fee;
        $content  = mb_ereg_replace('{{\$unit_price}}', $unit_price, $content);

        // 申込人数
        $participants = empty($invoice_data[0]->total_participants) ? '' : $invoice_data[0]->total_participants;
        $content  = mb_ereg_replace('{{\$participants}}', $participants, $content);

        return $content;
    }

    /**
     * 請求書データ
     *
     * @param  object $invoice_key This is the parameter from the url the invoice key.
     * @return array  $res         The data needed for the invoice.
     */
    private function get_invoice_data_needed($invoice_key)
    {
        global $wpdb;

        $param = [$invoice_key];

        $sql = <<<SQL
SELECT
    application.seminar_id,
    REPLACE(JSON_EXTRACT(application.data, '$."company_name"'), '"', '') as company_name,
    REPLACE(JSON_EXTRACT(application.data, '$."type"'), '"', '') as type,
    FORMAT(application.amount, 0) as amount,
    application.post_date,
    (SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = seminar_id) as seminar_name,
    payment_deadline.meta_value AS payment_deadline,
    FORMAT(general_fee.meta_value, 0) AS general_fee,
    FORMAT(member_fee.meta_value, 0) AS member_fee,
    count(participant.application_id) as total_participants
FROM
    {$wpdb->prefix}seminar_application application
    INNER JOIN
    (SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'general_fee') AS general_fee ON general_fee.post_id = application.seminar_id
    INNER JOIN
    (SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'member_fee') AS member_fee ON member_fee.post_id = application.seminar_id
    INNER JOIN
    (SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'ppc_money_payment_deadline') as payment_deadline ON payment_deadline.post_id = application.form_id
    INNER JOIN
    {$wpdb->prefix}seminar_participant participant ON application.ID = participant.application_id
WHERE
    application.invoice_key = %s;
SQL;

        $prepare = $wpdb->prepare($sql, $param);
        $res = $wpdb->get_results($prepare);
        return $res;
    }
}
