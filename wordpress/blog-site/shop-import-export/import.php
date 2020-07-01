<?php

/**
 * Include config
 */
require_once('config.php');

/**
 * エクスポートクラス
 *
 * This class only makes the update or
 * insert sql files. The real execution
 * for the database is in the outside.
 * Since the memory can't handle the
 * request since it is too many.
 * So it is handled outside of the
 * plugin.
 */
class Import
{
    /**
     * Csv file name
     *
     * @var string
     */
    public $csv_file = '';

    /**
     * What kind of site.
     * Change for every site.
     * Used in guid in insert post query.
     *
     * @var string
     */
    private $site = SHOP_SITE_TYPE;

    /**
     * Post pattern for insert
     *
     * @var string
     */
    private $post_id_pattern = '{post_id}';

    /**
     * For json response
     *
     * @var array
     */
    private $response = array(
        'status' => 'ok',
        'msg'    => '',
    );

    /**
     * Equivalent of the arrangement of the
     * data from the db. So please don't
     * rearrange cause this is arranged
     * accordingly already.
     *
     * @var array
     */
    private $equivalent = array(
        'id',
        'post_title',
        'store_name',
        'store_address',
        'store_tel',
        'store_url',
        'reserve_url',
        'open_close',
        'medical_time',
        'store_holiday',
        'medical_subject',
        'genre',
        'average_budget',
        'credit_card',
        'seats',
        'tobacco',
        'access',
        'parking',
        'parking_comment',
        'facility',
        'remarks',
        'nearest_station',
        'route',
        'store_remarks',

        'sales-time-remarks-1',
        'sales-time-remarks-2',
        'sales-time-remarks-3',


        'ppc_api_id',
        'ppc_api_source',
        'ppc_api_source_fdoc',
        'ppc_api_source_haisha',
    );

    /**
     * Equivalent of the arrangement of the
     * data from the db. So please don't
     * rearrange cause this is arranged
     * accordingly already.
     *
     * @var array
     */
    private $miyagi_equivalent = array(
        'id',
        'post_title',
        'store_name',
        'store_address',
        'store_tel',
        'open_close',
        'medical_time',
        'store_holiday',
        'medical_subject',
        'genre',
        'seki',
        'facility',
        'average_budget',
        'tobacco',
        'credit_card',
        'access',
        'parking',
        'parking_comment',
        'remarks',
        'store_url',
        'reserve_url',

        'sales-time-remarks-1',
        'sales-time-remarks-2',
        'sales-time-remarks-3',

        'ppc_api_id',
        'ppc_api_source',
        'ppc_api_source_fdoc',
        'ppc_api_source_haisha',
    );

    /**
     * カスタム営業時間のCSV
     *
     * @var array
     */
    private $sched_equivalent = array(
        'id',
        'post_id',
        'time',
        'time_display',
        'mon',
        'mon_display',
        'tue',
        'tue_display',
        'wed',
        'wed_display',
        'thu',
        'thu_display',
        'fri',
        'fri_display',
        'sat',
        'sat_display',
        'sun',
        'sun_display',
        'hol',
        'hol_display',
    );

    /**
     * Since we have to have an insert
     * query. We need to insert the
     * "field_" also which is required
     * for the acf plugin.
     *
     * @var array
     */
    private $acf_equivalent = array(
        'store_name'            => 'field_594b8ad1b68b5',
        'store_address'         => 'field_594b9263b68b6',
        'store_tel'             => 'field_594b92b3b68b7',
        'store_url'             => 'field_594bcaf7c7f4b',
        'reserve_url'           => 'field_5ca550bc208b9',
        'open_close'            => 'field_594b92c8b68b8',
        'medical_time'          => 'field_5bcd233eee5e5',
        'store_holiday'         => 'field_594b9325b68b9',
        'medical_subject'       => 'field_5bcd23d8ee5e6',
        'genre'                 => 'field_5bcd2488ee5e7',
        'average_budget'        => 'field_594b9354b68ba',
        'credit_card'           => 'field_594b9388b68bb',
        'seats'                 => 'field_5bcd249aee5e8',
        'tobacco'               => 'field_5bcd2639ee5ec',
        'access'                => 'field_5bcd264eee5ed',
        'parking'               => 'field_594b9485b68be',
        'parking_comment'       => 'field_594b94d7b68bf',
        'facility'              => 'field_5bcd25eeee5eb',
        'remarks'               => 'field_594b94fab68c0',
        'nearest_station'       => 'field_594b93bfb68bc',
        'route'                 => 'field_594b93f2b68bd',
        'store_remarks'         => 'field_5bcd24f9ee5ea',

        'sales-time-remarks-1' => 'field_5db0f51c0fb3f',
        'sales-time-remarks-2' => 'field_5db0f5350fb40',
        'sales-time-remarks-3' => 'field_5db13ac456fdb',

        'ppc_api_id'            => 'field_5c6dfc643eff3',
        'ppc_api_source'        => 'field_5c6bc5ac2688a',
        'ppc_api_source_fdoc'   => 'field_5c6cf3cc9cf55',
        'ppc_api_source_haisha' => 'field_5c6cf433def05',
    );

