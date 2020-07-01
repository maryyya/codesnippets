<?php

require_once('../require.php');

/**
 * This class is to insert or update
 * data from the db and to send
 * email.
 */
class Send
{
    /**
     * This is used fromt the input
     * page which is like kaigi_form_1_xxx.
     * This constant is used in insert_update_data().
     */
    const FORM_NAME_FORMAT = 'kaitou_form_';

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
     * Kubun equivalent.
     * The alphabets are in
     * the db as well.
     *
     * @var array
     */
    private $kubun_equivalent = array(
        'hojinkakucode' => 'A',   // 法人格 / 団体名
        'toshi_code'    => 'B',   // 都道府県名
        'area_code'     => 'C',   // 地域
        'dantai_kbn'    => 'D',   // 団体区分
        'gyojinaiyo'    => 'E',   // 行事内容
        'kaisaikaijo'   => 'F',   // 開催会場
        'kaisaijiki'    => 'G',   // 開催時期
        'kaisaibasho'   => 'H',   // 開催場所
        'ketteijiki'    => 'I',   // 決定時期
        // 'sankashasu2'   => 'J',   // 参加者数集計
    );

    /**
     * This are int datatypes in the database.
     * Cause from the post to sending the
     * db into the database, the values are
     * empty string thus when inserting
     * then the values becomes zero since
     * the datatypes are int.
     *
     * @var array
     */
    private $int_cols = array(
        'dantai_id',
        'sankashasu',
        'gaikokujinsu',
        'sankakokusu',
        'sokai_hole',
        'recep_hole',
        'shitsusu',
        'hiroma',
        'yosan',
        'uchikifukin',
    );

    /**
     * These are checkboxes and one radion
     * in the front. It means that when these
     * checkboxes or radio aren't checked
     * then the values would still be the same.
     *
     * @var array
     */
    private $checkbox_radio = array(
        'sankashasu2',
        'gyojinaiyo',
        'kaisaikaijo',
        'kaisaijiki',
        'kaisaibasho',
        'ketteijiki',
    );

    /**
     * 地域コード
     *
     * @var array
     */
    private $area_equiv = array(
        '0' => array(0),
        '1' => array(1, 2, 3, 4, 5, 6, 7),
        '2' => array(8, 9, 10, 11, 12, 13, 14, 15),
        '3' => array(16, 17, 18),
        '4' => array(19, 20, 21, 22, 23, 24),
        '5' => array(25, 26, 27, 28, 29, 30),
        '6' => array(31, 32, 33, 34, 35),
        '7' => array(36, 37, 38, 39),
        '8' => array(40, 41, 42, 43, 44, 45, 46, 47),
        '9' => array(99),
        'A' => array('AA'),
        'B' => array('BB'),
        'C' => array('CC'),
    );

    /**
     * サンキューメール
     *
     * @var string
     */
    private $mail_msg = <<<__MSG
{name}御中

__MSG;

    /**
     * 題名
     *
     * @var string
     */
    private $mail_subject = '';

    /**
     * 送信元
     *
     * @var string
     */
    private $from = '';

    /**
     * Constructor
     */
    public function __construct() {
        require_once('mysql.php');
        $this->db = new Mysql;

        require_once('clean_params.php');
        $this->clean = new Clean_Param;

        require_once('PepperMail/PepperMail.php');
    }

    /**
     * Execute functions such
     * as insert or updating data
     * and send email.
     *
     * @return json data
     */
    public function exec() {
        $param = $this->clean->clean_all('', $_POST, 'database');
        $response = $this->response;

        // send data
        if (!$this->send_data($param)) {
            $response['status'] = 'ng';
            $response['msg'] = 'Error inserting or updating data.';
            echo json_encode($response);
            return;
        }

        // get param admin which is 1
        $get_param_admin_val = 0;
        if (!empty($param['get_param_admin_val'])) {
            $get_param_admin_val = (int)$param['get_param_admin_val'] !== 0?1:0;
        }


// delete
// $param['e_mail'] = 'padon.mary@gmail.com';
// $param['e_mail'] = 'mary.angeleque.padon@pripress.co.jp';

        if (!empty($param['e_mail']) && $get_param_admin_val == 0) {
            $hojinkaku = !empty($_SESSION['DANTAI_HOJINKAKU'])?$_SESSION['DANTAI_HOJINKAKU']:'';
            $dantai_mei = empty($param['dantaimei'])?'':$param['dantaimei'];

            // send mail
            if (!$this->send_mail($param['e_mail'], $hojinkaku.$dantai_mei)) {
                $response['status'] = 'ng';
                $response['msg'] = 'Error sending mail.';
                echo json_encode($response);
                return;
            }
        }

        echo json_encode($response);
        return;
    }

