<?php

/**
 * This file holds the transaction
 * for the downloading of the files.
 */
class HC_Download
{
    /**
     * This function will be called from
     * js. This is for the downloading
     * of file
     *
     * @return array $res    This array contains the status and data.
     */
    public function download() {
        // get the post data
        $param = $_POST;

        // get the config data
        $hc_config = hc_config();

        // define the response first
        $res = $hc_config['response'];

        // check if param is array and not empty
        if ( empty( $param ) || !is_array( $param ) || !isset( $param['type'] ) ) {
            $res['msg'] = hc_get_msg( 'ERR-01' );

            return $res;
        }

        // sanitize the input data if there's no error in checking
        $this->sanitize_param( $param );

        // check the input data first
        $checked = $this->check_param( $param );
        if ( $checked !== true ) {
            $res['msg'] = $checked;
            return $res;
        }

        switch ( $param['type'] ) {
            // download a single file
            case 'download-single':
                // get the single file from file id
                $file = $this->get_single_file( $param['file_data'] );

                // check if file exists
                if ( $file === false ) {
                    $res['msg'] = hc_get_msg( 'ERR-32' );
                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'id'      => $file,
                    'admin_url' => admin_url(),
                    'home_url'  => home_url(),
                    'site_url'  => site_url()
                );

                return $res;
                break;

            // download multiple files
            case 'download-multiple':
                // get the file id from the file data
                $file_list = $this->get_multiple_file( $param );

                // check if file exists
                if ( $file_list === false ) {
                    $res['msg'] = hc_get_msg( 'ERR-35' );
                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'id'        => implode( ', ', $file_list ),
                    'admin_url' => admin_url(),
                    'home_url'  => home_url(),
                    'site_url'  => site_url()
                );

                return $res;
                break;

            // search using a keyword or tag
            case 'search':
                // get the data if found
                $search = $this->search( $param );

                // check if search is empty which means false
                if ( $search === false ) {
                    $res['msg'] = hc_get_msg( 'ERR-34' );

                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'searchdata' => $search[0],
                    'total'      => $search[1],
                    'numpg'      => empty( $param['numpg'] ) ? '' : $param['numpg'],
                    'limit'      => $param['limit'],
                    'home_url'   => home_url(),
                    'site_url'   => site_url(),
                    'loginid'    => $param['loginid'],
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
     * Sanitize all input param first.
     * This will clean all the input.
     * This function will also prepare
     * for hashing the password.
     *
     * @param  array $param Unsanitized param
     */
    private function sanitize_param( $param ) {
        foreach ( $param as $key => &$value ) {
            $param[$key] = sanitize_textarea_field( $value );
        }
    }

    /**
     * Check the parameter values
     *
     * @param  array          $param input value
     * @return boolean|string        true if no error else it returns the error message.
     */
    private function check_param( $param ) {
        // check if loginid exists
        if ( !$this->check_id( $param['loginid'], 'login' ) ) {
            return hc_get_msg( 'ERR-31' );
        }

        // in search type there's no need for checking more
        if ( $param['type'] === 'search' ) {
            return true;
        }

        // check the file data if empty
        if ( empty( $param['file_data'] ) ) {
            return hc_get_msg( 'ERR-32' );
        }

        // for single download only
        if ( $param['type'] === 'download-single' ) {
            // check each data
            $data_split = explode( '|', $param['file_data'] );
            if ( count( $data_split ) !== 2 ) {
                return hc_get_msg( 'ERR-33' );
            }

            // check if each data is empty
            if ( empty( $data_split[0] ) || empty( $data_split[1] ) ) {
                return hc_get_msg( 'ERR-32' );
            }

            // check if file id exists
            if ( !$this->check_id( $data_split[0], 'file' ) ) {
                return hc_get_msg( 'ERR-26' );
            }

            // check if there's a connection between the logind and the fileid
            if ( !$this->check_connection( $param ) ) {
                return hc_get_msg( 'ERR-32' );
            }
        }

        return true;
    }

    /**
     * Check the login id and
     * file id if it exists.
     *
     * @param  string  $id    loginid|fileid
     * @param  int     $type  login|file
     * @return boolean        true if exists else false.
     */
    private function check_id( $id, $type ) {
        global $wpdb;
        $db   = $type === 'login' ? 'hc_member' : 'hc_file';
        $cond = $type === 'login' ? 'loginid = %s' : 'ID = %d';

        $sql = <<<__SQL
SELECT
  COUNT(*) as cnt
FROM
  {$db}
WHERE
  {$cond}
  AND deleteflg = %d
__SQL;

        // add param for deleteflg
        $params = array( $id, 0 );

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $params );

        // get the result
        $res = $wpdb->get_results( $prepare );

        // check if data exists
        if ( (int)$res[0]->cnt > 0 ) {
            return true;
        }

        return false;
    }

    /**
     * Check if there's a connection between
     * the member and the file.
     *
     * @param  array   $param post value
     * @return boolean        true if connection exists else false.
     */
    private function check_connection( $param ) {
        global $wpdb;

        // unset parameters
        unset( $param['action'] );
        unset( $param['type'] );
        $split = explode( '|', $param['file_data'] );

        $sql = <<<__SQL
SELECT
  COUNT(*) as cnt
FROM
  hc_file_member file_mem
INNER JOIN hc_member mem
  ON file_mem.member_id = mem.ID
  AND mem.deleteflg = %d
WHERE
  file_mem.ID = %d
  AND file_mem.file_id = %d
  AND mem.loginid = %s;
__SQL;

        // add param for deleteflg
        $params = array(
            0
            , $split[1]
            , $split[0]
            , $param['loginid']
        );

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $params );

        // get the result
        $res = $wpdb->get_results( $prepare );

        // check if data exists
        if ( (int)$res[0]->cnt > 0 ) {
            return true;
        }

        return false;
    }

    /**
     * Get the single file
     *
     * @param  string      $param this contains the file id and file member id
     * @return boolean|int        false if there's error else it returns the file id.
     */
    private function get_single_file( $param ) {
        $split = explode( '|', $param );
        global $wpdb;

        $sql = <<<__SQL
SELECT
  file
FROM
  hc_file
WHERE
  ID = %d
  AND deleteflg = %d
__SQL;

        // get the params
        $params = array( $split[0], 0 );

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $params );

        // get the result
        $res = $wpdb->get_results( $prepare );

        // check if res is empty
        if ( empty( $res ) ) {
            return false;
        }

        // get the full path
        $file = get_attached_file( $res[0]->file );

        // check if file exists
        if ( !file_exists( $file ) ) {
            return false;
        }

        return (int)$res[0]->file;
    }

