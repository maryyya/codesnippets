<?php

/**
 * This class contains
 * the register, update, and delete
 * for a member.
 */
class HC_Member
{
    /**
     * This function will be called from
     * js. This will check what kind of
     * action first, is is a insert, update,
     * or delete member. This will call check_member
     * and call whichever action/type was passed.
     *
     * @return array $res    This array contains the status and data.
     */
    public function member() {
        // get the config data
        $hc_config = hc_config();

        // get the post data
        $param = $_POST;

        // define the response first
        $res = $hc_config['response'];

        // just check if param is not empty
        if ( empty( $param ) || !is_array( $param ) || !isset( $param['type'] ) ) {
            $res['msg'] = hc_get_msg( 'ERR-01' );
            return $res;
        }

        // sanitize the input data if there's no error in checking
        $this->sanitize_param( $param );

        // check input data first
        $checked = $this->check_member( $param );

        if ( $checked !== true ) {
            $res['msg'] = $checked;
            return $res;
        }

        switch ( $param['type'] ) {
            // メンバー登録
            case 'member-register':
                $insert = $this->insert_member( $param );
                if ( $insert === false ) {
                    $res['msg'] = hc_get_msg( 'ERR-02' );

                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'hc_member_id' => $insert,
                    'admin_url'    => admin_url()
                );

                return $res;
                break;

            // メンバー編集
            case 'member-detail':
                $detail = $this->update_member( $param );
                if ( $detail === false ) {
                    $res['msg'] = hc_get_msg( 'ERR-03' );

                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'admin_url'    => admin_url()
                );

                return $res;
                break;

            // メンバー削除
            case 'member-delete':
                $delete = $this->delete_member( $param );
                if ( $delete === false ) {
                    $res['msg'] = hc_get_msg( 'ERR-04' );

                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'admin_url'    => admin_url()
                );

                return $res;
                break;

            default:
                $res = array(
                    'status' => 'ng',
                    'data'   => array(),
                    'msg'    => hc_get_msg( 'ERR-01' )
                );
                break;
        }

        return $res;
    }

    /**
     * Check if loginid and email already exists
     * in the registering of new member.
     *
     * @param  string|array $param  input loginid or member id
     * @param  string       $col    column on the database
     * @param  string       $type   variable type
     * @param  array        $cond   this condition is to check if id is not same but login id already exists.
     * @return boolean              False if it doesnot exists else true.
     */
    public function check_data_existence( $param, $col, $type, $cond = array() ) {
        global $wpdb;
        $condition = '';

        // add the condition
        if ( !empty( $cond ) && count( $cond ) === 2) {
            $condition = $cond[0];
            $param = array( $param, (int)$cond[1] );
        }

        $sql = <<<__SQL
SELECT
  COUNT(*) as cnt
FROM
  hc_member
WHERE
  {$col} = {$type}
  {$condition}
__SQL;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $param );

        // get the results
        $res     = $wpdb->get_results( $prepare );
        if ( (int)$res[0]->cnt > 0 ) {
            return true;
        }