    /**
     * Since we have to have an insert
     * query. We need to insert the
     * "field_" also which is required
     * for the acf plugin.
     *
     * @var array
     */
    private $acf_miyagi_equivalent = array(
        'store_name'            => 'field_594b8ad1b68b5',
        'store_address'         => 'field_594b9263b68b6',
        'store_tel'             => 'field_594b92b3b68b7',
        'open_close'            => 'field_594b92c8b68b8',
        'medical_time'          => 'field_5b4eb1671090c',
        'store_holiday'         => 'field_594b9325b68b9',
        'medical_subject'       => 'field_5b4eb28e1090d',
        'genre'                 => 'field_5b4eb7f65a6e8',
        'seki'                  => 'field_5b4ec94c5a6ea',
        'facility'              => 'field_5b5fb0f48855a',
        'average_budget'        => 'field_594b9354b68ba',
        'tobacco'               => 'field_5b4ec9355a6e9',
        'credit_card'           => 'field_594b9388b68bb',
        'access'                => 'field_594b93bfb68bc',
        'parking'               => 'field_594b9485b68be',
        'parking_comment'       => 'field_594b94d7b68bf',
        'remarks'               => 'field_594b94fab68c0',
        'store_url'             => 'field_594bcaf7c7f4b',
        'reserve_url'           => 'field_5cd4de7560630',

        'sales-time-remarks-1' => 'field_5db0f51c0fb3f',
        'sales-time-remarks-2' => 'field_5db0f5350fb40',
        'sales-time-remarks-3' => 'field_5db13ac456fdb',

        'ppc_api_id'            => 'field_5c6dfc643eff3',
        'ppc_api_source'        => 'field_5c6bc5ac2688a',
        'ppc_api_source_fdoc'   => 'field_5c6cf3cc9cf55',
        'ppc_api_source_haisha' => 'field_5c6cf433def05',
    );

    /**
     * Import main function
     *
     * This is actually the creating
     * of insert or update query.
     *
     * @return
     */
    public function import_exec() {
        setlocale( LC_ALL, 'ja_JP.UTF-8' );

        $this->csv_file = $_POST['file'];

        if ( !$this->check() ) {
            $this->response['status'] = 'ng';
            $this->response['msg'] = 'not csv';
        }

        // set the shop-csv
        if ( get_option( 'shop-csv-file' ) === false ) {
            add_option( 'shop-csv-file', $this->csv_file );
        } else {
            update_option( 'shop-csv-file', $this->csv_file );
        }


        if ( strpos( $this->csv_file, 'sched' ) > -1 ) {
            // カスタム営業時間
            $this->sched_create_update_insert_query();
        } else {
            // 店舗情報のデータ
            $this->create_update_insert_query();
        }

        echo json_encode( $this->response );
        return;
    }

