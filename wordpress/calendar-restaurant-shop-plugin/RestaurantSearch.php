<?php

require_once 'RestaurantWeekData.php';

/**
 * This will get the data
 * of the 食堂売店 calendar.
 * It will be used for the admin page,
 * next and before button, and for the
 * front page.
 */
class RestaurantSearch
{
    /**
     * Function that will be called for searching.
     *
     * @param  array $param
     * @return array|json $res
     */
    public function mainSearch($param)
    {
        $nav = array();
        if (isset($param['nav_type'])) {
            switch ($param['nav_type']) {
                case 'before':
                    if (isset($param['monday'])) {
                        $nav['monday'] = $param['monday'] = date('Y-m-d', strtotime('previous Monday', strtotime($param['monday'])));
                    }

                    if (isset($param['sunday'])) {
                        $nav['sunday'] = $param['sunday'] = date('Y-m-d', strtotime('previous Sunday', strtotime($param['sunday'])));
                    }
                    break;

                case 'next':
                    if (isset($param['monday'])) {
                        $nav['monday'] = $param['monday'] = date('Y-m-d', strtotime('next Monday', strtotime($param['monday'])));
                    }

                    if (isset($param['sunday'])) {
                        $nav['sunday'] = $param['sunday'] = date('Y-m-d', strtotime('next Sunday', strtotime($param['sunday'])));
                    }
                    break;
                default:
                    # code...
                    break;
            }
        }

        if (isset($param['display_type']) && $param['display_type'] === 'add') {
            $param['nav_type'] = 'add';
            $weekdays = RestaurantWeekData::get_weekdays($param['datepicker']);
            $nav['monday'] = $param['monday'] = $weekdays[0];
            $nav['sunday'] = $param['sunday'] = $weekdays[6];
        }

        $data = $this->getCurrentWeekData($this->getDate($this->getData($param)), $nav);
        $res = array(
            'status' => 'ok',
            'data'   => array(
                'site_url' => $param['site_url'],
                'home_url' => home_url(),
                'dir'      => $param['dir'],
                'term_id'  => $param['term_id'],
                'term_name_admin' => $param['term'],
                'term'     => strpos($param['term'], '売店') === false ? '日替り' : 'お弁当',
                'res'      => $data,
            )
        );

        if (isset($param['display_type']) && $param['display_type'] === 'add') {
            return $res;
        } elseif (isset($param['display_type']) && $param['display_type'] === 'front') {
            return $data;
        } else {
            return $res;
        }
    }

    /**
     * Function that will get the
     * weekday for japanese
     *
     * @param  string $day Eng version
     * @return string $day Jap version
     */
    private function getWeekday($day)
    {
        // $day = date('D', strtotime($weekday));

        if ($day === 'Mon') {
            $day = '月';
        } elseif ($day === 'Tue') {
            $day = '火';
        } elseif ($day === 'Wed') {
            $day = '水';
        } elseif ($day === 'Thu') {
            $day = '木';
        } elseif ($day === 'Fri') {
            $day = '金';
        } elseif ($day === 'Sat') {
            $day = '土';
        } elseif ($day === 'Sun') {
            $day = '日';
        }

        return $day;
    }

    /**
     * Get all data from term id
     *
     * @param  array $param
     * @return array $res
     */
    private function getData($param)
    {
        global $wpdb;
        $args = array($param['term_id']);

        if (isset($param['nav_type'])) {
            $args[] = $param['monday'];
            $args[] = $param['sunday'];
            $cond   = 'AND DATE(res.weekday) >= %s';
            $cond   .= ' AND DATE(res.weekday) <= %s';
        } else {
            $args[] = RestaurantWeekData::get_weeknumber();
            $cond = 'AND WEEKOFYEAR(res.weekday) = %d';
        }

        $sql = <<<__SQL
SELECT
    *
FROM
    fukuri_restaurant_shop res
LEFT JOIN
    fukuri_restaurant_shop_meta res_meta
ON res.ID = res_meta.post_id
WHERE
    res.term_id = %d
$cond
ORDER BY res_meta.post_id, res_meta.menu_order
__SQL;
        $prepare = $wpdb->prepare($sql, $args);
        $res = $wpdb->get_results($prepare);

        return $res;
    }

