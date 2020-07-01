<?php

require_once 'RestaurantWeekData.php';

/**
 * Contains the list of categories.
 *
 * It only contains the parent category.
 * The title category. It doesn't have
 * the child category. The order is
 * based from acf field order.
 *
 * @return array $tabres Results of custom category data.
 */
function custom_tabres() {
    global $wpdb;

    $sql = <<<__SQL
SELECT
  terms.term_id
  ,terms.name
  ,tax.taxonomy
  ,tax.parent
FROM
  fukuri_terms terms
INNER JOIN
  fukuri_term_taxonomy tax
    ON terms.term_id = tax.term_id
INNER JOIN
  fukuri_options opt
    ON CONCAT('restaurant_cat_', terms.term_id, '_sort_by') = opt.option_name
WHERE
  tax.taxonomy = 'restaurant_cat' AND tax.parent = 0
GROUP BY terms.term_id
ORDER BY CAST(opt.option_value as UNSIGNED), terms.term_id ASC;
__SQL;

    $tabres = $wpdb->get_results($sql);

    return $tabres;
}

/**
 * Japanese week.
 *
 * The english weekname is translated into
 * Japanese week name.
 *
 * @param  string $day English week name
 * @return string $day Japanese week name
 */
function get_japanese_day( $day ) {
    if ( $day === 'Mon' ) {
        $day = '月';
    } elseif ( $day === 'Tue' ) {
        $day = '火';
    } elseif ( $day === 'Wed' ) {
        $day = '水';
    } elseif ( $day === 'Thu' ) {
        $day = '木';
    } elseif ( $day === 'Fri' ) {
        $day = '金';
    } elseif ( $day === 'Sat' ) {
        $day = '土';
    } elseif ( $day === 'Sun' ) {
        $day = '日';
    }

    return $day;
}

/**
 * Get the calendar data from
 * the database.
 *
 * Get the menu, price, and today's special.
 * Get only from this week.
 *
 * @param  int $val term id
 * @return array All data from this week
 */
function get_calendar_data($val) {
    global $wpdb;
    $weeknum = RestaurantWeekData::get_weeknumber();

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
    AND WEEKOFYEAR(weekday) = %d
ORDER BY res_meta.post_id, res_meta.menu_order
__SQL;

    if ( is_int( $val ) ) {
        $id = $val;
    } else {
        return array();
    }

    $prepare = $wpdb->prepare($sql, $id, $weeknum);

    return $wpdb->get_results( $prepare );
}

/**
 * Get the day's title
 *
 * This will get each day's title.
 * This is different from the menu, special, and price.
 * This data will explain the today's menu.
 * This is combined calendar and data from the database already.
 *
 * @param  array $weekdays      Date of this week
 * @param  array $calendar_data Data from the database based on this week's date.
 * @return array                Combined data and date of this week. Matched data already.
 */
function get_day_title($weekdays, $calendar_data) {
    $day_title = $day_title_temp = array();

    // combining of calendar and data
    foreach ( $weekdays as $day ) {
        // check first if there's no data from the database
        if ( empty( $calendar_data ) ) {
            $day_title_temp[$day][] = array();
            continue;
        }

        // if there's data then put the data on the matched day (mapping).
        foreach ( $calendar_data as $key => $val ) {
            // if the week's date matches the database's date
            if ( $day === $val->weekday ) {
                $day_title_temp[$day][] = $val->info;
                continue;
            }

            $day_title_temp[$day][] = '';
        }
    }

    // remove unnecessary arrays from the calendar
    foreach ( $day_title_temp as $key => $val ) {
        $day_title[$key] = array_values( array_filter( $val ) );
    }

    return $day_title;
}

/**
 * Menu data with calendar data.
 *
 * This contains the days with its
 * corresponding menu. Each day can
 * have 12 menu. So this list contains
 * 12 menu or less.
 *
 * @param  array $weekdays      Date of this week
 * @param  array $calendar_data Data from the database based on this week's date.
 * @param  int   $term_id       Term id is the category id
 * @return array $menu_data     Combined calendar data with menu data.
 */
function get_menu_data( $weekdays, $calendar_data, $term_id ) {
    $menu_data = $menu_data_temp = array();

    // combining of calendar and data
    foreach ( $weekdays as $day ) {
        // check first if there's no data from the database
        if ( empty( $calendar_data ) ) {
            $menu_data_temp[$day.'|'.$term_id][] = array();
            continue;
        }

        // if there's data then put the data on the matched day (mapping).
        foreach ( $calendar_data as $value ) {
            // if the week's date matches the database's date
            if ( $day === $value->weekday ) {
                $menu_data_temp[$day.'|'.$term_id][] = array(
                    'term_id' => $value->term_id,
                    'weekday' => $value->weekday,
                    'label'   => $value->label,
                    'menu'    => $value->menu,
                    'price'   => $value->price,
                    'order'   => $value->menu_order,
                );
                continue;
            }

            $menu_data_temp[$day.'|'.$term_id][] = array();
        }
    }

    // remove unnecessary arrays from the calendar
    foreach ( $menu_data_temp as $key => &$day ) {
        foreach ( $day as $day_termid => $day_menu ) {
            // if the day like Monday has no menu then delete it.
            if ( empty( $day_menu) ) {
                unset( $day[$day_termid] );
                continue;
            }
        }

        // reorder the keys since there was unsetting of array.
        $menu_data[$key] = array_values( $day );
    }

    return $menu_data;
}

