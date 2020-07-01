<?php
/**
 * 野球場のデータ取得、更新、削除
 */
class StadiumRegister
{
    public $search;

    /**
     * Main function
     * It will choose whether a check of add.
     *
     * @return json
     */
    public function displayCalendar($params)
    {
        $res = array(
            'status' => 'ng',
            'msg'    => '',
            'data'   => array()
        );

        // data from the database
        $data = $this->getData($params);
        if ($data === false) {
            return $res;
        }

        // this will get the calendar date
        $date   = $this->getDate($params);

        // combined data
        $calendarData = $this->getCalendarData($data, $date);

        // sorted data
        $sortedData = $this->sortedCalendar($calendarData);

        $res['status'] = 'ok';
        $res['data']   = array(
            'site_url'   => $params['site_url'],
            'home_url'   => home_url(),
            'plugin_dir' => $params['plugin_dir'],
            'calendarnm' => (int)$params['place'] > 1 ? '川下グラウンド':'拓北野球場',
            'date'       => substr($params['date'], 0, 4).'年'.substr($params['date'], -5, 2).'月',
            'calendar'   => $sortedData,
            'next'       => date("Y年m月", mktime(0, 0, 0, substr($params['date'], -5, 2)+1, 1, substr($params['date'], 0, 4))),
            'before'     => date("Y年m月", mktime(0, 0, 0, substr($params['date'], -5, 2)-1, 1, substr($params['date'], 0, 4))),
            'type'       => $params['place'],
            'datenormal' => substr($params['date'], 0, 4).'-'.substr($params['date'], -5, 2),
            'edit_date'  => isset($params['edit_date'])?$params['edit_date']:''
        );

        return $res;
    }

    /**
     * This one holds the inserting/updating/deleting
     * of events each day in this monthly calendar.
     *
     * @param  array $param data from input
     * @return array $res
     */
    public function registerCalendar($param)
    {
        $res = array(
            'status' => 'ng',
            'msg'    => '',
            'data'   => array()
        );

        if ($this->crud($param)) {
            $param['date'] = substr($param['date'], 0, 12);
            $res = $this->displayCalendar($param);
        }

        return $res;
    }

    /**
     * Get the date by year and month
     * since it is a monthly calendar
     *
     * @return array Calendar data from db
     */
    private function getData($params)
    {
        global $wpdb;
        $year  = substr($params['date'], 0, 4);
        $month = substr($params['date'], -5, 2);
        $type  = $params['place'];

        // 1と2しかない前提（SQLインジェクション対応）
        if ($type !== '1' && $type !== '2') {
            return false;
        }

        $prevmonth = date('m', strtotime('-1 month', strtotime($year.'-'.$month.'-01')));
        $prevyear  = $prevmonth === '12' ? date('Y', strtotime('-1 year', strtotime($year.'-'.$month.'-01'))) : $year;
        $lastdayprevmonth = date('Y-m-d', strtotime('last day', strtotime($year.'-'.$month.'-01')));
        $lastweekprevmonth = date('Y-m-25', strtotime('last day', strtotime($year.'-'.$month.'-01')));
        $lastdaynextmonth = date('Y-m-06', strtotime('last day of next month', strtotime($year.'-'.$month.'-01')));
        $sql = <<<__SQL
SELECT *
FROM fukuri_stadium
WHERE date <= %s AND date >= %s
AND type = %d
ORDER BY ID, date ASC
__SQL;

        $args = array(
            $lastdaynextmonth,
            $lastweekprevmonth,
            $type
        );

        $prep = $wpdb->prepare($sql, $args);
        $res = $wpdb->get_results($prep);
        return $res;
    }

