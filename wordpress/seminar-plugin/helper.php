<?php

/**
 * Just common function used
 * for other classes.
 */
class Helper
{
    /**
     * Get the status of the seminar
     *
     * @param  int    $post_id
     * @return string $html
     */
    public function status_html($post_id)
    {
        $status = ppc_get_seminar_status($post_id);
        $status_jp    = $status === 'open' ? '受付中' : ($status === 'close' ? '受付終了' : '');         // 日本語
        $status_class = $status === 'open' ? 'is_accepting' : ($status === 'close' ? 'is_close' : '');  // セミナーステータス
        $html = <<<HTML
        <style>
        .c_article_label.is_accepting {
        background-color: #c10404;
        }
        .c_article_label.is_close {
        background-color: #707070;
        }
        .c_article_label {
        display: inline-block;
        min-width: 100px;
        border-radius: 50px;
        color: #fff;
        text-align: center;
        margin-left: 10px;
        }
        </style>
        <span class="c_article_label {$status_class}">{$status_jp}</span>
        HTML;

        return $html;
    }


    /**
     * Get the 参加者
     *
     * @param array $data
     * @return array
     */
    public function get_participants($data)
    {
        $tmp_participants = [];
        foreach ($data as $key => $val) {
            if (strpos($key, 'participant') !== 0 && !strpos($key, 'participant')) {
                continue;
            }

            $num = substr($key, -1);
            $tmp_participants[$num] = '';
        }

        $participants = [];
        foreach ($tmp_participants as $key => $val) {
            $participants[] = [
                'sei'      => isset($data['ppc_sei_participant_name_' . $key]) ? $data['ppc_sei_participant_name_' . $key] : '', // 姓
                'mei'      => isset($data['ppc_mei_participant_name_' . $key]) ? $data['ppc_mei_participant_name_' . $key] : '', // 名
                'sei_kana' => isset($data['ppc_sei_participant_name_' . $key]) ? $data['ppc_sei_participant_name_' . $key] : '', // セイかな
                'mei_kana' => isset($data['ppc_mei_participant_name_' . $key]) ? $data['ppc_mei_participant_name_' . $key] : '', // 名かな
                'mail'     => isset($data['participant_mail_' . $key]) ? $data['participant_mail_' . $key] : '',         // メールアドレス
                'title'    => isset($data['participant_title_' . $key]) ? $data['participant_title_' . $key] : '',        // お役職

            ];
        }

        return $participants;
    }
}