        return false;
    }

    /**
     * Check if member is deleted
     *
     * @param  string $id member id
     * @return boolean    false if id is deleted else true
     */
    public function check_member_deleted( $id ) {
        global $wpdb;

         $sql = <<<__SQL
SELECT
  COUNT(*) as cnt
FROM
  hc_member
WHERE
  ID = %d
  AND deleteflg <> 1
__SQL;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, (int)$id );

        // get the results
        $res     = $wpdb->get_results( $prepare );

        if ( (int)$res[0]->cnt > 0 ) {
            return true;
        }

        return false;
    }

    /**
     * This function will get the
     * member detail.
     *
     * @param  int $mem_id    Member id from get.
     * @return boolean|array  If no data then return false, else it will return the array of data.
     */
    public function get_member_detail( $mem_id ) {
        global $wpdb;

        $sql = <<<__SQL
SELECT
  *
FROM
  hc_member
WHERE
  id = %d
  AND deleteflg <> 1
__SQL;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, (int)$mem_id );

        // get the results
        $res     = $wpdb->get_results( $prepare );

        if ( empty( $res ) ) {
            return false;
        }

        return $res[0];
    }

    /**
     * This will get all member list.
     * This is for 一覧ページ
     *
     * @param int    $offset  offset
     * @param int    $limit   limit
     * @param string $orderby ex loginid
     * @param string $order   asc or desc
     * @param string $search  search input
     * @return array $res     member list
     */
    public function get_member_list( $offset, $limit, $orderby, $order, $search ) {
        global $wpdb;
        $search_cond = '';
        $order_cond  = 'LENGTH(loginid) ASC';
        $param       = array();

        // condition for orderby
        if ( !empty( $orderby ) && !empty( $order ) ) {
            // get the orderby
            $orderby = sanitize_text_field( $orderby );
            $order   = sanitize_text_field( strtoupper( $order ) );

            // condition for orderby
            if ( $orderby === 'loginid' && $order === 'ASC' ) {
                $order_cond = 'LENGTH(loginid) ASC';
            } elseif ( $orderby === 'email' && $order === 'ASC' ) {
                $order_cond = 'LENGTH(email) ASC';
            } else {
                $order_cond = $orderby.' '.$order;
            }
        }

        // condition for search
        if ( !empty( $search ) ) {
            $date = $this->check_search_date( mb_convert_kana( $search, 'a' ) );
            if ( $date !== false ) {
                $search_cond .= "AND (CASE WHEN dtupdate IS NULL THEN dtcreate ELSE dtupdate END) LIKE '%%%s%%'";
                $param[]     = $date;
            } else {
                $search_cond .= "AND loginid LIKE '%%%s%%' \n OR email LIKE '%%%s%%'";
                $param[] = $search;
                $param[] = $search;
            }
        }

        $sql = <<<__SQL
SELECT
  SQL_CALC_FOUND_ROWS *
  , ID
  , loginid
  , email
  , CASE
    WHEN dtupdate IS NULL THEN dtcreate
    ELSE dtupdate
  END AS date
FROM
  hc_member
WHERE
  deleteflg <> 1
  {$search_cond}
ORDER BY {$order_cond}
LIMIT %d, %d
__SQL;

        // param for pagination
        $param[] = $offset;
        $param[] = $limit;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $param );
        
	// get the results
        $res     = $wpdb->get_results( $prepare );

        // get the total number of the results
        $total   = $wpdb->get_results( 'SELECT FOUND_ROWS() as total' );

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
     * Change the date into a
     * human time diff or japanese date.
     *
     * @param  string $date created date for the member
     * @return string       human date or japanese date
     */
    public function convert_japanese_date( $date ) {
        $new_day = '';
        $day = date( 'D', strtotime( $date ) );
        $new_date = date( 'Y年m月d日', strtotime( $date ) );

        if ( $day === 'Mon' ) {
            $new_day = '月';
        } elseif ( $day === 'Tue' ) {
            $new_day = '火';
        } elseif ( $day === 'Wed' ) {
            $new_day = '水';
        } elseif ( $day === 'Thu' ) {
            $new_day = '木';
        } elseif ( $day === 'Fri' ) {
            $new_day = '金';
        } elseif ( $day === 'Sat' ) {
            $new_day = '土';
        } elseif ( $day === 'Sun' ) {
            $new_day = '日';
        }

        return $new_date;
        // return $new_date . '（'.$new_day.'）';
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
            // sanitize for the email
            if ( $key === 'email' ) {
                $param[$key] = sanitize_email( $value );
                continue;
            }

            // sanitize all the params according to text
            $param[$key] = sanitize_text_field( $value );
        }
    }

    /**
     * This function will check if passed
     * input data is empty. And then check
     * if input is valid for the database.
     * And check if member exists
     *
     * @param  array   $param  passed input data.
     * @return boolean $res    false if there's error otherwise true.
     */
    private function check_member( $param ) {
        // This is for member delete action only
        if ( $param['type'] === 'member-delete' ) {
            // check if id exists
            $checked_memid = $this->check_data_existence( $param['memid'], 'ID', '%d' );
            if ( !$checked_memid ) {
                return hc_get_msg( 'ERR-05' );
            }

            // check if id is deleted
            $checked_delete_memid = $this->check_member_deleted( $param['memid'] );
            if ( !$checked_delete_memid ) {
                return hc_get_msg( 'ERR-06' );
            }

            return true;
        }

        // check if loginid is empty
        if ( !isset( $param['loginid'] ) || strlen( $param['loginid'] ) < 1 ) {
            return hc_get_msg( 'ERR-07' );
        }

        // check if email is empty
        if ( !isset( $param['email'] ) || strlen( $param['email'] ) < 1 ) {
            return hc_get_msg( 'ERR-08' );
        }

        // check if password is empty
        if ( !isset( $param['password'] ) || strlen( $param['password'] ) < 1 ) {
            return hc_get_msg( 'ERR-09' );
        }

        // check if email is valid
        if ( is_email( $param['email'] ) === false ) {
            return hc_get_msg( 'ERR-10' );
        }

        // if password is lesser than 8
        if ( strlen( $param['password'] ) < 8 ) {
            return hc_get_msg( 'ERR-11' );
        }

        // if password is alphanumeric
        if ( !preg_match( '/^[0-9A-Za-z]+$/', $param['password'] ) ) {
            return hc_get_msg( 'ERR-12' );
        }

        // This is for member register only. Not applicable for detail page.
        if ( $param['type'] === 'member-register' ) {
            // if loginid already exists
            $checked_loginid = $this->check_data_existence( $param['loginid'], 'loginid', '%s' );

            if ( $checked_loginid ) {
                return hc_get_msg( 'ERR-13', $param['loginid'] );
            }

            // if email already exists
            $checked_email = $this->check_data_existence( $param['email'], 'email', '%s' );
            if ( $checked_email ) {
                return hc_get_msg( 'ERR-14', $param['email'] );
            }
        }

        // This is for member detail action only.
        if ( $param['type'] === 'member-detail' ) {
            // check if id passed exists in the database.
            $checked_memid = $this->check_data_existence( $param['memid'], 'ID', '%d' );
            if ( !$checked_memid ) {
                return hc_get_msg( 'ERR-05' );
            }

            // check if new loginid is same with other id
            $cond = array( 'AND ID <> %d', $param['memid'] );
            $checked_loginid = $this->check_data_existence( $param['loginid'], 'loginid', '%s', $cond );
            if ( $checked_loginid ) {
                return hc_get_msg( 'ERR-13', $param['loginid'] );
            }

            // check if new email is same with other id
            $cond_email = array( 'AND ID <> %d', $param['memid'] );
            $checked_email = $this->check_data_existence( $param['email'], 'email', '%s', $cond_email );
            if ( $checked_email ) {
                return hc_get_msg( 'ERR-14', $param['email'] );
            }

             // check if id is deleted
            $checked_delete_memid = $this->check_member_deleted( $param['memid'] );
            if ( !$checked_delete_memid ) {
                return hc_get_msg( 'ERR-06' );
            }
        }

        return true;
    }

    /**
     * This function will now insert data
     * into the database from the input passed.
     *
     * @param  array       $param passed input data.
     * @return boolean|int        if insert has error then false else the last insert id.
     */
    private function insert_member( $param ) {
        global $wpdb;

        // unset other params first
        unset( $param['action'] );
        unset( $param['type'] );

        // add dt create to the parameter
        $param['dtcreate'] = $this->dtcreate();

        $sql = <<<__SQL
INSERT INTO hc_member (loginid, email, password, dtcreate)
VALUES (%s, %s, %s, %s)
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
     * Update the detail passed from
     * the database.
     *
     * @param  array   $param passed input data
     * @return boolean $res   if update has error then false else true.
     */
    private function update_member( $param ) {
        global $wpdb;
        $memid = $param['memid'];

        // unset other params first
        unset( $param['action'] );
        unset( $param['type'] );
        unset( $param['memid'] );

        // add dtupdate to the parameter
        $param['dtupdate'] = $this->dtcreate();
        $param['memid']    = (int)$memid;

        $sql = <<<__SQL
UPDATE
  hc_member
SET
  loginid = %s,
  email = %s,
  password = %s,
  dtupdate = %s
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
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete the user. This one will only change the
     * status. Not deleting the user entirely.
     *
     * @param  array   $param passed input data
     * @return boolean $res   if delete has error then false else true.
     */
    private function delete_member( $param ) {
        global $wpdb;
        $memid = $param['memid'];

        // unset other params first
        unset( $param['action'] );
        unset( $param['type'] );
        unset( $param['memid'] );

        // add dtupdate to the parameter
        $param['dtupdate']  = $this->dtcreate();
        $param['deleteflg'] = 1;
        $param['memid']     = (int)$memid;

        $sql = <<<__SQL
UPDATE
  hc_member
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
