<?php

namespace App\Modules;

/**
 * Class InsertMask
 *
 * @package App\Modules
 */
class InsertMask implements InsertInterface
{
    /**
     * テーブル名
     *
     * @string
     */
    const TABLE = 'originalform_mask';

    /**
     * Insertデータ
     *
     * @param  array $param フォームデータ
     * @return bool         エラーがあったらfalse、else true.
     */
    public function insert($param)
    {
        $log = new Log();
        $db  = new Db();
        try {
            if (!$db->insert(self::TABLE, $this->getOrganizeData($param))) {
                $log->error('[' . self::TABLE . ']データエラー。データ：' . json_encode($param, JSON_UNESCAPED_UNICODE));
                return false;
            }
        } catch (Exception $e) {
            $log->error('[' . self::TABLE . ']データ追加エラー。データ：' . json_encode($param, JSON_UNESCAPED_UNICODE));
            return false;
        }

        return true;
    }

    /**
     * $formDataのkeyはテーブルのカラム名です。
     *
     * @param array $param フォームデータ
     * @return array $formData テーブルの構成に応じて整理された配列
     */
    private function getOrganizeData($param)
    {
        // お支払い方法一覧
        $paymentTypeList = isset($param['paymentTypeList']) ? $param['paymentTypeList'] : array();
        $formData        = array(
            'name'           => $param["name"],            // ご担当者
            'receiptno'      => $param["receiptNoMask"],   // 受付番号
            'office_name'    => $param["office_name"],     // 法人・団体名
            'department'     => $param["department"],      // 部署
            'tel'            => $param["phone1"] . "-" . $param["phone2"] . "-" . $param["phone3"], // 電話番号
            'fax'            => $param["fax1"] . "-" . $param["fax2"] . "-" . $param["fax3"],       // FAX番号
            'mail'           => $param["mail"], // E-mail
            'zip'            => $param["zip_code1"] . "-" . $param["zip_code2"], // 郵便番号
            'addr1'          => $param["addr1"], // ご請求先住所１
            'addr2'          => isset($param["addr2"]) ? $param["addr2"] : '', // ご請求先住所２
            'nouhin_check'   => $param["nouhin_check"], // 納品先住所フラグ
            'nouhin_addr'    => ($param["nouhin_check"] != 1) ? $param["nouhin_addr"] : '（ご請求先住所と同じ）', // 納品先住所
            'amount1'        => $param["amount1"], // 大人用個包装なし:20箱単位
            'amount2'        => $param["amount2"], // 大人用個包装あり:30箱単位
            'amount3'        => $param["amount3"], // 子供用個包装あり:40箱単位
            'alcohol'        => $param["alcohol"], // アルコール除菌ジェル
            'camera_dome'    => $param["camera_dome"], // NSS非接触サーモグラフィー（ドーム型）カメラ
            'camera_guntype' => $param["camera_guntype"], // NSS非接触サーモグラフィー（ガンタイプ型）カメラ
            'payment_type'   => isset($paymentTypeList[$param["payment_type"]]) ? $paymentTypeList[$param["payment_type"]] : '', // お支払い方法
            'howknow'        => isset($param["howknow"]) ? $param["howknow"] : '',   // このマスク通販をどこで知りましたか
            'freetext'       => isset($param["freetext"]) ? $param["freetext"] : '', // 自由記入欄
            'spare01'        => isset($param["coupon"]) ? $param["coupon"] : '', // クーポンコードを予備01に
        );

        $formData['dt_created'] = date('Y-m-d H:i:s');
        return $formData;
    }
}
