<?php

/**
 * Include config
 */
require_once('config.php');

/**
 * エクスポートクラス
 */
class Export
{
    /**
     * Encoding for the csv.
     * The first one.
     */
    const FIRST_ENCODING_TYPE = 'SJIS-win';

    /**
     * Encoding for the csv.
     * The second one.
     */
    const SECOND_ENCODING_TYPE = 'UTF-8';

    /**
     * エクスポート
     */
    public function export_exec() {
        setlocale( LC_ALL, 'ja_JP.UTF-8' );

        // データを取得
        if ( SHOP_SITE_TYPE === 'miyagi' ) {
            $data = $this->get_miyagi_data();
        } else {
            $data = $this->get_data();
        }

        // create temporary file
        $this->create_tmp_file( $data );
    }

    /**
     * カスタム営業時間のエクスポート
     */
    public function sched_export_exec() {
        setlocale( LC_ALL, 'ja_JP.UTF-8' );
        $param = $_POST;
        global $wpdb;

        $sql = 'SELECT * FROM '.$wpdb->prefix.'shop ORDER BY post_id ASC, ID ASC;';
        $res = $wpdb->get_results( $sql );

        $header = array(
            'id',
            '店舗ID「POST ID」',
            '営業時間',
            '営業時間を表示',
            '月',
            '月を表示',
            '火',
            '火を表示',
            '水',
            '水を表示',
            '木',
            '木を表示',
            '金',
            '金を表示',
            '土',
            '土を表示',
            '日',
            '日を表示',
            '祝',
            '祝を表示',
        );

        $handle = fopen( SHOP_SCHED_CSV_FULL_FILEPATH, 'w' );
        if ( $handle !== false ) {
            // header
            fputcsv( $handle, $this->convert_encoding( $header ) );
            foreach ( $res as $val ) {
                $sched_data = json_decode( $val->data );
                $sched_mon = $sched_data->mon === '✖'?'X':$this->convert_encoding( $sched_data->mon );
                $sched_tue = $sched_data->tue === '✖'?'X':$this->convert_encoding( $sched_data->tue );
                $sched_wed = $sched_data->wed === '✖'?'X':$this->convert_encoding( $sched_data->wed );
                $sched_thu = $sched_data->thu === '✖'?'X':$this->convert_encoding( $sched_data->thu );
                $sched_fri = $sched_data->fri === '✖'?'X':$this->convert_encoding( $sched_data->fri );
                $sched_sat = $sched_data->sat === '✖'?'X':$this->convert_encoding( $sched_data->sat );
                $sched_sun = $sched_data->sun === '✖'?'X':$this->convert_encoding( $sched_data->sun );
                $sched_hol = $sched_data->hol === '✖'?'X':$this->convert_encoding( $sched_data->hol );

                $data = array(
                    $val->ID,
                    $val->post_id,
                    $this->convert_encoding( $sched_data->time ),
                    empty( $sched_data->time_display )?0:$sched_data->time_display,
                    $sched_mon,
                    $sched_data->mon_display,
                    $sched_tue,
                    $sched_data->tue_display,
                    $sched_wed,
                    $sched_data->wed_display,
                    $sched_thu,
                    $sched_data->thu_display,
                    $sched_fri,
                    $sched_data->fri_display,
                    $sched_sat,
                    $sched_data->sat_display,
                    $sched_sun,
                    $sched_data->sun_display,
                    $sched_hol,
                    $sched_data->hol_display,
                );

                fputcsv( $handle, $data );
            }
        }

        fclose( $handle );
    }

