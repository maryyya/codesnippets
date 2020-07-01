<?php

require_once('../require.php');

/**
 * To check for the account
 */
class Check_Account
{
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
     * Constructor
     */
    public function __construct() {
        require_once('mysql.php');
    }

    /**
     * Execute
     *
     * @return json
     */
    public function login_check() {
        if(!isset($_SESSION)) {
            session_start();
        };

        $param = $_POST;
        $response = $this->response;

        // check
        $data = $this->check($param);
        if (!$data) {
            $response['msg'] = 'check error';
            $response['status'] = 'ng';
            echo json_encode($response);
            return;
        }

        $_SESSION['DANTAI_ID']          = $data['DANTAI_ID'];
        $_SESSION['DANTAI_CD']          = $data['DANTAI_CD'];
        $_SESSION['DANTAI_MEI']         = $data['DANTAI_MEI'];
        $_SESSION['DANTAI_HOJINKAKU']   = $data['DANTAI_HOJINKAKU'];
        $_SESSION['SESSION_START_TIME'] = time();
        echo json_encode($response);
    }

    /**
     * Check the dantai code and password
     *
     * @param array  $post_param post dantai code and password
     * @return array|boolean $data  id or false if no data
     */
    private function check($post_param) {
        // 団体コード
        if (empty($post_param['dantaicd'])) {
            return false;
        }

        // パスワード
        if (empty($post_param['pass'])) {
            return false;
        }

        preg_match('/^[0-9]{0,15}$/', $post_param['dantaicd'], $dantaicd_match);
        if (empty($dantaicd_match)) {
            return false;
        }

        // if there are new lines or spaces
        if ((strstr($post_param['dantaicd'], ' ') !== false)
        || (strstr($post_param['dantaicd'], "\n") !== false)
        || (strstr($post_param['dantaicd'], "\r\n") !== false)
        || (strstr($post_param['dantaicd'], PHP_EOL) !== false)
        || (strstr($post_param['pass'], ' ') !== false)
        || (strstr($post_param['pass'], "\n") !== false)
        || (strstr($post_param['pass'], "\r\n") !== false)
        || (strstr($post_param['pass'], PHP_EOL) !== false)
        ) {
            return false;
        }

        // remove trim
        if (($post_param['dantaicd'] != trim($post_param['dantaicd']))
        || ($post_param['pass'] != trim($post_param['pass']))) {
            return false;
        }

        // if there are html entities
        if (($post_param['dantaicd'] != htmlentities(strip_tags($post_param['dantaicd']), ENT_QUOTES, 'UTF-8'))
        || ($post_param['pass'] != htmlentities(strip_tags($post_param['pass']), ENT_QUOTES, 'UTF-8'))) {
            return false;
        }

        $param = array(
            'dantai_code' => $this->clean_string($post_param['dantaicd']),
            'password'    => $this->clean_string($post_param['pass'])
        );
        $sql = 'SELECT dantai.ID, dantai.dantai_code, dantai.dantaimei, kubun.value as hojinkaku FROM dantai';
        $sql.= ' LEFT JOIN kubun ON kubun.code = dantai.hojinkakucode';
        $sql.= ' WHERE dantai_code = ? AND password = ?';

        $db = new Mysql;
        $res = $db->select($sql, $param);

        return empty($res)?false:array(
            'DANTAI_ID'        => $res[0]['ID'],
            'DANTAI_CD'        => $res[0]['dantai_code'],
            'DANTAI_MEI'       => $res[0]['dantaimei'],
            'DANTAI_HOJINKAKU' => $res[0]['hojinkaku'],
        );
    }

    /**
     * Clean everything before insert
     *
     * @param string $param
     * @return string $str cleaned param
     */
    public function clean_string($param) {
        $str = mb_convert_encoding(strip_tags($param), 'UTF-8', 'UTF-8');
        $str = htmlentities($str, ENT_QUOTES, 'UTF-8');
        return $str;
    }
}