    /**
     * Get the calendar data
     *
     * @return array Arranged calendar data
     */
    private function getDate($params)
    {
        $dayname  = '';
        $calendar = array();
        $weekdays = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');

        $year    = substr($params['date'], 0, 4);
        $month   = substr($params['date'], -5, 2);
        $numdays = date('t', mktime(0, 0, 0, $month, 1, $year));
        $lastdayprevmonth = date('Y-m-d', strtotime('last day', strtotime($year.'-'.$month.'-01')));

        for ($i=1; $i<=(int)$numdays; $i++) {
            $calendar[] = $year.'/'.$month.'/'.sprintf('%02d', $i);
        }

        $cal = array();
        foreach ($weekdays as $key => $value) {
            foreach ($calendar as $keys => $vals) {
                $dayname = date('D', strtotime($vals));
                if ($value === $dayname) {
                    $cal[$key][] = date('Y-m-d', strtotime($vals));
                }
            }
        }

        $minus1 = date('Y-m-d', strtotime('-1 day', strtotime($lastdayprevmonth)));
        $minus2 = date('Y-m-d', strtotime('-2 day', strtotime($lastdayprevmonth)));
        $minus3 = date('Y-m-d', strtotime('-3 day', strtotime($lastdayprevmonth)));
        $minus4 = date('Y-m-d', strtotime('-4 day', strtotime($lastdayprevmonth)));
        $minus5 = date('Y-m-d', strtotime('-5 day', strtotime($lastdayprevmonth)));

        if ($cal[1][0] === $year.'-'.$month.'-01') {
            array_unshift($cal[0], date('Y-m-d', strtotime($lastdayprevmonth)));
        }  elseif ($cal[2][0] === $year.'-'.$month.'-01') {
            array_unshift($cal[0], $minus1);
            array_unshift($cal[1], date('Y-m-d', strtotime($lastdayprevmonth)));
        } elseif ($cal[3][0] === $year.'-'.$month.'-01') {
            array_unshift($cal[0], $minus2);
            array_unshift($cal[1], $minus1);
            array_unshift($cal[2], date('Y-m-d', strtotime($lastdayprevmonth)));
        } elseif ($cal[4][0] === $year.'-'.$month.'-01') {
            array_unshift($cal[0], $minus3);
            array_unshift($cal[1], $minus2);
            array_unshift($cal[2], $minus1);
            array_unshift($cal[3], date('Y-m-d', strtotime($lastdayprevmonth)));
        } elseif ($cal[5][0] === $year.'-'.$month.'-01') {
            array_unshift($cal[0], $minus4);
            array_unshift($cal[1], $minus3);
            array_unshift($cal[2], $minus2);
            array_unshift($cal[3], $minus1);
            array_unshift($cal[4], date('Y-m-d', strtotime($lastdayprevmonth)));
        } elseif ($cal[6][0] === $year.'-'.$month.'-01') {
            array_unshift($cal[0], $minus5);
            array_unshift($cal[1], $minus4);
            array_unshift($cal[2], $minus3);
            array_unshift($cal[3], $minus2);
            array_unshift($cal[4], $minus1);
            array_unshift($cal[5], date('Y-m-d', strtotime($lastdayprevmonth)));
        }

        $nextmonth = $month === '12' ? (int)$month - 11: ( $month === '01' ? 12 : $month + 1 ) ;
        $nextmonth = sprintf('%02d', $nextmonth);
        $nextyear  = $month === '12' ? (int)$year + 1 : ( $month === '01' ? (int)$year - 1 : $year );
        $nextlastday = date('Y-m-t', strtotime($nextyear.'-'.$nextmonth.'-01'));
        $lastdaythismonth = date('Y-m-t', strtotime($year.'-'.$month.'-01'));

        if (in_array($lastdaythismonth, $cal[0])) {
            array_push($cal[1], $nextyear.'-'.$nextmonth.'-01');
            array_push($cal[2], $nextyear.'-'.$nextmonth.'-02');
            array_push($cal[3], $nextyear.'-'.$nextmonth.'-03');
            array_push($cal[4], $nextyear.'-'.$nextmonth.'-04');
            array_push($cal[5], $nextyear.'-'.$nextmonth.'-05');
            array_push($cal[6], $nextyear.'-'.$nextmonth.'-06');
        } elseif (in_array($lastdaythismonth, $cal[1])) {
            array_push($cal[2], $nextyear.'-'.$nextmonth.'-01');
            array_push($cal[3], $nextyear.'-'.$nextmonth.'-02');
            array_push($cal[4], $nextyear.'-'.$nextmonth.'-03');
            array_push($cal[5], $nextyear.'-'.$nextmonth.'-04');
            array_push($cal[6], $nextyear.'-'.$nextmonth.'-05');
        } elseif (in_array($lastdaythismonth, $cal[2])) {
            array_push($cal[3], $nextyear.'-'.$nextmonth.'-01');
            array_push($cal[4], $nextyear.'-'.$nextmonth.'-02');
            array_push($cal[5], $nextyear.'-'.$nextmonth.'-03');
            array_push($cal[6], $nextyear.'-'.$nextmonth.'-04');
        } elseif (in_array($lastdaythismonth, $cal[3])) {
            array_push($cal[4], $nextyear.'-'.$nextmonth.'-01');
            array_push($cal[5], $nextyear.'-'.$nextmonth.'-02');
            array_push($cal[6], $nextyear.'-'.$nextmonth.'-03');
        } elseif (in_array($lastdaythismonth, $cal[4])) {
            array_push($cal[5], $nextyear.'-'.$nextmonth.'-01');
            array_push($cal[6], $nextyear.'-'.$nextmonth.'-02');
        } elseif (in_array($lastdaythismonth, $cal[5])) {
            array_push($cal[6], $nextyear.'-'.$nextmonth.'-01');
        }

        return $cal;
    }

