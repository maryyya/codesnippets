<?php
namespace App\Http\Models\Manages;

use DB;
use CdLog;
use Exception;
use App\ORM\Master\MHoliday;
use App\Http\Models\AppBaseModel;

/**
 * センター管理のビジネスロジッククラス
 */
class HolidayModel extends AppBaseModel
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct() {}

    /**
     * 対象年
     *
     * @return object 年一覧
     */
    public function getYear()
    {
        $MHoliday = new MHoliday();
        return $MHoliday->getTargetYear();
    }

    /**
     * 検索データを取得
     *
     * @param  array $req      POSTのデータ
     * @return array $calendar カレンダー記録
     */
    public function getSearchData($req)
    {
        $MHoliday = new MHoliday();
        $res      = $MHoliday->getAllData($req);
        $calendar = [];

        foreach ($res as $key => $value) {
            $day         = strtolower(date('D', strtotime($value->date)));  // 今週の名前
            $month       = substr($value->date, 0, 7);                      // 月
            $lastDay     = date('t', strtotime($value->date));              // 指定した月の日数
            $weekOfMonth = $this->weekOfMonth($value->date);                // 月の週
            $dayOfMonth  = date('d', strtotime($value->date));              // 日
            $dayOfWeek   = date('N', strtotime($value->date));              // 曜日の数値表現

            $data = [
                'date'       => $value->date,
                'status'     => $value->status,
                'dayOfMonth' => $dayOfMonth,
                'day'        => $day,
                'type'       => $req['btnType']['type'],
                'holflg'     => $value->HOLIDAYFLAG,
                'pubholflg'  => $value->PUBLICHOLIDAYFLAG
            ];

            // 週の最初の曜日の前に空の配列を追加
            if ($weekOfMonth === 0 && $dayOfMonth === '01') {
                // 日曜日は含まない
                if ($dayOfWeek == 7) {
                    $calendar[$month][$weekOfMonth][] = $data;
                    continue;
                }

                // 空の配列を追加
                for ($i=0; $i < $dayOfWeek; $i++) {
                    $calendar[$month][$weekOfMonth][] = [];
                }

                // add data
                $calendar[$month][$weekOfMonth][] = $data;
                continue;
            }

            // 最後の曜日の後に空の配列を追加
            if ($weekOfMonth > 3 && strpos($value->date, $lastDay) !== false) {
                // add data
                $calendar[$month][$weekOfMonth][] = $data;

                // num of empty arrays to add
                $cnt = $dayOfWeek == 7 ? 6 : 6 - $dayOfWeek;

                // 週の最初の曜日の前に空の配列を追加
                for ($i=0; $i < $cnt; $i++) {
                    $calendar[$month][$weekOfMonth][] = [];
                }
                continue;
            }

            $calendar[$month][$weekOfMonth][] = $data;
        }

        return $calendar;
    }

    /**
     * カレンダー更新
     *
     * @param  array   $req POSTパラメータ
     * @return boolean      false if update error, else true
     */
    public function update($req)
    {
        try {
            DB::beginTransaction();

            foreach ($req['param'] as $value) {
                $data = Mholiday::find($value['date']);

                // 休日なし、通常営業日
                switch ($value['changeTo']) {
                    // 通常の営業日
                    case 'holiday':
                        $params['HOLIDAYFLAG'] = $value['value'] == 1?0:1;
                        break;

                    // 祝祭日の休業日
                    case 'publicHoliday':
                        $params['PUBLICHOLIDAYFLAG'] = $value['value'] == 1?0:1;
                        break;

                    // 祝祭日の休業日
                    default:
                        $params['HOLIDAYFLAG']       = 0;
                        $params['PUBLICHOLIDAYFLAG'] = 0;
                        break;
                }

                $params['UPDATEDATETIME'] = date('Y-m-d H:i:s'); // 更新日時
                $params['UPDATEUSERID']   = $this->getUserId();  // 更新者ID

                $data->update($params);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $logInfo = Mholiday::getLogInfo(debug_backtrace());
            CdLog::error($e->getMessage(), $logInfo['file'], $logInfo['line']);
            return false;
        }

        return true;

    }

    /**
     * 今月の週
     * Ex. 2018-01-01 = 1
     * 2018-12-03 = 2
     *
     * @param  string $date 日付
     * @return int          今月の週
     */
    private function weekOfMonth($date)
    {
        // その月の最初の日
        $firstOfMonth = date('Y-m-01', strtotime($date));

        // 今年の週
        $thisWeek = intval(strftime('%U', strtotime($date)));

        // 月の最初の日の週 + 1
        $weekOfMonth = intval(strftime('%U', strtotime($firstOfMonth)));

        return $thisWeek - $weekOfMonth;
    }


    public function getPublicHolidayInfo($date)
    {
        $MHoliday = new MHoliday();
        $result = $MHoliday->getPublicHolidayFlag($date);
        return $result;
    }
}