    /**
     * Get the date with the data
     *
     * @param  array $data
     * @return array $res
     */
    private function getDate($data)
    {
        $res = $calendar = array();
        $sunday = date('Y-m-d', strtotime('sunday last week'));
        $saturday = date('Y-m-d', strtotime('saturday this week'));
        $today = date('d');

        $date = $weekdays = $years = $year = $month = $months = array();
        $current_year = date('Y', strtotime('+2 years')) + 1;
        for ($i = 2017; $i < $current_year; $i++) {
            $years[] = $i;
        }

        for ($i = 1; $i < 13; $i++) {
            $months[] = sprintf("%02d", $i);
        }

        for ($i = 1; $i < 8; $i++) {
            $weekdays[] = $i;
        }

        foreach ($years as $key => $year) {
            foreach ($months as $key => $month) {
                $date[$year][$month] = array(
                    'year'     => $year,
                    'month'    => $month,
                    'weekdays' => $weekdays,
                    'days'     => date('t', mktime(0, 0, 0, $month, 1, $year))
                );
            }
        }

        foreach ($date as $year => $value) {
            foreach ($value as $month => $day) {
                for ($i = 1; $i <= $day['days']; $i++) {
                    $calendar[] = array(
                        'year'  => $year,
                        'month' => $month,
                        'days'  => sprintf("%02d", $i)
                    );
                }
            }
        }

        $sam = array();
        foreach ($calendar as $calendarKey => $calendarVal) {
            $calendarWeekday = $calendarVal['year'] . '-' . $calendarVal['month'] . '-' . $calendarVal['days'];
            if (empty($data)) {
                $res[$calendarWeekday][] = array();
            } else {
                foreach ($data as $dataKey => $dataValue) {
                    if ($calendarWeekday === $dataValue->weekday) {
                        $res[$calendarWeekday]['data'][] = array(
                            'term_id'  => $dataValue->term_id,
                            'type'     => $dataValue->info,
                            'weekday'  => $dataValue->weekday,
                            'label'    => $dataValue->label,
                            'menu'     => $dataValue->menu,
                            'price'    => $dataValue->price,
                            'dtcreate' => $dataValue->dtcreate,
                            'order'    => $dataValue->menu_order,
                        );
                    } else {
                        $res[$calendarWeekday][] = array();
                    }
                }
            }
        }

        return $res;
    }

    /**
     * Get the day's title
     *
     * This will get each day's title.
     * This is different from the menu, special, and price.
     * This data will explain the today's menu.
     * This is combined calendar and data from the database already.
     *
     * @param  array $calendarData Data from the database based on this week's date.
     * @param  array $monday       This data contains the monday post parameter.
     * @param  array $sunday       This data contains the sunday post parameter.
     * @return array $dayTitle     Combined data and date of this week. Matched data already.
     */
    private function getTitleMenu($calendarData, $monday, $sunday)
    {
        $dayTitle = $dayTitleTemp = array();

        // combining of calendar and data
        foreach ($calendarData as $timestamp => $records) {

            // this condition will get only the data from current week.
            if (strtotime($monday) > strtotime($timestamp) || strtotime($sunday) < strtotime($timestamp)) {
                continue;
            }

            // this loop will start matching the data to each day.
            foreach ($records as $record) {
                // if records is empty the put an empty string on it to match the string that has data.
                if (empty($record)) {
                    $dayTitleTemp[$timestamp][] = '';
                    continue;
                }

                // if there's data then put the data on the matched day (mapping).
                foreach ($record as $value) {
                    $dayTitleTemp[$timestamp][] = $value['type'];
                }

                $dayTitleTemp[$timestamp][] = '';
            }
        }

        // remove unnecessary arrays from the calendar
        foreach ($dayTitleTemp as $key => $val) {
            $dayTitle[] = array_values(array_filter($val));
        }

        return $dayTitle;
    }

