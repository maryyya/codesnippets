<?php

/**
 * This class handles all the
 * transaction for the file.
 */
class HC_File
{
    /**
     * This function will be called from
     * js. This will check what kind of
     * action first, is is a insert, update,
     * or delete file. This will call check_file
     * and call whichever action/type was passed.
     *
     * @return array $res    This array contains the status and data.
     */
    public function file() {
        // get the post data
        $param = $_POST;

        // get the config data
        $hc_config = hc_config();

        // define the response first
        $res = $hc_config['response'];

        // just check if param is not empty
        if ( empty( $param ) || !is_array( $param ) || !isset( $param['type'] ) ) {
            $res['msg'] = hc_get_msg( 'ERR-01' );
            return $res;
        }

        // sanitize the input data if there's no error in checking
        foreach ( $param as $key => &$value ) {
            // sanitize for the email
            if ( $key === 'content' ) {
                $sanitize = sanitize_textarea_field( $value );
                $strip = stripslashes( $value );
                $param[$key] = str_replace( site_url(), '[url]', $strip );

                continue;
            }

            // do not sanitize the list of members for registration in file
            if ( $key === 'mem_data' ) {
                continue;
            }

            // sanitize all the params according to text
            $param[$key] = sanitize_text_field( $value );
        }

        // check input data first
        $checked = $this->check_file( $param );
        if ( $checked !== true ) {
            $res['msg'] = $checked;
            return $res;
        }

        switch ( $param['type'] ) {
            // ファイル登録
            case 'register':
                $insert = $this->insert_file( $param );
                // check if insert is unsuccessful
                if ( $insert === false ) {
                    $res['msg'] = hc_get_msg( 'ERR-02' );

                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'file_id'   => $insert,
                    'admin_url' => admin_url(),
                );

                return $res;
                break;

            // ファイル更新
            case 'detail':
                $update = $this->update_file( $param );
                // check if update is unsuccessful
                if ( $update === false ) {
                    $res['msg'] = hc_get_msg( 'ERR-03' );

                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'file_id'   => $param['file_id'],
                    'admin_url' => admin_url(),
                );

                return $res;
                break;

            // ファイル削除
            case 'delete':
                $delete = $this->delete_file( $param );
                // check if delete is unsuccessful
                if ( $delete === false ) {
                    $res['msg'] = hc_get_msg( 'ERR-04' );

                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'admin_url' => admin_url(),
                );

                return $res;
                break;

            // ファイルとメンバー紐付け登録
            case 'file_mem_register':
                // check if registration is unsuccessful
                if ( !$this->file_member_register( $param ) ) {
                    $res['msg'] = hc_get_msg( 'ERR-02' );

                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'admin_url' => admin_url(),
                    'id'        => $param['file_id']
                );

                return $res;
                break;

            // ファイルとメンバー紐付け登録
            case 'file_mem_delete':
                // check if delete is unsuccessful
                if ( !$this->file_member_delete( $param ) ) {
                    $res['msg'] = hc_get_msg( 'ERR-04' );

                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'admin_url' => admin_url(),
                    'id'        => $param['file_id']
                );

                return $res;
                break;

            default:
                $res['msg'] = hc_get_msg( 'ERR-01' );
                break;
        }

        return $res;
    }

    /**
     * Check if file id exists
     *
     * @param  string $id  id of the file
     * @return boolean     true if id exists else false.
     */
    public function check_data_existence( $id, $action = '' ) {
        global $wpdb;

        // check if id is empty
        if ( empty( $id ) ) {
            return false;
        }

        $sql = <<<__SQL
SELECT
  COUNT(*) as cnt
  , title
FROM
  hc_file
WHERE
  ID = %d
  AND deleteflg = %d
__SQL;

        // parameter for the query
        $param = array( (int)$id, 0 );

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $param );

        // get the result
        $res = $wpdb->get_results( $prepare );

        // check if data exists
        if ( (int)$res[0]->cnt > 0 ) {
            return $res[0]->title;
        }

