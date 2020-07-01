<?php
/**
 * 共通データモデル
 *
 */
namespace App\Http\Models;

use DB;
use Exception;
use App\ORM\Master\MZipCode;
use App\ORM\Master\MCenter;

/**
 * 共通データ
 *
 * @author ppc
 *
 */
class CommonModel
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct(){}

    /**
     * 都道府県
     *
     * @return object 都道府県の一覧
     */
    public function getPrefecture()
    {
        $sql = <<<__SQL
SELECT
  *
FROM
  m_localgovernment
WHERE FLAG = ?
ORDER BY
  CODE
__SQL;

        return DB::select($sql, [config('const.ADDRESS_FLAG.ADDRESS1')]);
    }

    /**
     * 郵便番号データを取得
     *
     * @param  array $zipcode 郵便番号
     * @return array          郵便番号データ「郵便番号,都道府県コード,市区町村名,町域名」
     */
    public function getZipcodeData($zipcode)
    {
        $MZipCode = new MZipCode();
        return $MZipCode->selectZipcode($zipcode);
    }

    /**
     * 全センターデータを取得
     *
     * @return array
     */
    public function getCenterData()
    {
        $MCenter = new MCenter();
        return $MCenter->selectAll();
    }
}