    /**
     * Get the calendar data
     * It is combined with the calendar and data.
     * This contains if each day has an event or not.
     *
     * @param  array $data         This holds the data from the database.
     * @param  array $date         This holds the date.
     * @return array $calendarData Combined data.
     */
    private function getCalendarData($data, $date)
    {
        $calendarData = array();
        // loop through the date data
        foreach ($date as $key => $timestampRecords) {
            // loop throught the timestamp records
            foreach ($timestampRecords as $timestampKeys => $timestamp) {
                // check first if there is no timestamp data
                if (empty($timestamp)) {
                    $calendarData[$key][$timestampKeys][] = array();
                    continue;
                }

                // check if there's no data from the database
                if (empty($data)) {
                    $calendarData[$key][$timestampKeys][] = date('Y-m-d', strtotime($timestamp));
                    continue;
                }

                // now start mapping the data based from the timestamp
                foreach ($data as $dataKey => $dataVal) {
                    $day = date('Y-m-d', strtotime($dataVal->date));

                    // if they do not match then just put it as a date.
                    if ($timestamp !== $day) {
                        $calendarData[$key][$timestampKeys][] = date('Y-m-d', strtotime($timestamp));
                        continue;
                    }

                    // now if values match then put the corresponding data on based from the timestamp.
                    $infos = $dataVal->info;
                    $calendarData[$key][$timestampKeys][(int)$infos] = $dataVal;
                }
            }
        }

        return $calendarData;
    }

    /**
     * Sort the data
     *
     * @param  array $calendarData unsorted data
     * @return array $sortedData   sorted data
     */
    private function sortedCalendar($calendarData)
    {
        $sortedData = $sortedDataTmp = $list = array();

        // this list the order of each days
        for ($i=1; $i < 5; $i++) {
            $list[$i] = array();
        }

        // preparing the data to be sorted
        foreach ($calendarData as $key => $records) {
            foreach ($records as $recordKeys => $record) {
                $sort = $sortedDataTmp[$key][$recordKeys] = array_unique($record, SORT_REGULAR);
                if(count($sortedDataTmp[$key][$recordKeys]) > 1) {
                    $sort = array_filter(array_map(function($param) {
                        return is_string($param) ? '': $param;
                    }, $sort));
                    $data = array();
                    foreach($sort as $k => $v) {
                        $data[$v->info] = $v;
                    }
                    $sortedDataTmp[$key][$recordKeys] = $data;
                }
            }
        }

        // sorting the data
        foreach ($sortedDataTmp as $key => $records) {
            foreach ($records as $recordKeys => $record) {
                $sortedData[$key][$recordKeys] = $record;
                if (is_array($record) && !empty($record)) {
                    $unmatched = array_diff_key($list, $record);
                    $combined = $record + $unmatched;
                    ksort($combined);

                    $sortedData[$key][$recordKeys] = array_values($combined);
                }
            }
        }

        return $sortedData;
    }

