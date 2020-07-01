<?php

/**
 * This class holds the session
 * transactions for member
 * and file function.
 */
class HC_Session
{
    // public function start_session() {
    //     $secure = 'SECURE';

    //     // set a custom session name
    //     $session_nm = 'hc_session_id';

    //     // stop javascript from being able to access session id
    //     $httponly = true;

    //     // forces session to use only cookies
    //     if ( ini_set( 'session.use_only_cookies', 1 ) === FALSE ) {
    //         wp_redirect( site_url( '/' ).'member' );
    //         exit();
    //     }

    //     // get cookie params
    //     $cookie_params = session_get_cookie_params();
    //     var_dump($cookie_params);
    // }
    /**
     * Check the session
     *
     * @param  array   $param session value
     * @return boolean        false if there's error, otherwise true.
     */
    public function check_session( $param ) {
        // if empty and needed session is not set
        if ( empty( $param ) && !isset( $param['hc_status'] ) && !isset( $param['hc_loginid'] ) ) {
            return false;
        }

        // check for session timeout created
        if ( !isset( $param['created'] ) ) {
            return false;
        }

        // check if set but empty
        if ( isset( $param['hc_status'] ) && strlen( $param['hc_status'] ) < 1 ) {
            return false;
        }

        // check if set but empty
        if ( isset( $param['hc_loginid'] ) && strlen( $param['hc_loginid'] ) < 1 ) {
            return false;
        }

        // check if set but empty
        if ( isset( $param['created'] ) && strlen( $param['created'] ) < 1 ) {
            return false;
        }

        // if hc_status is not login
        if ( $param['hc_status'] !== 'login' ) {
            return false;
        }

        // check the value of loginid
        if ( $param['hc_loginid'] === 0 && is_int( $param['hc'] ) ) {
            return false;
        }

        // check the time session
        if ( isset( $param['created'] ) && ( time() - (int)$param['created'] > 3600 ) ) {
            session_unset();
            session_destroy();
            return false;
        }

        // if loginid does not exists
        if ( !$this->check_loginid( $param['hc_loginid'] ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check the session login id
     * if it exists.
     *
     * @param  string  $param loginid
     * @return boolean        true if exists else false.
     */
    private function check_loginid( $param ) {
        global $wpdb;

        $sql = <<<__SQL
SELECT
  COUNT(*) as cnt
FROM
  hc_member
WHERE
  loginid = %s
  AND deleteflg = %d
__SQL;

        // add param for deleteflg
        $params = array( $param, 0 );

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
}