    /**
     * To create update insert query
     *
     * @return
     */
    private function create_update_insert_query() {
        $batchsize = 300;
        $last_post_id = $this->get_last_post_id();
        $post_id_pattern = $this->post_id_pattern;
        $equivalent = SHOP_SITE_TYPE === 'miyagi'?$this->miyagi_equivalent:$this->equivalent;

        // open the exported csv file
        $exported_csv_handle = fopen( $this->csv_file, 'r' );
        if ( $exported_csv_handle !== false ) {
            $row = 0;
            // loop thru the each record from the csv file
            while( ( $data = fgetcsv( $exported_csv_handle, 1000, "," ) ) !== FALSE ) {
                // loop thru each column from every row
                foreach ( $data as $key => $val ) {
                    if ( strlen( $val ) < 1 ) {
                        $data[$key] = '';
                        continue;
                    }

                    // change the encoding
                    $data[$key] = mb_convert_encoding( $val, 'UTF-8', 'SJIS-win' );
                }

                // splitting of CSV file
                if ( $row % $batchsize == 0 ) {
                    // opening of each sql file
                    $sql_handle = fopen( SHOP_PLUGIN_PATH.'tmp/query_'.$row.'.sql', 'w' );
                }

                if ( $data[0] !== 'id' && strlen( $data[1] ) > 0 ) {
                    // if no post id then considered as new data
                    $post_id = $data[0] !== '' || $data[0] > 0?$data[0]:'';

                    // loop thru the csv equivalent and column
                    foreach( $equivalent as $key => $meta_key ) {
                        if ( $meta_key === 'id' ) {
                            continue;
                        }

                        // this is the value of the post meta
                        $meta_value = $data[$key];

                        // EPARK病院API
                        if ( $meta_key === 'ppc_api_source_fdoc' && $meta_value === 'detail' ) {
                            $meta_value = 'a:1:{i:0;s:18:"fdoc_dental_detail";}';
                        }

                        // EPARK歯科API 詳細と空満
                        if ( $meta_key === 'ppc_api_source_fdoc' && $meta_value === 'detail-realtime' ) {
                            $meta_value = $meta_value === 'a:2:{i:0;s:13:"haisha_detail";i:1;s:14:"haisha_apstate";}';
                        } else if ( $meta_key === 'ppc_api_source_haisha' && $meta_value === 'detail' ) {
                            $meta_value = $meta_value === 'a:1:{i:0;s:13:"haisha_detail";}';
                        }

                        // if empty post id on the csv then considered as new data so insert query
                        if ( empty( $post_id ) ) {
                            // for inserting into post table
                            if ( $meta_key === 'post_title' ) {
                                $title = $meta_key === 'post_title'?$meta_value:'';
                                $sql = $this->insert_post( $last_post_id, $title );
                            } else {
                                // inserting into postmeta table. If null means no data then don't insert
                                $sql = $meta_value !== 'NULL'?$this->make_sql( $post_id_pattern, $meta_key, $meta_value, 'insert' ):'';
                            }
                        } else {
                            // change the title into post table
                            if ( $meta_key === 'post_title' ) {
                                $sql = $this->change_post_title( $post_id, $meta_value );
                            } else {
                                // to check whether there's a meta data already
                                // it's either empty or data does not exists yet
                                $sql = $meta_value === 'NULL'?'':$this->make_sql( $post_id, $meta_key, $meta_value, 'update' );
                            }
                        }

                        fwrite( $sql_handle, $sql );
                    }
                }

                $row++;
                // $last_post_id++;
            }

            // close the sql file
            fclose( $sql_handle );

            // close the csv file
            fclose( $exported_csv_handle );
        }
    }

    /**
     * カスタム営業時間のインポート
     *
     * @return
     */
    private function sched_create_update_insert_query() {
        global $wpdb;
        $batchsize = 300;
        $exported_csv_handle = fopen( $this->csv_file, 'r' );
        $cols = array(
            'post_id',
            'data',
            'user_id',
            'dtcreate',
        );
        if ( $exported_csv_handle !== false ) {
            $row = 0;
            // loop thru the each record from the csv file
            while( ( $data = fgetcsv( $exported_csv_handle, 1000, "," ) ) !== FALSE ) {
                // loop thru each column from every row
                foreach ( $data as $key => $val ) {
                    if ( strlen( $val ) < 1 ) {
                        $data[$key] = '';
                        continue;
                    }

                    // change the encoding
                    $data[$key] = mb_convert_encoding( $val, 'UTF-8', 'SJIS-win' );
                }

                // splitting of CSV file
                if ( $row % $batchsize == 0 ) {
                    // opening of each sql file
                    $sql_handle = fopen( SHOP_PLUGIN_PATH.'tmp/sched_query_'.$row.'.sql', 'w' );
                }

                if ( $data[0] !== 'id' && strlen( $data[1] ) > 0 ) {
                    // item id, if no item id then considered as new data
                    $item_id = $data[0] !== '' || $data[0] > 0?$data[0]:'';
                    $sched_data = array();
                    foreach ( $data as $key => $value ) {
                        $value = $value === 'X'?'✖':($value === ''?'空白':$value);

                        $sched_data[$this->sched_equivalent[$key]] = $value;
                    }

                    $post_id = $sched_data['post_id'];
                    unset( $sched_data['id'] );
                    unset( $sched_data['post_id'] );

                    $json_data = $this->escape_content( json_encode( $sched_data, JSON_UNESCAPED_UNICODE ) );
                    $vals = array(
                        $post_id,
                        "'".$json_data."'",
                        9999,
                        '"'.date( 'Y-m-d H:i:s' ).'"',
                    );

                    if ( empty( $item_id ) ) {
                        $sql = 'INSERT INTO '.$wpdb->prefix.'shop ('.implode( ', ', $cols ).')';
                        $sql.= ' VALUES ('.implode( ', ', $vals ).');'."\n";
                    } else {
                        $sql = "UPDATE ".$wpdb->prefix."shop ";
                        $sql.= "SET data = '".$json_data."'";
                        $sql.= " , dtupdate = '".date( 'Y-m-d H:i:s' )."'";
                        $sql.= " WHERE ID = ".$item_id.' AND post_id = '.$post_id.";\n";
                    }

                    fwrite( $sql_handle, $sql );
                }

                $row++;
            }

            // close the sql file
            fclose( $sql_handle );
        }

        // close the csv file
        fclose( $exported_csv_handle );
    }

