<?php
/**
 * Calendar List
 *
 * This contains two calendars.
 * The 拓北野球場and川下グラウンド.
 * This calendars contain events.
 *
 * @param  int $type 1 is for 拓北野球場 and 2 is 川下グラウンド.
 */
function custom_stadium( $type ) {
    $year             = date( 'Y' );
    $month            = date( 'm' );
    $prevmonth        = date( 'm', strtotime( '-1 month', strtotime( $year.'-'.$month.'-01' ) ) );
    $prevyear         = $prevmonth === '12' ? date( 'Y', strtotime( '-1 year', strtotime( $year.'-'.$month.'-01' ) ) ) : $year;
    $lastdayprevmonth = date( 'Y-m-d', strtotime( 'last day of previous month', strtotime( $year.'-'.$month.'-01' ) ) );
    $lastweekprevmonth = date( 'Y-m-25', strtotime( 'last day of previous month', strtotime( $year.'-'.$month.'-01' ) ) );
    $lastdaynextmonth = date( 'Y-m-06', strtotime( 'last day of next month', strtotime( $year.'-'.$month.'-01' ) ) );
    $before           = date( "Y年m月", mktime( 0, 0, 0, substr( $month, -5, 2 )-1, 1, substr( $year, 0, 4 ) ) );
    $next             = date( "Y年m月", mktime( 0, 0, 0, substr( $month, -5, 2 )+1, 1, substr( $year, 0, 4 ) ) );

    // get the latest time updated of the calendar.
    $updatedDay    = custom_get_latest_time($type);

    // get the events of both calendar
    $res           = get_stadium_data($lastdaynextmonth, $lastweekprevmonth, $type);

    // create the montly calendar
    $cal           = custom_monthly_calendar($year, $month, $lastdayprevmonth);

    // combine the monthly calendar and the events data
    $calendar_data = get_calendar_data($res, $cal);

    // sorted data
    $arr4          = sorted_calendar_data($calendar_data);
?>
    <div class="block calendar-display calendar<?php echo $type;?>">
      <div class="blockpad">
        <?php if ( $updatedDay !== false ) :?>
        <input type="hidden" name="edit_date<?php echo $type;?>" value="<?php echo $updatedDay['date'].'（'. $updatedDay['japaneseday'].'）'.$updatedDay['hour'];?>">
        <p class="edit_date">最終更新日時：<?php echo $updatedDay['date'].'（'. $updatedDay['japaneseday'].'）'.$updatedDay['hour'];?></p>
        <?php endif;?>
        <div class="calendarnav">
          <p><?php the_title();?>予約状況カレンダー</p>
          <ul>
            <input type="hidden" name="before<?php echo $type;?>" value="<?php echo $before; ?>">
            <input type="hidden" name="next<?php echo $type;?>" value="<?php echo $next; ?>">
            <li><?php echo date('Y年m月');?></li>
            <li class="next"><a id="<?php echo $type;?>" href="javascript:void(0)"><img src="common/images/icons/icon_arrow_week_next.png" alt="next"></a></li>
            <li class="next"><a id="<?php echo $type;?>" href="javascript:void(0)">次の月へ</a></li>
          </ul>
        </div>
        <table class="table_week mab10">
          <tbody>
            <tr>
              <th></th>
              <th>月</th>
              <th>火</th>
              <th>水</th>
              <th>木</th>
              <th>金</th>
              <th>土</th>
              <th>日</th>
            </tr>
            <?php
            for( $j=0; $j<6; $j++ ):
              $sams = '<tr class="day"><td class="time">06 ～ 09<br>09 ～ 12<br>12 ～ 15<br>15 ～ 18</td>';
              foreach ( $arr4 as $key => $value ) :
                $day = '';
                if ( isset( $arr4[$key][$j][0] ) ) {
                  $day = $arr4[$key][$j][0];
                } else {
                  for ($i=1; $i < 5; $i++) {
                    if ( isset($arr4[$key][$j][$i] ) && !empty( $arr4[$key][$j][$i] ) ) {
                      $day = isset( $arr4[$key][$j][$i]->date ) ? $arr4[$key][$j][$i]->date : '';
                      $day = date( 'Y-m-d', strtotime( $day ) );
                    }
                  }
                }
                if ( empty( $day ) ) {
                  $sams .= '<td class="prevmonth"><span class="daynum" style="text-align:left"></span>';
                } elseif ( date('Y-m', strtotime( $day ) ) !== $year.'-'.$month ) {
                  $sams .= '<td class="prevmonth"><span class="daynum" style="text-align:left">'.date( 'd', strtotime( $day ) ).'</span>';
                } elseif ( strlen( $day ) > 0) {
                  $sams .= '<td><span class="daynum" style="text-align:left">'.date( 'd', strtotime( $day ) ).'</span>';
                } else {
                  $sams .= '<td class="prevmonth"><span class="daynum" style="text-align:left"></span>';
                }

                // this is for the events
                if ( empty( $arr4[$key][$j] ) ) {
                  $sams .= '';
                } else {
                  foreach ( array_values( $arr4[$key][$j] ) as $k => $v ) {
                    if ($k !== 4) {
                      if (!empty( $v->status ) ) {
                        if ( (int)$v->status === 1 ) {
                          $sams .= '<span class="status status1"></span>';
                        } elseif ( (int)$v->status === 2 ) {
                          $sams .= '<span class="status status2"></span>';
                        } elseif ( (int)$v->status === 0 ) {
                          $sams .= '<span class="status status2"></span>';
                        }
                      } else {
                        $sams .= '<span class="status status2"></span>';
                      }
                    }
                  }
                }

                $sams .= '</td>';
                endforeach;
              $sams .= '</tr>';
              $html = preg_replace( '/<tr class="day"><td class="time">06 ～ 09<br>09 ～ 12<br>12 ～ 15<br>15 ～ 18<\/td><td class="prevmonth"><span class="daynum" style="text-align:left"><\/span><\/td><td class="prevmonth"><span class="daynum" style="text-align:left"><\/span><\/td><td class="prevmonth"><span class="daynum" style="text-align:left"><\/span><\/td><td class="prevmonth"><span class="daynum" style="text-align:left"><\/span><\/td><td class="prevmonth"><span class="daynum" style="text-align:left"><\/span><\/td><td class="prevmonth"><span class="daynum" style="text-align:left"><\/span><\/td><td class="prevmonth"><span class="daynum" style="text-align:left"><\/span><\/td><\/tr>/', '', $sams );
              echo $html;
              endfor;
              ?>
          </tbody>
        </table>
        <p class="mab10"><span class="bold">＜カレンダーの見方＞</span><br>
          日付枠は上から「午前6時から午前9時」「午前9時から午後12時」「午後12時から午後3時」「午後3時から午後6時」の時間帯の予約状況を表します。</p>
        <ul class="legend">
          <li><span class="status status1">
            </span> 予約可</li>
          <li><span class="status status2">
            </span> 予約不可</li>
        </ul>
      </div>
    </div>
<?php
}

