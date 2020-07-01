<?php

namespace App\Modules;

/**
 * Class InsertIdeaBook
 *
 * @package App\Modules
 */
class InsertIdeaBook implements InsertInterface
{
    /**
     * テーブル名
     *
     * @string
     */
    const TABLE = 'originalform_ideabook';

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
            $paymentTypeList = isset($param['paymentTypeList']) ? $param['paymentTypeList'] : array();
            $formData = array(
                'name'        => $param["name"],            // 注文者
                'receiptno'   => $param["receiptno"],       // 受付番号
                'office_name' => $param["office_name"],     // 法人・団体名
                'department'  => $param["department"],      // 部署
                'tel'         => $param["phone1"] . "-" . $param["phone2"] . "-" . $param["phone3"], // 電話番号
                'fax'         => $param["fax1"] . "-" . $param["fax2"] . "-" . $param["fax3"],       // FAX番号
                'mail'        => $param["mail"], // E-mail
                'zip'         => $param["zip_code1"] . "-" . $param["zip_code2"], // 郵便番号
                'addr1'       => $param["addr1"], // 納品先住所１
                'addr2'       => isset($param["addr2"]) ? $param["addr2"] : '', // 納品先住所２
                'amount'      => $param["amount"], // 印刷アイデアBook
                'payment_type'=> isset($paymentTypeList[$param["payment_type"]]) ? $paymentTypeList[$param["payment_type"]] : '', // お支払い方法
                'freetext'    => isset($param["freetext"]) ? $param["freetext"] : '', // 自由記入欄
                'dt_created'  => date('Y-m-d H:i:s')
            );
            if (!$db->insert(self::TABLE, $formData)) {
                $log->error('['.self::TABLE.']データエラー。データ：' . json_encode($param, JSON_UNESCAPED_UNICODE));
                return false;
            }
        } catch (Exception $e) {
            $log->error('['.self::TABLE.']データ追加エラー。データ：' . json_encode($param, JSON_UNESCAPED_UNICODE));
            return false;
        }

        return true;
    }
}
