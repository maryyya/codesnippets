<?php
namespace App\Http\Models\Manages;

use App\Http\Models\AppBaseModel;
use App\ORM\Master\MAlert;
use DB;
use DateTime;
use Exception;

/**
 * アラート管理のビジネスロジッククラス
 *
 * @author
 */
class AlertModel extends AppBaseModel
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {}
    
    /**
     * アラート管理一覧データを取得
     *
     * @param 
     * @return array アラート一覧
     */
    public function getAlertData()
    {
        $mAlert = new MAlert();
        $res = $mAlert->selectByAlert();
        return $res;
    }

    /**
     * アラート管理更新データを取得
     *
     * @param  int アラートID
     * @return array アラートマスタデータ
     */
    public function getdetailValue($id)
    {
        $mAlert = new MAlert();
        $res = $mAlert->selectByAlertDetail($id);
        return $res;
    }

    /**
     * 対象ユーザーがいない場合のデータを取得
     *
     * @param 
     * @return array アラート管理
     */
    public function getAlertDataOnly($id)
    {
        $mAlert = new MAlert();
        $res = $mAlert->selectByAlertOnly($id);
        return $res;
    }

    /**
     * アラート対象ユーザー数検索
     *
     * @param  int アラートID
     * @return array アラートマスタデータ
     */
    public function getTargetUser($id)
    {
        $mAlert = new MAlert();
        $res = $mAlert->selectByTargetUser($id);
        return $res;
    }

    /**
     * アラートデータを更新
     *
     * @param  int アラート情報
     * @return array アラートマスタデータ
     */

    public function registAlert($alertData)
    {
        $mAlert = new MAlert();
        $upUser = $this->getUserId();
        $res = $mAlert->updateByAlert($alertData, $upUser);
        return $res;
    }

    /**
     * アラート通知対象マスタを編集
     *
     * @param  int アラート対象ユーザー,アラートID
     * @return array アラート通知対象マスタデータ
     */

    public function deleteAlertTarget($alertId)
    {
        $mAlert = new MAlert();
        $upUser = $this->getUserId();
        $res = $mAlert->deleteByAlertTarget($alertId, $upUser);
        return $res;
    }

    /**
     * アラート通知対象マスタを更新
     *
     * @param  int アラート対象ユーザー,アラートID
     * @return array アラート通知対象マスタデータ
     */

    public function upsertAlertTarget($userData,$alertData)
    {
        $mAlert = new MAlert();
        $upUser = $this->getUserId();
        $userDataLength = count($userData);
        for($i=0;$userDataLength > $i;$i++){
            $mAlert->updateByAlertTarget($userData[$i], $alertData, $upUser);
        }
        return;
    }
}