/**
 * Get the latest time
 *
 * This function will get the latest time
 * the calendar was update.
 *
 * @param  int $type          1 is for 拓北野球場 and 2 is 川下グラウンド.
 * @return string $updatedDay Exact time the calendar was updated. With Japanese format.
 */
function custom_get_latest_time($type) {
    global $wpdb;

    // This is sql is for getting the time of the when the calendar was last updated.
    $datesql = <<<__SQL
SELECT GREATEST(
 COALESCE(dtcreate, 0),
 COALESCE(dtupdate, 0)) as datemax
FROM fukuri_stadium
WHERE
  type = %d
ORDER BY datemax DESC LIMIT 1;
__SQL;

    $prep = $wpdb->prepare($datesql, $type);
    $dateres = $wpdb->get_results( $prep );
    if ( empty( $dateres ) ) {
        return false;
    }

    $updateDate = $dateres[0]->datemax;
    $hour       = date( 'H:i', strtotime( $updateDate ) );
    $formatDate = date( 'Y年m月d日', strtotime( $updateDate) );
    $formatDay  = date( 'D', strtotime( $updateDate) );
    $updatedDay = '';
    switch ( $formatDay ) {
    case 'Mon':
        $updatedDay = '月';
        break;
    case 'Tue':
        $updatedDay = '火';
        break;
    case 'Wed':
        $updatedDay = '水';
        break;
    case 'Thu':
        $updatedDay = '木';
        break;
    case 'Fri':
        $updatedDay = '金';
        break;
    case 'Sat':
        $updatedDay = '土';
        break;
    case 'Sun':
        $updatedDay = '日';
        break;
    default:
        # code...
        break;
    }

    return array( 'japaneseday' => $updatedDay, 'date' => $formatDate, 'hour' => $hour );
}