    /**
     * Insert, and update for the calendar
     *
     * @param  array $param data input
     * @return boolean false if there's error in inserting/updating, otherwise false.
     */
    private function crud($param)
    {
        global $wpdb;
        $timezone = new DateTime(null, new DateTimeZone('Asia/Tokyo'));
        $dtcreate = $timezone->format('Y-m-d H:i:s');

        $date = str_replace('年', '/', $param['date']);
        $date = str_replace('月', '/', $date);
        $type = $param['place'];

        $updatePrepParam = $updateDateParam = $insertPrepParam = $updateDate = $update = $insert = array();
        foreach ($param['sel'] as $key => $value) {
            if (strlen($value[1]) > 0) {
                $info   = $value[0];
                $status = $value[1];
                $checkSql = <<<__SQL
SELECT count(*) as cnt
FROM fukuri_stadium
WHERE type = %d
AND date = %s
AND info = %d
__SQL;
                $checkParam = array($type, $date, $info);
                $checkPrep = $wpdb->prepare($checkSql, $checkParam);
                $checkRes = $wpdb->get_results($checkPrep);
                $checkVal = $checkRes[0]->cnt;

                if ((int)$checkVal > 0) {
                    $update[] = 'WHEN type = %d AND date = %s AND info = %d THEN %d';
                    $updateDate[] = 'WHEN type = %d AND date = %s AND info = %d THEN %s';
                    $updatePrepParam[] = array($type, $date, $info, $status);
                    $updateDateParam[] = array($type, $date, $info, $dtcreate);
                } else {
                    $insertPrepParam[] = array($param['place'], $date, $info, $status, $dtcreate);
                    $insert[] = '(%d, %s, %d, %d, %s)';
                }
            } else {
                $info   = $value[0];
                $checkSql = <<<__SQL
SELECT count(*) as cnt
FROM fukuri_stadium
WHERE type = %d
AND date = %s
AND info = %d
__SQL;
                $checkParam = array($type, $date, $info);
                $checkPrep  = $wpdb->prepare($checkSql, $checkParam);
                $checkRes   = $wpdb->get_results($checkPrep);
                $checkVal   = $checkRes[0]->cnt;

                if ((int)$checkVal > 0) {
                    $info   = $value[0];
                    $status = empty($value[1]) ? '""' : $value[1];

                    $update[]          = 'WHEN type = %d AND date = %s AND info = %d THEN %d';
                    $updateDate[]      = 'WHEN type = %d AND date = %s AND info = %d THEN %s';
                    $updatePrepParam[] = array($type, $date, $info, $status);
                    $updateDateParam[] = array($type, $date, $info, $dtcreate);
                }
            }
        }

        $updateVal     = implode("\r\n", $update);
        $updateDateVal = implode("\r\n", $updateDate);
        $insertPar = $updatePar = $updateDatePar = array();

        if (!empty($updateVal)) {
            $updatePar = call_user_func_array('array_merge', $updatePrepParam);
        }

        if (!empty($updateDateVal)) {
            $updateDatePar = call_user_func_array('array_merge', $updateDateParam);
        }

        $insertVal = implode(",\r\n", $insert);
        if (!empty($insertVal)) {
            $insertPar = call_user_func_array('array_merge', $insertPrepParam);
        }

        $updateSql = <<<__SQL
UPDATE fukuri_stadium
    SET status = CASE
        {$updateVal}
        ELSE status
    END,
    dtupdate = CASE
        {$updateDateVal}
        ELSE dtupdate
    END
__SQL;

        $insertSql = <<<__SQL
INSERT INTO fukuri_stadium (type, date, info, status, dtcreate)
VALUES {$insertVal}
__SQL;

        $insertRes = $updateRes = '';

        $wpdb->query('START TRANSACTION');
        if (!empty($insertVal) && !empty($updateVal)) {
            $insertPrepare = $wpdb->prepare($insertSql, $insertPar);
            $insertRes = $wpdb->query($insertPrepare);

            $merge = array();
            $merge[] = $updatePar;
            $merge[] = $updateDatePar;

            $updatePrepare = $wpdb->prepare($updateSql, call_user_func_array('array_merge', $merge));
            $updateRes = $wpdb->query($updatePrepare);
        } elseif (!empty($insertVal)) {
            $insertPrepare = $wpdb->prepare($insertSql, $insertPar);
            $insertRes = $wpdb->query($insertPrepare);
        } else {
            $merge = array();
            $merge[] = $updatePar;
            $merge[] = $updateDatePar;

            $updatePrepare = $wpdb->prepare($updateSql, call_user_func_array('array_merge', $merge));

            $updateRes = $wpdb->query($updatePrepare);
        }

        $wpdb->query('COMMIT');

        $res = $insertRes = $updateRes = true;
        if ($insertRes === false || $updateRes === false) {
            $wpdb->query('ROLLBACK');
            return true;
        } else {
            return true;
        }
    }
}