    /**
     * Get the file id for the multiple
     * download function.
     *
     * @param  array          $param input value
     * @return boolean|array         false if no pattern else array with data or empty array.
     */
    private function get_multiple_file( $param ) {
        global $wpdb;
        $list = array();

        $sql = <<<__SQL
SELECT
  file.file
FROM
  hc_file file
INNER JOIN
  hc_file_member file_mem
ON
  file.ID = file_mem.file_id
  AND file.deleteflg = %d
INNER JOIN
  hc_member mem
ON
  file_mem.member_id = mem.ID
  AND mem.deleteflg = %d
WHERE
  file.ID = %d
  AND file_mem.ID = %d
  AND mem.loginid = %s
__SQL;

        // get the values for parameters first
        foreach ( $param['file_data'] as $value) {
            $exp = explode( '|', $value );

            // if no pattern then return the data
            if ( count( $exp ) !== 3 ) {
                return false;
            }

            // get the parameters
            $params = array(
                0                   // file delete flag
                , 0                 // member delete flag
                , (int)$exp[0]    // file id
                , (int)$exp[1]    // file member id
                , $param['loginid'] // loginid
            );

            // prepare the sql
            $prepare = $wpdb->prepare( $sql, $params );

            // get the results
            $res = $wpdb->get_results( $prepare );

            // check if it is empty
            if ( !empty( $res ) ) {
                $list[] = (int)$res[0]->file;
            }
        }

        return $list;
    }

    /**
     * Search from keyword and tags
     *
     * @param  array         $param input parameter
     * @return array|booelan        false if data is empty otherwise return the found data.
     */
    private function search( $param ) {
        global $wpdb;
        $params = array( $param['loginid'], 0 );
        $pagination = $keywordcond = $tagcond = '';

        // for keyword search
        if ( !empty( $param['search'] ) ) {
            $date = $this->check_search_date( mb_convert_kana( $param['search'], 'a' ) );
            if ( $date !== false ) {
                $keywordcond .= "AND date_data LIKE '%%%s%%'";  // date
                $params[]    = $date;
            } else {
                $keywordcond .= "AND (CONCAT(title, content) LIKE '%%%s%%')";
                $params[]    = $param['search'];   // title
            }
        }

        // for the tag search
        if ( !empty( $param['tag'] ) ) {
            // for the condition
            // $tagcond .= "AND tag LIKE '%%%s%%'";
            $tagcond .= "AND tag = %s";

            // for the parameter
            $params[] = $param['tag'];
        }

        // for condition
        $pagination .= "LIMIT %d, %d";

        // for parameter
        if ( empty( $param['numpg'] ) ) {
            $params[] = 0;
        } else {
            $params[] = ( $param['numpg'] - 1 ) * $param['limit'];
        }
        $params[] = $param['limit'];

        $sql = <<<__SQL
SELECT SQL_CALC_FOUND_ROWS *,
  file.*
  , mem.loginid
  , file_mem.ID as file_mem_id
FROM
  hc_file file
INNER JOIN
  hc_file_member file_mem
ON file.ID = file_mem.file_id
INNER JOIN
  hc_member mem
ON file_mem.member_id = mem.ID
WHERE
  mem.loginid = %s
  AND file.deleteflg = %d
  {$keywordcond}
  {$tagcond}
ORDER BY file.ID DESC
{$pagination}
__SQL;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $params );

        // get the results
        $res = $wpdb->get_results( $prepare );

        // get the total items
        $total = $wpdb->get_results( 'SELECT FOUND_ROWS() as total' );

        // if empty then just return false
        if ( empty( $res ) ) {
            return false;
        }

        // to get the img url
        foreach ( $res as $key => $value ) {
            // $img = wp_get_attachment_image_src( $value->image_id, 'yama_member_thumbnail' );
            // $value->img_url = $img !== false ? wp_get_attachment_image( $value->image_id, 'yama_member_thumbnail' ): false;
            $value->img_url = hc_get_file_type( $value->file );
        }

        // return the list
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
}