    /**
     * Menu data with calendar data.
     *
     * This contains the days with its
     * corresponding menu. Each day can
     * have 12 menu. So this list contains
     * 12 menu or less.
     *
     * @param  array $calendarData  Data from the database based on this week's date.
     * @param  array $monday        This data contains the monday post parameter.
     * @param  array $sunday        This data contains the sunday post parameter.
     * @return array $menuData      Combined calendar data with menu data.
     */
    private function getMenuData($calendarData, $monday, $sunday)
    {
        $menuData = $menuDataTemp = array();

        // combining of calendar and data
        foreach ($calendarData as $timestamp => $records) {
            // this condition will get only the data from current week.
            if (strtotime($monday) > strtotime($timestamp) || strtotime($sunday) < strtotime($timestamp)) {
                continue;
            }

            // this loop will start matching the data to each day.
            foreach ($records as $record) {
                // get the name day of the week
                $nameday = date('D', strtotime($timestamp));

                // get the japanese day from the name day.
                $weekday = $this->getWeekday($nameday);

                // if records is empty the put an empty array on it to match the array that has data.
                if (empty($record)) {
                    $menuDataTemp[date('m月d日', strtotime($timestamp)) . '(' . $weekday . ')' . '|' . $timestamp . '|' . $nameday][] = array();
                    continue;
                }

                // this loop will now get the data and put into a new array
                foreach ($record as $value) {
                    $menuDataTemp[date('m月d日', strtotime($timestamp)) . '(' . $weekday . ')' . '|' . $timestamp . '|' . $nameday][] = array(
                        'data' => array(
                            'term_id'  => $value['term_id'],
                            'type'     => $value['type'],
                            'weekday'  => $value['weekday'],
                            'label'    => $value['label'],
                            'menu'     => $value['menu'],
                            'price'    => $value['price'],
                            'dtcreate' => $value['dtcreate'],
                            'order'    => $value['order'],
                        )
                    );
                }

                $menuDataTemp[date('m月d日', strtotime($timestamp)) . '(' . $weekday . ')' . '|' . $timestamp . '|' . $nameday][] = array();
            }
        }

        // remove unnecessary arrays from the calendar
        foreach ($menuDataTemp as $key => &$day) {
            foreach ($day as $dayTermid => $dayMenu) {
                // if the day like Monday has no menu then delete it.
                if (empty($dayMenu)) {
                    unset($day[$dayTermid]);
                    continue;
                }
            }

            // reorder the keys since there was unsetting of array.
            $menuData[$key] = array_values($day);
        }

        return $menuData;
    }

    /**
     * Sort the menu according to the menu order from the database.
     *
     * @param  array $menuData unsorted data
     * @return array $sortData sorted data
     */
    private function getSortedMenu($menuData)
    {
        $sortData = $sortDataTemp = $menuNumber = array();

        // this loop will reorder the data by its order value from database.
        foreach ($menuData as $key => $menu) {
            // if menu is empty then put empty array on it.
            if (empty($menu)) {
                $sortDataTemp[$key] = array();
                continue;
            }

            // start the reordering of the data
            foreach ($menu as $record) {
                if (empty($record['data']['order'])) {
                    $sortDataTemp[$key][] = array();
                    continue;
                }

                $sortDataTemp[$key][$record['data']['order']] = $record;
            }
        }

        // this will get the order list
        for ($i = 1; $i < 13; $i++) {
            $menuNumber[$i] = array(
                'data' => array(
                    'label' => '',
                    'menu' => '',
                    'price' => '',
                )
            );
        }

        // this will now match the order list from the data from the db.
        foreach ($sortDataTemp as $key => $menuRecord) {
            $unmatched = array_diff_key($menuNumber, $menuRecord);
            $combined = $menuRecord + $unmatched;
            ksort($combined);

            $sortData[$key] = array_filter($combined);
        }

        return $sortData;
    }


    /**
     * Only display for the current week
     *
     * @param  array $data data from the database
     * @param  array $nav  weekly dates
     * @return array       this contains the calendar with its corresponding data.
     */
    private function getCurrentWeekData($data, $nav)
    {
        // get the weekdays date
        $weekdays = RestaurantWeekData::get_weekdays();

        // get monday and sunday for mapping the data.
        $monday = isset($nav['monday']) ? $nav['monday'] : $weekdays[0];
        $sunday = isset($nav['sunday']) ? $nav['sunday'] : $weekdays[6];

        // get the day data. This is the title of the day.
        $titleMenu  = $this->getTitleMenu($data, $monday, $sunday);

        // get the menu, price, special data combined with the calendar.
        $menu       = $this->getMenuData($data, $monday, $sunday);

        // sorted menu data
        $sortedMenu = $this->getSortedMenu($menu);

        return array(
            'menu'       => $sortedMenu,
            'title'      => $titleMenu,
            'titleCount' => count(array_filter($titleMenu))
        );
    }
}