/**
 * Stadium Data
 *
 * This function contains the query for
 * getting all the events for the two
 * calendar
 *
 * @param  int $type 1 is for 拓北野球場 and 2 is 川下グラウンド.
 * @param  string $nextmonth Y-m-d for nextmonth date
 * @param  string $prevmonth Y-m-d for lastmonth date
 * @param  string $type      type of calendar, 1:野球場, 2: 川下グラウンド
 * @return array event data
 */
function get_stadium_data( $nextmonth, $prevmonth, $type ) {
    global $wpdb;

    // This is sql is for getting the events of the calendar.
    $sql = <<<__SQL
SELECT *
FROM fukuri_stadium
WHERE date <= %s AND date >= %s
AND type = %d
ORDER BY ID, date ASC
__SQL;

    $param = array( $nextmonth, $prevmonth, $type );
    $prep = $wpdb->prepare($sql, $param);
    return $wpdb->get_results( $prep );
}

/**
 * Create the monthy calendar
 *
 * This calendar will show first
 * the today's month.
 *
 * @param string $year  This year
 * @param string $month This month
 * @param  string $lastdayprevmonth the last day of last month
 * @return array $cal   Calendar with this year and month
 */
function custom_monthly_calendar($year, $month, $lastdayprevmonth) {
    $dayname  = '';
    $calendar = array();
    $weekdays = array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' );

    // get the number of days each month.
    $numdays = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );

    // loop through it and change it to two digits.
    for ( $i=1; $i<=(int)$numdays; $i++ ) {
        $calendar[] = $year.'/'.$month.'/'.sprintf( '%02d', $i );
    }

    // Now create the monthly calendar. It has the name of the weeks with its corresponding days.
    // This list is like by day. What days does monday or tuesday have. It goes by that.
    $cal = array();
    foreach ( $weekdays as $key => $value ) {
        foreach ( $calendar as $keys => $vals ) {
            $dayname = date( 'D', strtotime( $vals ) );
            if ( $value === $dayname ) {
                $cal[$key][] = date( 'Y-m-d', strtotime( $vals ) );
            }
        }
    }

    // add array at the beginning of the week since it's not like 01 day starts every monday.
    $minus1 = date( 'Y-m-d', strtotime( '-1 day', strtotime( $lastdayprevmonth ) ) );
    $minus2 = date( 'Y-m-d', strtotime( '-2 day', strtotime( $lastdayprevmonth ) ) );
    $minus3 = date( 'Y-m-d', strtotime( '-3 day', strtotime( $lastdayprevmonth ) ) );
    $minus4 = date( 'Y-m-d', strtotime( '-4 day', strtotime( $lastdayprevmonth ) ) );
    $minus5 = date( 'Y-m-d', strtotime( '-5 day', strtotime( $lastdayprevmonth ) ) );

    if ( $cal[1][0] === $year.'-'.$month.'-01' ) {
        array_unshift( $cal[0], date( 'Y-m-d', strtotime( $lastdayprevmonth ) ) );
    }  elseif ( $cal[2][0] === $year.'-'.$month.'-01' ) {
        array_unshift( $cal[0], $minus1 );
        array_unshift( $cal[1], date( 'Y-m-d', strtotime( $lastdayprevmonth ) ) );
    } elseif ( $cal[3][0] === $year.'-'.$month.'-01' ) {
        array_unshift( $cal[0], $minus2 );
        array_unshift( $cal[1], $minus1 );
        array_unshift( $cal[2], date( 'Y-m-d', strtotime( $lastdayprevmonth ) ) );
    } elseif ( $cal[4][0] === $year.'-'.$month.'-01' ) {
        array_unshift( $cal[0], $minus3 );
        array_unshift( $cal[1], $minus2 );
        array_unshift( $cal[2], $minus1 );
        array_unshift( $cal[3], date( 'Y-m-d', strtotime( $lastdayprevmonth ) ) );
    } elseif ( $cal[5][0] === $year.'-'.$month.'-01' ) {
        array_unshift( $cal[0], $minus4 );
        array_unshift( $cal[1], $minus3 );
        array_unshift( $cal[2], $minus2 );
        array_unshift( $cal[3], $minus1 );
        array_unshift( $cal[4], date( 'Y-m-d', strtotime( $lastdayprevmonth ) ) );
    } elseif ( $cal[6][0] === $year.'-'.$month.'-01' ) {
        array_unshift( $cal[0], $minus5 );
        array_unshift( $cal[1], $minus4 );
        array_unshift( $cal[2], $minus3 );
        array_unshift( $cal[3], $minus2 );
        array_unshift( $cal[4], $minus1 );
        array_unshift( $cal[5], date( 'Y-m-d', strtotime( $lastdayprevmonth ) ) );
    }

    $nextmonth        = $month === '12' ? (int)$month - 11: ( $month === '01' ? 12 : $month + 1 ) ;
    $nextmonth = sprintf('%02d', $nextmonth);
    $nextyear         = $month === '12' ? (int)$year + 1 : ( $month === '01' ? (int)$year - 1 : $year );
    $nextlastday      = date('Y-m-t', strtotime($nextyear.'-'.$nextmonth.'-01'));
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
 * This will now match the date with events.
 *
 * @param  array $data         Contains data from db.
 * @param  array $date         Calendar list without data
 * @return array $calendarData Calendar with date
 */
