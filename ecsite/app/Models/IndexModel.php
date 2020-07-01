<?php
namespace App\Http\Models;

use DB;
use CdLog;
use Exception;
use App\Http\Models\AppBaseModel;
use App\ORM\Master\MAlert;
use App\ORM\Transaction\TAlertSend;
use App\ORM\Transaction\TAlertTarget;

/**
 * トップページモデル
 */
class IndexModel extends AppBaseModel
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct(){}

    /**
     * 自分に来たalertデータ件数を取得
     *
     * @param  int $userid セッションユーザID
     * @return int $res    count
     */
    public function getAlertCount($userid)
    {
        $TAlertSend = new TAlertSend();
        $res = $TAlertSend->selectUserAlertCount($userid);
        return $res[0]->cnt;
    }

    /**
     * アラート通知対象
     * チェックボックスの使う
     *
     * @param  int   $userid セッションユーザID
     * @return array $res    アラート通知対象データ
     */
    public function getTarget($userid)
    {
        $MAlert = new MAlert();
        $res = $MAlert->selectAlertTarget($userid);
        return $res;
    }

    /**
     * アラート通知履歴
     * ビューでテーブルのデータ
     *
     * @param  int   $userid セッションユーザID
     * @return array $res    アラート通知履歴データ
     */
    public function getTargetData($request, $userid, $target)
    {
        $targetList = [];

        // アラート通知対象
        foreach ($target as $value) {
            $targetList[] = !isset($value->ALERTCODE) ? substr($value, 0, 2): substr($value->ALERTCODE, 0, 2);
        }
        $target = implode(', ', $targetList);

        // ページャーに使用
        $request['OFFSET'] = (int)($request['OFFSET'] - 1) * $request['LIMIT'];
        $forCount = true;

        $TSend = new TAlertSend();
        $rec   = $TSend->selectAlertData($request, $userid, $target);
        $total = $TSend->selectAlertData($request, $userid, $target, $forCount);

        return [
            'rec'   => $rec,
            'total' => $total[0]->cnt,
        ];
    }

    /**
     * アラートのステータスは対応済に変更
     *
     * @param  array   $sendid アラート通知履歴ID
     * @param  int     $userid ログインユーザーのID
     * @return boolean $res    更新に失敗したらFALSE, else TRUE
     */
    public function updateStatus($sendid, $userid)
    {
        $status = 1; // ステータスは対応済みになります

        $TAlertSend = new TAlertSend();
        $data = $TAlertSend::find($sendid);

        try {
            DB::beginTransaction();

            $param = [
                'STATUS'         => $status, // ステイタス : ０：未対応 １：対応済み
                'UPDATEUSERID'   => $userid, // 登録者ID
                'UPDATEDATETIME' => date('Y-m-d H:i:s'), // 更新日時
            ];

            $res = $data->fill($param)->save();
            if ($res !== true) {
                DB::rollBack();
                return false;
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            $logInfo = $TAlertSend::getLogInfo(debug_backtrace());
            CdLog::error($e->getMessage(), $logInfo['file'], $logInfo['line']);
            return false;
        }

        return false;
    }
}
