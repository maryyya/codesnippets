<?php

/**
 * This class all the transaction
 * for the login in the front.
 */
class HC_Login
{
    /**
     * This is the login function
     * This calls the sanitizing
     * and validating of the paramenter.
     *
     * @return array $res Contains the status and the data.
     */
    public function login() {
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
            case 'auth':
                // check if authentication is successful.
                $auth = $this->auth( $param );
                if ( $auth === false ) {
                    $res['msg'] = hc_get_msg( 'ERR-30' );
                    return $res;
                }

                $res['status'] = 'ok';
                $res['data']   = array(
                    'id'        => $auth,
                    'admin_url' => admin_url(),
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
     * Authenticate the input value.
     * Check if loginid and password
     * exist.
     *
     * @param  array       $param input value
     * @return boolean|int        member id if authentication success otherwise false.
     */
    private function auth( $param ) {
        global $wpdb;

        // unset parameters that are unnecessary
        unset( $param['type'] );
        unset( $param['action'] );

        $sql = <<<__SQL
SELECT
  COUNT(*) as cnt
  , loginid
FROM
  hc_member
WHERE
  loginid = %s
AND
  password = %s
AND
  deleteflg = %d
__SQL;

        // set the deleteflg
        $param[] = 0;

        // prepare the sql
        $prepare = $wpdb->prepare( $sql, $param );

        // get the result
        $res = $wpdb->get_results( $prepare );

        // check if data exists
        if ( (int)$res[0]->cnt > 0 ) {
            // sessions start
            session_start();

            // add login status
            $_SESSION['hc_status'] = 'login';

            // add the login id
            $_SESSION['hc_loginid'] = $res[0]->loginid;

            // session time
            $_SESSION['created'] = time();

            return $res[0]->ID;
        }

        return false;
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
     * This function will check the
     * input data. If it is empty.
     *
     * @param  array $param   input value
     * @return boolean|string error message if there's error otherwise true.
     */
    private function check_param( $param ) {
        // check if memid is empty
        if ( !isset( $param['mem_id'] ) || strlen( $param['mem_id'] ) < 1 ) {
            return hc_get_msg( 'ERR-28' );
        }

        // check if password is empty
        if ( !isset( $param['password'] ) || strlen( $param['password'] ) < 1 ) {
            return hc_get_msg( 'ERR-29' );
        }

        // if password is lesser than 8
        if ( strlen( $param['password'] ) < 8 ) {
            return hc_get_msg( 'ERR-30' );
        }

        // if value is zero
        if ( $param['mem_id'] === 0 || $param['password'] === 0 ) {
            return hc_get_msg( 'ERR-30' );
        }

        return true;
    }
}