    /**
     * To get the last
     * post id for inserting a
     * new record in post type
     * shop.
     *
     * @return int post id
     */
    private function get_last_post_id() {
        global $wpdb;
        $sql = 'SELECT ID FROM '.$wpdb->prefix.'posts ORDER BY ID DESC LIMIT 1';
        $res = $wpdb->get_results( $sql );
        return $res[0]->ID + 1;
    }

    /**
     * Change the post title in
     * post table. This is just
     * creating a query.
     *
     * @param int    $post_id shop id
     * @param string $value   title
     * @return string         update query
     */
    private function change_post_title( $post_id, $value ) {
        global $wpdb;
        $sql = "UPDATE ".$wpdb->prefix."posts SET post_title = '".$this->escape_content( $value )."' WHERE ID = ".$post_id."; \n";
        return $sql;
    }

    /**
     * Escape the content
     *
     * @param string $value    unescaped content
     * @return string $content escaped content
     */
    private function escape_content( $value ) {
        global $wpdb;
        $content = $wpdb->remove_placeholder_escape( esc_sql( $value ) );
        return $content;
    }

    /**
     * To make the insert or update query.
     * This is for the post meta table only.
     *
     * @param int    $post_id    shop id
     * @param string $meta_key   shop meta key like store_name, etc.
     * @param string $meta_value the meta_key values.
     * @param string $type       either 'insert' or 'update'
     * @return string $sql       insert or update query
     */
    private function make_sql( $post_id, $meta_key, $meta_value, $type ) {
        global $wpdb;
        $content = $this->escape_content( $meta_value );
        $acf_equivalent = SHOP_SITE_TYPE === 'miyagi'?$this->acf_miyagi_equivalent:$this->acf_equivalent;

        if ( $type === 'insert' ) {
            $sql = "INSERT INTO ".$wpdb->prefix."postmeta (post_id, meta_key, meta_value) VALUES ( ".$post_id.", '".$meta_key."', '".$content."');\n";
            // check if there's an equivalent acf field
            if ( isset( $acf_equivalent[$meta_key] ) ) {
                $sql.= "INSERT INTO ".$wpdb->prefix."postmeta (post_id, meta_key, meta_value) VALUES ( ".$post_id.", '_".$meta_key."', '".$acf_equivalent[$meta_key]."');\n";
            }
        } else {
            $sql = "UPDATE ".$wpdb->prefix."postmeta SET meta_value = '".$content."' WHERE post_id = ".$post_id." AND meta_key = '".$meta_key."'; \n";
        }

        return $sql;
    }

    /**
     * This is creating the insert
     * query for the post table.
     *
     * @param int $post_id   shop id
     * @param string $title  post title or name
     * @return string $sql   insert query
     */
    private function insert_post( $post_id, $title ) {
        global $wpdb;

        $col = array(
            'ID',
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_title',
            'post_status',
            'post_name',
            'post_modified',
            'post_modified_gmt',
            'guid',
            'post_type',

            // these are not null columns that are needed but has empty value
            'post_content',
            'post_excerpt',
            'to_ping',
            'pinged',
            'post_content_filtered',
        );

        $val = array(
            date( 'Y-m-d H:i:s' ),
            gmdate( 'Y-m-d H:i:s' ),
            $this->escape_content( $title ),
            'private',
            $this->escape_content( $title ),
            date( 'Y-m-d H:i:s' ),
            gmdate( 'Y-m-d H:i:s' ),
            'https://'.$this->site.'.machishs.jp/?post_type=shop&amp;p={post_id}',
            'shop',

            // these are the empty string values
            "",
            "",
            "",
            "",
            "",
        );
        $sql = "INSERT INTO ".$wpdb->prefix."posts (".implode( ', ', $col ).")";
        $sql.= ' VALUES ({post_id}, 1, "'.implode( '", "', $val ).'");'."\n";

        return $sql;
    }

    /**
     * Check the csv files
     *
     * @return boolean if has error then false else true
     */
    private function check() {
        // to check if not empty
        if ( empty( $this->csv_file ) ) {
            return false;
        }

        // only accepts csv
        $ext = pathinfo( $this->csv_file, PATHINFO_EXTENSION );
        if ( $ext !== 'csv' ) {
            return false;
        }

        return true;
    }
}