        return false;
    }

    /**
     * This function will get the
     * file detail.
     *
     * @param  int $file_id   File id from get.
     * @return boolean|array  If no data then return false, else it will return the array of data.
     */
    public function get_file_detail( $id ) {
        global $wpdb;

        // check if id is not empty
        if ( empty( $id ) ) {
            return false;
        }

        $sql = <<<__SQL
SELECT
  ID
  , title
  , content
  , date_data as date
  , tag
  , file
  , image_id
  , dtcreate
  , dtupdate
  , deleteflg
FROM
  hc_file
WHERE
  ID = %d
  AND deleteflg = %d
__SQL;
        // get the param for the sql
        $param = array( (int)$id, 0 );

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $param );

        // get the result
        $res = $wpdb->get_results( $prepare );

        // check if result is empty
        if ( empty( $res ) ) {
            return false;
        }

        return $res[0];
    }

    /**
     * Get the list of the files
     *
     * @param  int    $offset offset for pagination
     * @param  int    $limit  limit for pagination
     * @param  string $search search keyworkd
     * @param  string $tag    search tag
     * @return array  $res    result of the list
     */
    public function get_file_list( $offset, $limit, $search, $tag ) {
        global $wpdb;
        $cond = '';
        $param = array();
        $param[] = 0;

        // condition for search keyword
        if ( !empty( $search ) ) {
            $date = $this->check_search_date( mb_convert_kana( $search, 'a' ) );
            if ( $date !== false ) {
                $cond    .= "AND date_data LIKE '%%%s%%'";
                $param[] = $date;
            } else {
                $cond    .= "AND title LIKE '%%%s%%'";
                $param[] = $search;
            }
        }

        // if tag selectbox is not empty
        if ( !empty( $tag ) ) {
            // $cond    .= "AND tag LIKE '%%%s%%'";
            $cond    .= "AND tag = %s";
            $param[] = $tag;
        }

        $sql = <<<__SQL
SELECT
  SQL_CALC_FOUND_ROWS *
  , ID
  , title
  , content
  , date_data
  , tag
  , file
  , image_id
  , CASE
    WHEN dtupdate IS NULL THEN dtcreate
    ELSE dtupdate
  END as date
FROM
  hc_file
WHERE
  deleteflg = %d
{$cond}
ORDER BY id DESC
LIMIT %d, %d
__SQL;

        // parameters for pagination
        $param[] = $offset;
        $param[] = $limit;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $param );

        // get the results
        $res = $wpdb->get_results( $prepare );

        // get the total items
        $total = $wpdb->get_results( 'SELECT FOUND_ROWS() as total' );

        // return the list
        return array( $res, (int)$total[0]->total );
    }

    /**
     * This will get the file
     * member list.
     *
     * @param  int    $file_id file id from get
     * @param  int    $offset  offset of pagination
     * @param  int    $limit   limit of pagination
     * @param  string $search  search keyword
     * @return array  $res     member list and total number list
     */
    public function get_file_member_list( $file_id, $offset, $limit, $search ) {
        global $wpdb;
        $param = array( (int)$file_id );
        $search_cond = '';

        // condition for search
        if ( !empty( $search ) ) {
            $search_cond = "AND loginid LIKE '%%%s%%' \n AND email LIKE '%%%s%%'";
            $param[] = $search;
            $param[] = $search;
        }

        $sql = <<<__SQL
SELECT
  SQL_CALC_FOUND_ROWS *
  , hc_member.*
FROM
  hc_member
WHERE
  ID NOT IN (SELECT
      member_id
    FROM
      hc_file_member WHERE file_id = %d)
  {$search_cond}
LIMIT %d, %d
__SQL;

        // parameters for pagination
        $param[] = $offset;
        $param[] = $limit;

        // prepare sql
        $prepare = $wpdb->prepare( $sql, $param );

        // get results
        $res = $wpdb->get_results( $prepare );

        // get the total
        $total   = $wpdb->get_results( 'SELECT FOUND_ROWS() as total' );

        return array( $res, (int)$total[0]->total );
    }

    /**
     * Get the member list for the file.
     *
     * @param  int    $file_id file id from get
     * @param  int    $offset  offset for pagination
     * @param  int    $limit   limit for pagination
     * @param  string $search  search keyword
     * @return array           member list of the file and total number list.
     */
    public function get_file_member( $file_id, $offset, $limit, $search ) {
        global $wpdb;
        $param = array( (int)$file_id );
        $search_cond = '';

        // condition for search
        if ( !empty( $search ) ) {
            $search_cond = "AND loginid LIKE '%%%s%%' \n AND email LIKE '%%%s%%'";
            $param[] = $search;
            $param[] = $search;
        }

        $sql = <<<__SQL
SELECT
  SQL_CALC_FOUND_ROWS *
  , mem.ID as memid
  , mem.loginid
  , mem.email
  , fmem.ID as fmemid
  , fmem.file_id
  , fmem.dtcreate
FROM
  hc_member mem
INNER JOIN
  hc_file_member fmem
ON
  mem.ID = fmem.member_id
WHERE fmem.file_id = %d
  {$search_cond}
ORDER BY mem.ID ASC
LIMIT %d, %d
__SQL;

        // parameters for pagination
        $param[] = $offset;
        $param[] = $limit;

        // prepare sql
        $prepare = $wpdb->prepare( $sql, $param );

        // get results
        $res = $wpdb->get_results( $prepare );

        // get the total
        $total = $wpdb->get_results( 'SELECT FOUND_ROWS() as total' );

        return array( $res, (int)$total[0]->total );
    }

    /**
     * Check the value of search data
     * then if date then arrange the date
     *
     * @param  string $search search data
     * @return string $new    date search
     */
    private function check_search_date( $search ) {
        $final_date = '';
        $pattern = array(
            'a'  => '[0-9]{4}[\/|／|年|-](0[1-9]|1[0-2]|[1-9])[\/|／|月|-](0[1-9]|[1-2][0-9]|3[0-1]|[1-9])[日]',
            'a1' => '[0-9]{4}[\/|／|年|-](0[1-9]|1[0-2]|[1-9])[\/|／|月|-](0[1-9]|[1-2][0-9]|3[0-1]|[1-9])',
            'a2' => '[0-9]{4}[年](0[1-9]|1[0-2]|[1-9])[月]',
            'a3' => '[0-9]{4}[\/|／|年](0[1-9]|1[0-2]|[1-9])',
            'a4' => '[0-9]{4}[年]',
            'a5' => '[0-9]{4}',
        );

        // combine all regex
        $regex = '/'.implode( $pattern, '|' ).'/u';

        // check if character in string is has matched the regex
        $res = preg_match_all( $regex, $search, $match );

        // check if no error in matching
        if ( $res === false ) {
            return false;
        }

        // check if there is a match
        if ( !isset( $match[0][0] ) ) {
            return false;
        }

        // clean the date
        $clean_year  = strlen( $match[0][0] ) < 8 ? str_replace( '年', '', $match[0][0] ) : str_replace( '年', '-', $match[0][0] );
        $clean_month = strlen( $clean_year ) < 10 ? str_replace( '月', '', $clean_year ) : str_replace( '月', '-', $clean_year );
        $clean_day   = str_replace( '日', '', $clean_month );
        $strip       = str_replace( '/', '-', $clean_day );

        // change the month and day to two digit
        $convert    = explode( '-', $strip );
        $final_date = $convert[0];

        // convert the month to two digit
        if ( isset( $convert[1] ) && strlen( $convert[1] ) > 0 ) {
            $final_date.= '-'.sprintf( '%02d', $convert[1] );
        }

        // convert the day to two digit
        if ( isset( $convert[2] ) && strlen( $convert[2] ) > 0 ) {
            $final_date.= '-'.sprintf( '%02d', $convert[2] );
        }

        return $final_date;
    }

    /**
     * This function will now insert data
     * into the database from the input passed.
     *
     * @param  array       $param passed input data.
     * @return boolean|int        if insert has error then false else the last insert id.
     */
    private function insert_file( $param ) {
        global $wpdb;

        // unset other params first
        unset( $param['action'] );
        unset( $param['type'] );

        // add dt create to the parameter
        $param['dtcreate'] = $this->dtcreate();

        $sql = <<<__SQL
INSERT INTO hc_file (title, content, date_data, tag, file, dtcreate)
VALUES (%s, %s, %s, %s, %d, %s)
__SQL;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $param );

        try {
            // start the transaction
            $wpdb->query( 'START TRANSACTION' );
            // queue the query
            $res = $wpdb->query( $prepare );
            // commit the query
            $wpdb->query( 'COMMIT' );

            // check if unsuccessful insert
            if ( $res === false ) {
                $wpdb->query( 'ROLLBACK' );
                return false;
            }

            return $wpdb->insert_id;
        } catch ( \Exception $e ) {
            return false;
        }

        return false;
    }

    /**
     * Update the file's data
     *
     * @param  array   $param input data
     * @return boolean        true if successful update else false.
     */
    private function update_file( $param ) {
        global $wpdb;
        $file_id = $param['file_id'];

        // unset other params first
        unset( $param['action'] );
        unset( $param['type'] );
        unset( $param['file_id'] );

        $sql = <<<__SQL
UPDATE
  hc_file
SET
  title = %s
  , content = %s
  , date_data = %s
  , tag = %s
  , file = %d
  , dtupdate = %s
WHERE
  ID = %d
__SQL;

        // add dt create to the parameter
        $param['dtupdate'] = $this->dtcreate();

        // add the file_id to the parameter
        $param['ID']       = $file_id;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $param );

        try {
            // start the transaction
            $wpdb->query( 'START TRANSACTION' );
            // queue the query
            $res = $wpdb->query( $prepare );
            // commit the query
            $wpdb->query( 'COMMIT' );

            // check if unsuccessful insert
            if ( $res === false ) {
                $wpdb->query( 'ROLLBACK' );
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete the file
     *
     * This is just a soft delete. It will
     * just update the delete flag of the
     * data.
     *
     * @param  array   $param input data
     * @return boolean        true if update successfully else false.
     */
    private function delete_file( $param ) {
        global $wpdb;
        $file_id = $param['file_id'];

        // unset other params first
        unset( $param['action'] );
        unset( $param['type'] );
        unset( $param['file_id'] );

        // add dtupdate to the parameter
        $param['dtupdate']  = $this->dtcreate();
        $param['deleteflg'] = 1;
        $param['file_id']   = (int)$file_id;

        $sql = <<<__SQL
UPDATE
  hc_file
SET
  dtupdate = %s,
  deleteflg = %d
WHERE
  ID = %d
__SQL;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $param );

        try {
            // start the transaction
            $wpdb->query( 'START TRANSACTION' );
            // queue the query
            $res = $wpdb->query( $prepare );
            // commit the query
            $wpdb->query( 'COMMIT' );

            // check if unsuccessful insert
            if ( $res === false ) {
                $wpdb->query( 'ROLLBACK' );
                return false;
            }

            return true;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Register the members for each file
     *
     * @param  array       $param input value
     * @return boolean            false if unsuccessful insert otherwise true.
     */
    private function file_member_register( $param ) {
        global $wpdb;
        $values = $parameters = array();

        // get the value to be inserted and for the prepare statement
        foreach ( $param['mem_data'] as $value ) {
            $values[] = '(%d, %d, %s)';
            $parameters[] = array(
                $param['file_id']
                , $value
                , $this->dtcreate()
            );
        };

        // values to be inserted
        $parameters_merged = call_user_func_array( 'array_merge', $parameters );

        // prepared statement
        $val_prepare = implode( ", \n", $values );

        $sql = <<<__SQL
INSERT INTO hc_file_member (file_id, member_id, dtcreate)
VALUES {$val_prepare}
__SQL;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $parameters_merged );

        try {
            // start the transaction
            $wpdb->query( 'START TRANSACTION' );
            // queue the query
            $res = $wpdb->query( $prepare );
            // commit the query
            $wpdb->query( 'COMMIT' );

            // check if unsuccessful insert
            if ( $res === false ) {
                $wpdb->query( 'ROLLBACK' );
                return false;
            }

            return true;
        } catch ( \Exception $e ) {
            return false;
        }

        return false;
    }

    /**
     * Delete the connection between file
     * and member. This is hard delete
     * on the database.
     *
     * @param  array   $param input value
     * @return boolean        true if successful delete otherwise false.
     */
    private function file_member_delete( $param ) {
        global $wpdb;

        // member id prepare
        $mem_id_change = array_map( function( $val ) { return '%d'; }, $param['mem_data'] );
        $mem_id_prepare = implode( ', ', $mem_id_change );

        // sql
        $sql = <<<__SQL
DELETE FROM
  hc_file_member
WHERE
  file_id = %d
AND
  member_id IN ({$mem_id_prepare})
__SQL;

        // get parameters
        array_unshift( $param['mem_data'], $param['file_id'] );

        // prepare sql
        $prepare = $wpdb->prepare( $sql, $param['mem_data'] );

        try {
            // start the transaction
            $wpdb->query( 'START TRANSACTION' );
            // queue the query
            $res = $wpdb->query( $prepare );
            // commit the query
            $wpdb->query( 'COMMIT' );

            // check if unsuccessful delete
            if ( $res === false ) {
                $wpdb->query( 'ROLLBACK' );
                return false;
            }

            return true;
        } catch ( \Exception $e ) {
            return false;
        }

        return false;
    }

    /**
     * This function will check if passed
     * input data is empty. And then check
     * if input is valid for the database.
     * And check if member exists.
     *
     * @param  array   $param  passed input data.
     * @return boolean $res    error message returns if there's error otherwise true.
     */
    private function check_file( $param ) {
        // if type is deleting the file or file member registration
        if ( $param['type'] === 'delete'
            || $param['type'] === 'file_mem_register'
            || $param['type'] === 'file_mem_delete' ) {
            // check if file id passed exists
            if ( !$this->check_data_existence( $param['file_id'] ) ) {
                return hc_get_msg( 'ERR-05' );
            }

            return true;
        }

        // check if title is set
        if ( !isset( $param['title'] ) ) {
            return hc_get_msg( 'ERR-19' );
        }

        // check if content is set
        if ( !isset( $param['content'] ) ) {
            return hc_get_msg( 'ERR-20' );
        }

        // check if issue_date is set
        if ( !isset( $param['issue_date'] ) ) {
            return hc_get_msg( 'ERR-21' );
        }

        // check if tag is set
        if ( !isset( $param['tag'] ) ) {
            return hc_get_msg( 'ERR-22' );
        }

        // check if file is set
        if ( !isset( $param['file'] ) ) {
            return hc_get_msg( 'ERR-26' );
        }

        // check if image_path is set
        // if ( !isset( $param['image_path'] ) ) {
        //     return hc_get_msg( 'ERR-23' );
        // }

        // check if title is empty
        if ( isset( $param['title'] ) && strlen( $param['title'] ) < 1 ) {
            return hc_get_msg( 'ERR-19' );
        }

        // check if content is empty
        if ( isset( $param['content'] ) && strlen( $param['content'] ) < 1 ) {
            return hc_get_msg( 'ERR-20' );
        }

        // check if issue_date is empty
        if ( isset( $param['issue_date'] ) && strlen( $param['issue_date'] ) < 1 ) {
            return hc_get_msg( 'ERR-21' );
        }

        // separate the date for checking the date
        $month = date( 'm', strtotime( $param['issue_date'] ) );
        $year  = date( 'Y', strtotime( $param['issue_date'] ) );
        $day   = date( 'd', strtotime( $param['issue_date'] ) );
        if ( !checkdate( $month, $day, $year ) ) {
            return hc_get_msg( 'ERR-24' );
        }

        // check if tag is empty
        if ( isset( $param['tag'] ) && strlen( $param['tag'] ) < 1 ) {
            return hc_get_msg( 'ERR-22' );
        }

        // check if file is empty
        if ( isset( $param['file'] ) && strlen( $param['file'] ) < 1 ) {
            return hc_get_msg( 'ERR-26' );
        }

        // check if image_path is empty
        if ( isset( $param['image_path'] ) && strlen( $param['image_path'] ) < 1 ) {
            return hc_get_msg( 'ERR-23' );
        }

        return true;
    }

    /**
     * Current date time for updating
     * and inserting a member.
     *
     * @return string current date
     */
    private function dtcreate() {
        $timezone = new DateTime( null, new DateTimeZone( 'Asia/Tokyo' ) );
        return $timezone->format( 'Y-m-d' );
    }
}

