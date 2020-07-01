<?php

/**
 * This class will get the
 * data of the week.
 */
class RestaurantWeekData
{

    /**
     * Get the weekdays.
     *
     * It will get the dates of
     * today's week.
     *
     * @param  string $date Y-m-d | empty
     * @return array list of dates.
     */
    public static function get_weekdays($date = '')
    {
        $day = empty( $date ) ? date( 'D' ) : $date;
        if ( $day === 'Sun' && version_compare( PHP_VERSION, '5.6.29' ) === -1 ) {
            return array(
                date( 'Y-m-d', strtotime( 'monday this week' , strtotime( 'monday last week' ) ) ),
                date( 'Y-m-d', strtotime( 'tuesday this week', strtotime( 'monday last week' ) ) ),
                date( 'Y-m-d', strtotime( 'wednesday this week', strtotime( 'monday last week' ) ) ),
                date( 'Y-m-d', strtotime( 'thursday this week', strtotime( 'monday last week' ) ) ),
                date( 'Y-m-d', strtotime( 'friday this week', strtotime( 'monday last week' ) ) ),
                date( 'Y-m-d', strtotime( 'saturday this week', strtotime( 'monday last week' ) ) ),
                date( 'Y-m-d', strtotime( 'sunday this week', strtotime( 'monday last week' ) ) )
            );
        }

        return array(
            date( 'Y-m-d', strtotime( 'monday this week' , strtotime( 'monday this week' ) ) ),
            date( 'Y-m-d', strtotime( 'tuesday this week', strtotime( 'monday this week' ) ) ),
            date( 'Y-m-d', strtotime( 'wednesday this week', strtotime( 'monday this week' ) ) ),
            date( 'Y-m-d', strtotime( 'thursday this week', strtotime( 'monday this week' ) ) ),
            date( 'Y-m-d', strtotime( 'friday this week', strtotime( 'monday this week' ) ) ),
            date( 'Y-m-d', strtotime( 'saturday this week', strtotime( 'monday this week' ) ) ),
            date( 'Y-m-d', strtotime( 'sunday this week', strtotime( 'monday this week' ) ) ),
        );
    }

    /**
     * Get the weeknumber for the database condition.
     *
     * @return int week number by year.
     */
    public static function get_weeknumber()
    {
        $weekdays = self::get_weekdays();
        return ( int )date( 'W', strtotime( $weekdays[0] ) );
    }
}
