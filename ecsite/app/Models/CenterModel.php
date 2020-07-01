<?php
namespace App\Http\Models\Manages;

use DB;
use CdLog;
use Exception;
use App\ORM\Master\MCenter;
use App\Http\Models\AppBaseModel;

/**
 * センター管理のビジネスロジッククラス
 */
class CenterModel extends AppBaseModel
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct() {}

    /**
     * センター管理検索データを取得
     *
     * @param  array $param 入力のデータ
     * @return array        レコードと合計
     */
    public function getSearchData($request)
    {
        // ページャーに使用
        $request['OFFSET'] = (int)($request['OFFSET'] - 1) * $request['LIMIT'];
        $forCount = true;

        $MCenter = new MCenter();
        $rec     = $MCenter->selectSearchData($request);
        $total   = $MCenter->selectSearchData($request, $forCount);

        return [
            'rec'   => $rec,
            'total' => $total[0]->cnt
        ];
    }

    /**
     * 編集と詳細編集ページで
     * パラメータIDかCODEを確認する
     *
     * @param  int     $num  センターID
     * @return boolean       存在する場合はtrue、そうでない場合はfalse
     */
    public function checkNum($request)
    {
        $MCenter = new MCenter();
        $res = $MCenter->countCenter($request);
        return $res[0]->cnt > 0 ?true:false;
    }

    /**
     * センター管理詳細を取得
     *
     * @param  array  $param センターID
     * @return array  $res   詳細データ
     */
    public function getDetail($request)
    {
        $MCenter = new MCenter();
        return $MCenter->selectDetails($request);
    }

    /**
     * 新規登録
     *
     * @param  array       $param 入力パラメータ
     * @return boolean|int        エラーが挿入された場合はfalse、そうでない場合は最新のIDを返します。
     */
    public function register($request)
    {
CdLog::info(print_r($request, true));
        $param = $request;
        unset($param['TYPE']);
        unset($param['CENTERID']);
        unset($param['ADDRESS1']);

        // パラメータを設定
        $param['DEPARTUREBASENO']     = '020';                // 発ベースNo
        $param['CREATEDATETIME']      = date('Y-m-d H:i:s');  // 登録日時
        $param['CREATEUSERID']        = $this->getUserId();   // 登録者ID
        $param['UPDATEDATETIME']      = date('Y-m-d H:i:s');  // 更新日時
        $param['UPDATEUSERID']        = $this->getUserId();   // 更新者ID
        $param['LOCALGOVERNMENTCODE'] = $request['ADDRESS1']; // 地方自治体コード
        $param['DELETEFLAG']          = config('const.DELETE_FLAG.OFF'); // 削除フラグ

CdLog::info(print_r($param, true));
        try {
            DB::beginTransaction();

            $MCenter = new MCenter();
            $res = $MCenter->create($param);

            if (empty($res)) {
                DB::rollBack();
                return false;
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            $logInfo = MCenter::getLogInfo(debug_backtrace());
            CdLog::error($e->getMessage(), $logInfo['file'], $logInfo['line']);
            return false;
        }

        return false;
    }

    /**
     * 詳細編集
     *
     * @param  array   $param 入力パラメータ
     * @return boolean        更新エラーの場合はfalse、そうでない場合はtrue
     */
    public function update($request)
    {
        $param = $request;

        // parameters
        $param['LOCALGOVERNMENTCODE'] = $request['ADDRESS1']; // 地方自治体コード
        $param['DEPARTUREBASENO']     = '020';                // 発ベースNo
        $param['UPDATEDATETIME']      = date('Y-m-d H:i:s');  // 更新日時
        $param['UPDATEUSERID']        = $this->getUserId();   // 更新者ID

        // 不要
        unset($param['CENTERID']);
        unset($param['ADDRESS1']);

        $data = MCenter::find($request['CENTERID']);

        try {
            DB::beginTransaction();

            $res = $data->fill($param)->save();
            if ($res !== true) {
                DB::rollBack();
                return false;
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            $logInfo = MCenter::getLogInfo(debug_backtrace());
            CdLog::error($e->getMessage(), $logInfo['file'], $logInfo['line']);
            return false;
        }

        return false;
    }

    /**
     * データを削除
     * でも削除フラグを更新
     * だけです。削除フラグ
     * は0から1に変更されます。
     *
     * @param  int     $id センターID
     * @return boolean     更新エラーの場合はfalse、そうでない場合はtrue
     */
    public function remove($id)
    {
        $param['DELETEFLAG'] = config('const.DELETE_FLAG.ON');

        $data = MCenter::find($id);

        try {
            DB::beginTransaction();

            $res = $data->fill($param)->save();
            if ($res !== true) {
                DB::rollBack();
                return false;
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            $logInfo = MCenter::getLogInfo(debug_backtrace());
            CdLog::error($e->getMessage(), $logInfo['file'], $logInfo['line']);
            return false;
        }

        return false;
    }
}