/**
 * Sort the menu according to the menu order from the database.
 *
 * @param  array $menu_data unsorted data
 * @return array $sort_data sorted data
 */
function get_sorted_menu( $menu_data ) {
    $sort_data = $sort_temp_data = $menu_number = array();

    foreach ( $menu_data as $key => $menu ) {
        if ( !empty( $menu) ) {
            foreach ( $menu as $menu_data ) {
                if ( empty( $menu_data['order']) ) {
                    $sort_temp_data[$key][] = array();
                    continue;
                }

                $sort_temp_data[$key][$menu_data['order']] = $menu_data;
            }

            continue;
        }

        $sort_temp_data[$key][] = array();
    }

    for ( $i=1; $i < 13; $i++ ) {
        $menu_number[$i] = array(
            'label' => '',
            'menu'  => '',
            'price' => '',
        );
    }

    foreach ( $sort_temp_data as $key => $menu_data ) {
        $unmatched = array_diff_key( $menu_number, $menu_data );
        $combined  = $menu_data + $unmatched;
        ksort($combined);

        $sort_data[$key] = array_filter( $combined );
    }

    return $sort_data;
}


/**
 * Get the calendar
 *
 * This contains the one or two calendar
 * of a parent. It consists the menu,
 * price and today's special.
 *
 * @param  int $term_id termid
 * @return array Contains the list of calendars.
 */
function custom_calendar( $term_id ) {
    // get the dates for this week(Mon-Sun)
    $weekdays = RestaurantWeekData::get_weekdays();

    // data from the database based from the term id
    $data = get_calendar_data( $term_id );

    // get the day data. This is the title of the day.
    $day_title = get_day_title( $weekdays, $data );

    // get the menu, price, special data combined with the calendar.
    $menu_data = get_menu_data( $weekdays, $data, $term_id );

    // sorted menu data
    $sort_data = get_sorted_menu($menu_data);
?>
    <tr>
      <?php foreach( $menu_data as $key => $val ):
        $dateTerm = explode( '|', $key );
        $nameday = date( 'D', strtotime($dateTerm[0]) );
        $weekday = get_japanese_day( $nameday );
      ?>
      <th>
        <?php echo date( 'm月d日', strtotime( $dateTerm[0]) ).'('.$weekday.')';?>
        <input type="hidden" name="<?php echo $nameday.'_'.$dateTerm[1];?>" value="<?php echo $dateTerm[0];?>">
      </th>
      <?php endforeach;?>
    </tr>
    <tr class="info">
      <?php if ( count($day_title) > 0 ):
        foreach( $day_title as $title ) : ?>
      <td><?php echo !empty( $title ) ? nl2br( $title[0] ): ''; ?></td>
      <?php
        endforeach;
      else:
        for( $s = 0; $s < 7; $s++ ) {
          echo '<td></td>';
        }
      endif;?>
    </tr>
    <?php for( $i=1;$i<13;$i++ ):
        $calendar_menu_data = '<tr class="menu">';
        foreach( $sort_data as $val ):
          foreach ( $val as $keys => $vals ) {
            if ( strlen( $vals['label'] ) <= 0 ) {
              unset( $val[$keys] );
              continue;
            }
          }

          if ( !empty( $val[$i] ) ) {
            $calendar_menu_data .= '<td>';
            $calendar_menu_data .= strlen( $val[$i]['label'] ) > 0 ? '<span class="bold">'.$val[$i]['label'].'</span>':'';
            $calendar_menu_data .= '<br>';
            $calendar_menu_data .= strlen( $val[$i]['menu'] ) > 0 ? '<span class="descrip">'.$val[$i]['menu'].'</span>' : '';
            $calendar_menu_data .= '<br>';
            $calendar_menu_data .= strlen( $val[$i]['price'] ) > 0 ? '<span class="price">'.$val[$i]['price'].'円</span>':'';
            $calendar_menu_data .= '</td>';
          } else {
            $calendar_menu_data .= '<td></td>';
          }
        endforeach;
        $calendar_menu_data .= '</tr>';
        $html = preg_replace( '/<tr class="menu"><td><\/td><td><\/td><td><\/td><td><\/td><td><\/td><td><\/td><td><\/td><\/tr>/', '', $calendar_menu_data );

        echo $html;?>
    <?php endfor;
}