    /**
     * This is to insert data or
     * update data.
     *
     * @param array $param POST data
     * @return boolean true if no error inserting or updating data
     */
    private function send_data($param) {

        try {
            $this->db->conn()->query('BEGIN');
            $res = $this->insert_update($param);
            $this->db->conn()->query('COMMIT');
        } catch (\Exception $e) {
            $res = false;
            $this->db->log->write('ERROR MESSAGE:'.$e->getMessage(), __LINE__, 'send.php');
        }

        return $res;
    }

    /**
     * Insert update 団体と回答データ
     *
     * @param array $param post param
     * @return boolean
     */
    private function insert_update($param) {
        unset($param['get_param_admin']);
        unset($param['get_param_admin_val']);
        $dantai = $kaitou = array();
        $all_kbn = $this->get_all_kubun();

        $tel = $fax = '';
        if (isset($param['tel_1']) && strlen($param['tel_1']) > 0) {
            $tel.= $param['tel_1'].'-';
        }

        if (isset($param['tel_2']) && strlen($param['tel_2']) > 0) {
            $tel.= $param['tel_2'].'-';
        }

        if (isset($param['tel_3']) && strlen($param['tel_3']) > 0) {
            $tel.= $param['tel_3'];
        }

        if (isset($param['fax_1']) && strlen($param['fax_1']) > 0) {
            $fax.= $param['fax_1'].'-';
        }

        if (isset($param['fax_2']) && strlen($param['fax_2']) > 0) {
            $fax.= $param['fax_2'].'-';
        }

        if (isset($param['fax_3']) && strlen($param['fax_3']) > 0) {
            $fax.= $param['fax_3'];
        }

        if (!empty($tel)) {
            $dantai['tel'] = $tel;
        }

        if (!empty($fax)) {
            $dantai['fax'] = $fax;
        }

        if (!empty($param['dantai_kbn']) && !empty($param['dantai_kbn2'])) {
            $param['dantai_kbn2'] = $param['dantai_kbn'].'.'.$param['dantai_kbn2'];
        }

        if (!isset($param['sasshi'])) {
            $param['sasshi'] = NULL;
        }

        if (!isset($param['sasshi_support'])) {
            $param['sasshi_support'] = NULL;
        }

        foreach ($param as $key => $val) {
            if (strpos($key, self::FORM_NAME_FORMAT) === false) {
                if ($key === 'moduleurl'    ||
                    $key === 'siteurl'      ||
                    $key === 'page_items'   ||
                    $key === 'kaitou_items' ||
                    $key === 'page_type'    ||
                    $key === 'tel_1' ||
                    $key === 'tel_2' ||
                    $key === 'tel_3' ||
                    $key === 'fax_1' ||
                    $key === 'fax_2' ||
                    $key === 'fax_3' ||
                    $key === 'dantai_cd'
                ) {
                    continue;
                }

                if ($key === 'sasshi') {
                    $dantai[$key] = !empty($val)?str_replace(', ', "\n", $val):null;
                } else if ($key === 'sasshi_support' && !empty($val)) {
                    $dantai[$key] = (int)$val;
                } else {
                    $dantai[$key] = $val;
                }
                continue;
            }

            preg_match('/'.self::FORM_NAME_FORMAT.'\d{1,2}/', $key, $matches);
            if (empty($matches[0])) {
                // add log
                continue;
            }

            $recordkey = str_replace(self::FORM_NAME_FORMAT, '', $matches[0]);
            $recordkeynm = str_replace(self::FORM_NAME_FORMAT.$recordkey.'_', '', $key);

            $kaitou[(int)$recordkey][$recordkeynm] = $val;
        }

        // 団体データを更新
        $update_dantai_sql = 'UPDATE dantai SET '.$this->create_update_sql($dantai)." WHERE id = ?;";
        $dantai['id'] = $_SESSION['DANTAI_ID'];
        $update_dantai_res = $this->db->update($update_dantai_sql, $dantai);
        if (!$update_dantai_res) {
            $this->db->log->write('ERROR MESSAGE:団体データの更新はエラーがあります。', __LINE__, 'send.php');
            return false;
        }


        // 回答データ更新と追加
        if (!empty($kaitou)) {
            foreach($kaitou as $key => $items) {
                $items['data_kbn'] = DATA_KBN;

                $kaitou_param = $items;

                // to put null values whenever the value is empty
                foreach ($this->checkbox_radio as $checkbox_radio_val) {
                    if (!isset($items[$checkbox_radio_val])) {
                        $kaitou_param[$checkbox_radio_val] = NULL;
                    }
                }

                // 2017年
                if (isset($items['code_2yearsago']) && strlen($items['code_2yearsago']) > 0) {
                    $kaitou_param['code_2yearsago']   = $items['code_2yearsago'];
                    $kaitou_param['area_2yearsago']   = $this->get_area_code($items['code_2yearsago']);
                    $kaitou_param['name_2yearsago']   = $all_kbn[$this->kubun_equivalent['toshi_code']][$items['code_2yearsago']];
                } else {
                    $kaitou_param['area_2yearsago'] = $kaitou_param['name_2yearsago'] = NULL;
                }

                // 2018年
                if (isset($items['code_1yearago']) && strlen($items['code_1yearago']) > 0) {
                    $kaitou_param['code_1yearago']   = $items['code_1yearago'];
                    $kaitou_param['area_1yearago']   = $this->get_area_code($items['code_1yearago']);
                    $kaitou_param['name_1yearago']   = $all_kbn[$this->kubun_equivalent['toshi_code']][$items['code_1yearago']];
                } else {
                    $kaitou_param['area_1yearago'] = $kaitou_param['name_1yearago'] = NULL;
                }

                // 2019年
                if (isset($items['code_thisyear']) && strlen($items['code_thisyear']) > 0) {
                    $kaitou_param['code_thisyear']   = $items['code_thisyear'];
                    $kaitou_param['area_thisyear']   = $this->get_area_code($items['code_thisyear']);
                    $kaitou_param['name_thisyear']   = $all_kbn[$this->kubun_equivalent['toshi_code']][$items['code_thisyear']];
                } else {
                    $kaitou_param['area_thisyear'] = $kaitou_param['name_thisyear'] = NULL;
                }

                // 2020年
                if (isset($items['code_1yearlater']) && strlen($items['code_1yearlater']) > 0) {
                    $kaitou_param['code_1yearlater']   = $items['code_1yearlater'];
                    $kaitou_param['area_1yearlater']   = $this->get_area_code($items['code_1yearlater']);
                    $kaitou_param['name_1yearlater']   = $all_kbn[$this->kubun_equivalent['toshi_code']][$items['code_1yearlater']];
                } else {
                    $kaitou_param['area_1yearlater'] = $kaitou_param['name_1yearlater'] = NULL;
                }

                // 2021年
                if (isset($items['code_2yearslater']) && strlen($items['code_2yearslater']) > 0) {
                    $kaitou_param['code_2yearslater']   = $items['code_2yearslater'];
                    $kaitou_param['area_2yearslater']   = $this->get_area_code($items['code_2yearslater']);
                    $kaitou_param['name_2yearslater']   = $all_kbn[$this->kubun_equivalent['toshi_code']][$items['code_2yearslater']];
                } else {
                    $kaitou_param['area_2yearslater'] = $kaitou_param['name_2yearslater'] = NULL;
                }

                // 2022年
                if (isset($items['code_3yearslater']) && strlen($items['code_3yearslater']) > 0) {
                    $kaitou_param['code_3yearslater']   = $items['code_3yearslater'];
                    $kaitou_param['area_3yearslater']   = $this->get_area_code($items['code_3yearslater']);
                    $kaitou_param['name_3yearslater']   = $all_kbn[$this->kubun_equivalent['toshi_code']][$items['code_3yearslater']];
                } else {
                    $kaitou_param['area_3yearslater'] = $kaitou_param['name_3yearslater'] = NULL;
                }

                // 2023年
                if (isset($items['code_4yearslater']) && strlen($items['code_4yearslater']) > 0) {
                    $kaitou_param['code_4yearslater']   = $items['code_4yearslater'];
                    $kaitou_param['area_4yearslater']   = $this->get_area_code($items['code_4yearslater']);
                    $kaitou_param['name_4yearslater']   = $all_kbn[$this->kubun_equivalent['toshi_code']][$items['code_4yearslater']];
                } else {
                    $kaitou_param['area_4yearslater'] = $kaitou_param['name_4yearslater'] = NULL;
                }

                // 2024年
                if (isset($items['code_5yearslater']) && strlen($items['code_5yearslater']) > 0) {
                    $kaitou_param['code_5yearslater']   = $items['code_5yearslater'];
                    $kaitou_param['area_5yearslater']   = $this->get_area_code($items['code_5yearslater']);
                    $kaitou_param['name_5yearslater']   = $all_kbn[$this->kubun_equivalent['toshi_code']][$items['code_5yearslater']];
                } else {
                    $kaitou_param['area_5yearslater'] = $kaitou_param['name_5yearslater'] = NULL;
                }

                if (is_array($items) && !empty($items)) {
                    foreach($items as $key_item => $item) {
                        // for the kubun values
                        if (in_array($key_item, array_keys($this->kubun_equivalent))) {
                            $explode_items = explode(', ', $item);
                            if (is_array($explode_items)) {
                                $explode_item_val_arr = array();
                                foreach($explode_items as $explode_item) {
                                    $explode_item_val_arr[] = $all_kbn[$this->kubun_equivalent[$key_item]][$explode_item];
                                }
                                $explode_item_val = implode("\n", $explode_item_val_arr);
                            } else {
                                $explode_item_val = $all_kbn[$this->kubun_equivalent[$key_item]][$explode_items];
                            }
                            $kaitou_param[$key_item] = $explode_item_val;
                            continue;
                        }

                        // for int datatypes in the db
                        if (in_array($key_item, $this->int_cols)) {
                            $kaitou_param[$key_item] = isset($item) && strlen($item) > 0?(int)$item:null;
                        } else {
                            $kaitou_param[$key_item] = $item;
                        }
                    }
                }

                // insert
                if (empty($items['kaitou_id'])) {
                    unset($kaitou_param['kaitou_id']);
                    $last_no = $this->get_last_no();
                    $new_no = sprintf('%04d', (int)$last_no[0]['no'] + 1);
                    $last_dantai_kaitou = $this->get_last_dantai_kaitou($dantai['id']);
                    if (isset($last_dantai_kaitou[0])) {
                        $exploded_serial_code = explode('-', $last_dantai_kaitou[0]['serial_code']);
                        $last_dantai_kaitou_serial_code = isset($exploded_serial_code[1])?$exploded_serial_code[1]+1:1;
                    } else {
                        $last_dantai_kaitou_serial_code = 1;
                    }

                    $kaitou_param['no']            = $new_no;
                    $kaitou_param['data_kbn']      = DATA_KBN;
                    $kaitou_param['dantai_id']     = $dantai['id'];
                    $kaitou_param['serial_code']   = $param['dantai_cd'].'-'.$last_dantai_kaitou_serial_code;
                    $kaitou_param['shin_kanri_no'] = SHIN_KANRI_NO.'-'.$new_no;

                    $kaitou_sql = 'INSERT INTO kaitou('.implode(', ', array_keys($kaitou_param)).') VALUES ('.$this->create_insert_sql($kaitou_param).')';
                    $kaitou_res = $this->db->insert($kaitou_sql, $kaitou_param);
                    if (!$kaitou_res) {
                        $this->db->log->write('ERROR MESSAGE:回答のデータ追加「'.$kaitou_param['convention_mei'].'」はエラーがあります。', __LINE__, 'send.php');
                        return false;
                    }
                } else {
                    // update
                    if(!$this->check_kaitou_id($items['kaitou_id'])) {
                        $this->db->log->write('ERROR MESSAGE:団体IDが存在しません。', __LINE__, 'send.php');
                        continue;
                    }

                    unset($kaitou_param['kaitou_id']);
                    $kaitou_sql = 'UPDATE kaitou SET '.$this->create_update_sql($kaitou_param)." WHERE id = ?;";
                    $kaitou_param['id'] = $items['kaitou_id'];
                    $kaitou_res = $this->db->update($kaitou_sql, $kaitou_param);
                    if (!$kaitou_res) {
                        $this->db->log->write('ERROR MESSAGE:回答のデータ変更「'.$items['kaitou_id'].'」はエラーがあります。', __LINE__, 'send.php');
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * To make question marks
     *
     * @param array $param
     * @return string
     */
    private function create_insert_sql($param) {
        $ques = array();
        foreach ($param as $key => $value) {
            $ques[] = '?';
        }

        return implode(', ', $ques);
    }

    /**
     * Get the 地域code from 都市コード
     *
     * @param string $toshi_code
     * @return NULL|int empty then null else int
     */
    private function get_area_code($toshi_code) {
        $area_code_list = $this->area_equiv;
        $equiv = array();
        foreach($area_code_list as $key => $toshi_code_list) {
            if (in_array($toshi_code, $toshi_code_list)) {
                $equiv[] = $key;
            }
        }

        return isset($equiv[0])?$equiv[0]:NULL;
    }

    /**
     * Just create a simple
     * update sql query.
     * From array it will turn
     * into string.
     *
     * @param array $arr
     * @return string
     */
    private function create_update_sql($arr) {
        return implode(" = ?, \n", array_keys($arr)).' = ?';
    }

    /**
     * Check the kaitou id before updating
     * the data
     *
     * @param int $kaitou_id
     * @return boolean false if not exists otherwise true
     */
    private function check_kaitou_id($kaitou_id) {
        $sql = 'SELECT count(id) as cnt FROM kaitou WHERE id = ?';
        $res = $this->db->select($sql, array('id' => (int)$kaitou_id));
        return $res[0]['cnt']>0?true:0;
    }

    /**
     * Get the kubun code
     *
     * @return array
     */
    private function get_all_kubun() {
        $res = $this->db->select('SELECT * FROM kubun WHERE delflag = ?', array('delflag' => 0));
        $data = array();
        foreach($res as $val) {
            $data[$val['type']][$val['code']] = $val['value'];
        }
        return $data;
    }

    /**
     * Get the last no from
     * kaitou table
     *
     * @return array
     */
    private function get_last_no() {
        $sql = 'SELECT no, shin_kanri_no FROM kaitou ORDER BY no * 1 DESC LIMIT 1';
        $res = $this->db->select_res_arr($sql);
        return $res;
    }

    /**
     * Get the last kaitou with
     * dantai id
     *
     * @param int $dantai_id
     * @return array
     */
    private function get_last_dantai_kaitou($dantai_id) {
        $sql = 'SELECT dantai_id, serial_code FROM kaitou WHERE dantai_id = ? ORDER BY serial_code DESC LIMIT 1';
        $res = $this->db->select($sql, array('dantai_id' => $dantai_id));
        return $res;
    }

    /**
     * Sending thank you mail.
     *
     * @param array $to
     * @param array $name 役職名
     * @return boolean true if no error sending mail else false
     */
    private function send_mail($to, $name) {
        // 言語設定
        mb_language('Japanese');
        // 内部エンコーディング
        mb_internal_encoding('UTF-8') ;

        $mail = new PepperMail();

        try {
            // メール設定
            $mail_config_params = array(
               'host'        => MAIL_HOST,
               'username'    => MAIL_USERNAME,
               'password'    => MAIL_PASSWORD,
               'port'        => MAIL_PORT,
            );

            // メールの内容を設定
            $mail_params = array(
              'subject'    => $this->mail_subject,
              'body'       => preg_replace('/{name}/', $name, $this->mail_msg),
              'from'       => $this->from,
              'to'         => $to,
            );

            $res_mail = $mail->sendMail($mail_params, $mail_config_params);
            if (!$res_mail) {
                $this->db->log->write('ERROR MESSAGE:サンキューメールの送信「'.$to.'」はエラーがあります。', __LINE__, 'send.php');
                return false;
            }

        } catch (\Exception $e) {
            $this->db->log->write('ERROR MESSAGE:サンキューメールの送信「'.$to.'」はエラーがあります。エラー：'.$e->getMessage(), __LINE__, 'send.php');
            return false;
        }
        return true;
    }
}