function get_calendar_data( $data, $date )
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
 * Combine the monthly calendar and the data.
 *
 * This will now contain the date with
 * it corresponding events.
 *
 * @param  array $calendar_data Sorted calendar data
 * @return array $sortedData    Unsorted calendar data
 */
function sorted_calendar_data($calendar_data) {
    $sorted_data = $sorted_data_tmp = $list = array();

    // this list the order of each days
    for ( $i=1; $i < 5; $i++ ) {
        $list[$i] = array();
    }

    // sort the data of the events on each day of the month.
    foreach ( $calendar_data as $key => $records ) {
        foreach ( $records as $record_keys => $record ) {
            $sort = $sorted_data_tmp[$key][$record_keys] = array_unique( $record, SORT_REGULAR );
            if( count( $sort ) > 1 ) {
                $sort = array_filter( array_map ( function( $par ) {
                    return is_string( $par ) ? '': $par;
                }, $sort) );
                $data = array();
                foreach( $sort as $value ) {
                    $data[$value->info] = $value;
                }

                $sorted_data_tmp[$key][$record_keys] = $data;
            }

        }
    }

    // eliminate the unnecessary empty arrays on each day
    $arr2 = array();
    foreach ( $sorted_data_tmp as $key => $records ) {
        foreach ( $records as $keys => $record ) {
            if ( is_array( $record ) && !empty( $record ) ) {
                $unmatched = array_diff_key( $list, $record );
                $combined = $record + $unmatched;
                ksort($combined);

                $arr2[$key][$keys] = $combined;
            } else {
                $arr2[$key][$keys] = array_values($record);
            }
        }
    }

    return $arr2;
}