    /**
     * Create the tmp file first.
     * Then on the download.php is
     * where the exporting of csv
     * happens.
     *
     * @param array $data Data from db
     */
    private function create_tmp_file( $data ) {
        header( "Content-type: text/csv" );
        $final_data = array();
        // to create a temporary csv file
        $handle = fopen( SHOP_CSV_FULL_FILEPATH, 'w' );
        if ( $handle !== false ) {
            // first is the header
            fputcsv( $handle, $data['header'] );

            // the proceed with writing the data from the db
            foreach ( $data['data'] as $key => $items ) {
                $row = 0;

                // make the data from db which is an object into an array
                foreach ( $items as $itemsKey => $val ) {
                    $final_data[$row] = is_null( $val )?'':$this->convert_encoding( $val );
                    $row++;
                }

                fputcsv( $handle, $final_data );
            }
        }

        fclose( $handle );
    }

    /**
     * 店舗情報を取得する
     *
     * @return array
     */
    private function get_data() {
        global $wpdb;

        $sql = <<<__SQL
SELECT
    post.id as ID
    , post.post_title as タイトル
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_name'         and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '名称'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_address'      and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '住所'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_tel'          and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '電話番号'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_url'          and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'URL'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'reserve_url'        and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '予約URL'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'open_close'         and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '営業時間'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'medical_time'       and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '診療時間'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_holiday'      and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '定休日'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'medical_subject'    and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '診療科目'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'genre'              and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'ジャンル'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'average_budget'     and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '平均予算'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'credit_card'        and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'クレジットカード'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'seats'              and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '席数'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'tobacco'            and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '喫煙'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'access'             and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'アクセス'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'parking'            and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '駐車場'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'parking_comment'    and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '駐車場に関するコメント'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'facility'           and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '店舗設備'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'remarks'            and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '備考'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'nearest_station'    and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '最寄り駅'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'route'              and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '行き方'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_remarks'      and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '店舗備考'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'sales-time-remarks-1' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '営業時間備考１'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'sales-time-remarks-2' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '営業時間備考２'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'sales-time-remarks-3' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '営業時間備考３'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_id'         and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'API ID'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source'     and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'APIデータソース'
    #, case when (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source_fdoc' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) LIKE '%fdoc_dental_detail%' then 'EPARK病院API詳細' end as 'EPARK病院API詳細'
    #, case when (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source_haisha' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) LIKE '%haisha_detail%' then 'EPARK歯科API詳細' end as 'EPARK歯科API詳細'
    , case when (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source_fdoc' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) LIKE '%fdoc_dental_detail%' then 'detail' end as 'EPARK病院API'
    , case
		when (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source_haisha' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) LIKE '%a:2:{i:0;s:13:"haisha_detail";i:1;s:14:"haisha_apstate";}%'
			then 'detail-realtime'
		when (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source_haisha' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) LIKE '%a:1:{i:0;s:13:"haisha_detail";}%'
			then 'detail'
		end as 'EPARK歯科API詳細'
FROM
    {$wpdb->prefix}posts post
WHERE
    post_type = 'shop'
    and post_status in ('private', 'draft', 'publish', 'pending')
#and post.ID = 823
ORDER BY post.id asc
#limit 12;
__SQL;
        $data = $wpdb->get_results( $sql );

        $header = array(
            'id',
            'タイトル',
            '名称',
            '住所',
            '電話番号',
            'URL',
            '予約URL',
            '営業時間',
            '診療時間',
            '定休日',
            '診療科目',
            'ジャンル',
            '平均予算',
            'クレジットカード',
            '席数',
            '喫煙',
            'アクセス',
            '駐車場',
            '駐車場に関するコメ',
            '店舗設備',
            '備考',
            '最寄り駅',
            '行き方',
            '店舗備考',
            '営業時間備考１',
            '営業時間備考２',
            '営業時間備考３',
            'API ID',
            'APIデータソース',
            'EPARK病院API',
            'EPARK歯科API詳細',
        );

        return array( 'header' => $this->convert_encoding( $header ), 'data' => $data );
    }

    /**
     * 宮城の店舗情報を取得する
     *
     * Why? Because miyagi's
     * data arrangement is diff
     * from saitama and tokyo.
     *
     * @return array
     */
    private function get_miyagi_data() {
        global $wpdb;

        $sql = <<<__SQL
SELECT
    post.id as ID
    , post.post_title as タイトル
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_name'         and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '名称'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_address'      and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '住所'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_tel'          and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '電話番号'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'open_close'         and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '営業時間'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'medical_time'       and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '診療時間'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_holiday'      and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '定休日'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'medical_subject'    and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '診療科目'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'genre'              and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'ジャンル'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'seki'               and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '席数'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'facility'           and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '店舗設備'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'average_budget'     and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '平均予算'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'tobacco'            and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '喫煙'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'credit_card'        and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'クレジットカード'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'access'             and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'アクセス'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'parking'            and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '駐車場'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'parking_comment'    and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '駐車場に関するコメント'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'remarks'            and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '備考'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'store_url'          and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'URL'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'reserve_url'        and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '予約URL'

    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'sales-time-remarks-1' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '営業時間備考１'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'sales-time-remarks-2' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '営業時間備考２'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'sales-time-remarks-3' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as '営業時間備考３'

    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_id'         and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'API ID'
    , (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source'     and post_id = post.ID ORDER BY meta_id asc LIMIT 1) as 'APIデータソース'
    #, case when (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source_fdoc' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) LIKE '%fdoc_dental_detail%' then 'EPARK病院API詳細' end as 'EPARK病院API詳細'
    #, case when (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source_haisha' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) LIKE '%haisha_detail%' then 'EPARK歯科API詳細' end as 'EPARK歯科API詳細'
    , case when (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source_fdoc' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) LIKE '%fdoc_dental_detail%' then 'detail' end as 'EPARK病院API'
    , case
		when (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source_haisha' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) LIKE '%a:2:{i:0;s:13:"haisha_detail";i:1;s:14:"haisha_apstate";}%'
			then 'detail-realtime'
		when (select meta_value from {$wpdb->prefix}postmeta where meta_key = 'ppc_api_source_haisha' and post_id = post.ID ORDER BY meta_id asc LIMIT 1) LIKE '%a:1:{i:0;s:13:"haisha_detail";}%'
			then 'detail'
		end as 'EPARK歯科API詳細'
FROM
    {$wpdb->prefix}posts post
WHERE
    post_type = 'shop'
    and post_status in ('private', 'draft', 'publish', 'pending')
#and post.ID = 823
ORDER BY post.id asc
#limit 12;
__SQL;
        $data = $wpdb->get_results( $sql );

        $header = array(
            'id',
            'タイトル',

            '名称',
            '住所',
            '電話番号',
            '営業時間',
            '診療時間',
            '定休日',
            '診療科目',
            'ジャンル',
            '席数',
            '店舗設備',
            '平均予算',
            '喫煙',
            'クレジットカード',
            'アクセス',
            '駐車場',
            '駐車場に関するコメント',
            '備考',
            'URL',
            '予約URL',

            '営業時間備考１',
            '営業時間備考２',
            '営業時間備考３',

            'API ID',
            'APIデータソース',
            'EPARK病院API',
            'EPARK歯科API詳細',
        );

        return array( 'header' => $this->convert_encoding( $header ), 'data' => $data );
    }

    /**
     * Convert encoding so that the
     * csv can be read.
     *
     * @param  array|string $param default data. Array is for the header and the string is for the db data.
     * @return array|string        converted data
     */
    private function convert_encoding( $param ) {
        if ( is_array( $param ) ) {
            $data = array();
            foreach ( $param as $key => $val ) {
                $data[$key] = mb_convert_encoding( $val, self::FIRST_ENCODING_TYPE, self::SECOND_ENCODING_TYPE );
            }

            return $data;
        } else {
            return mb_convert_encoding( $param, self::FIRST_ENCODING_TYPE, self::SECOND_ENCODING_TYPE );
        }
    }
}
