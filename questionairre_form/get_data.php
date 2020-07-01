<?php

/**
 * This is to get the defined
 * data from the db. These
 * data are used in radio,
 * checkbox or selectbox.
 */
class Get_Data
{
    /**
     * Db Class in constructor
     *
     * @var object
     */
    private $db;

    /**
     * Get options
     *
     * @var array
     */
    private $options = array();

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
        'sankashasu2'   => 'J',   // 参加者数集計
    );

    /**
     * Input in web and in the database
     *
     * @var array
     */
    private $input = array(
        'dantai_cd',
        'dantai_kbn',
        'dantai_kbn2',
        'jusho1',
        'jusho2',
        'dantaimei',
        'hojinkakucode',
        'furigana',
        'yubinbango',
        'toshi_code',
        'todofuken',
        'yakushokumei',
        'tantoshamei',
        'naisen',
        'tel_1',
        'tel_2',
        'tel_3',
        'fax_1',
        'fax_2',
        'fax_3',
        'e_mail',
        'url',
        'sasshi',
        'sasshi_support',
        'kaitou_form' => array(
            'convention_mei',
            'convention_url',
            'kaigi_shubetsu',
            'sankashasu',
            'sankashasu2',
            'gaikokujinsu',
            'sankakokusu',
            'gyojinaiyo',
            'type_e_ext',
            'kaisaikaijo',
            'type_f_ext',
            'sokai_hole',
            'recep_hole',
            'shitsusu',
            'hiroma',
            'kaisaijiki',
            'kaisaibasho',
            'ketteijiki',
            'type_i_ext',
            'yosan',
            'uchikifukin',
            'code_2yearsago',
            'area_2yearsago',
            'code_2yearsago',
            'name_2yearsago',
            'area_1yearago',
            'code_1yearago',
            'name_1yearago',
            'area_thisyear',
            'code_thisyear',
            'name_thisyear',
            'area_1yearlater',
            'code_1yearlater',
            'name_1yearlater',
            'area_2yearslater',
            'code_2yearslater',
            'name_2yearslater',
            'area_3yearslater',
            'code_3yearslater',
            'name_3yearslater',
            'area_4yearslater',
            'code_4yearslater',
            'name_4yearslater',
            'area_5yearslater',
            'code_5yearslater',
            'name_5yearslater',
        ),
    );

    /**
     * Constructor
     */
    public function __construct() {
        require_once('mysql.php');
        $this->db = new Mysql;

        require_once('clean_params.php');
        $this->clean = new Clean_Param;
    }

    /**
     * Get 団体と回答
     *
     * @param array $session
     * @param array $post
     * @return array
     */
    public function get_data($session, $post_data) {
        $post = $this->clean->clean_all('', $post_data);
        $all_data  = $this->all_data($session['DANTAI_ID']);
        $code      = $this->get_kubun_code();

        // for the radio button, select and checkboxes
        $this->options = array(
            'hojinkakucode' => $this->kubun($this->kubun_equivalent['hojinkakucode']),    // 法人格 / 団体名
            'toshi_code'    => $this->kubun($this->kubun_equivalent['toshi_code']),       // 都道府県名
            'area_code'     => $this->kubun($this->kubun_equivalent['area_code']),        // 地域
            'dantai_kbn'    => $this->kubun($this->kubun_equivalent['dantai_kbn'], 6),    // 団体区分
            'gyojinaiyo'    => $this->kubun($this->kubun_equivalent['gyojinaiyo']),       // 行事内容
            'kaisaikaijo'   => $this->kubun($this->kubun_equivalent['kaisaikaijo']),      // 開催会場
            'kaisaijiki'    => $this->kubun($this->kubun_equivalent['kaisaijiki'], 2),    // 開催時期
            'kaisaibasho'   => $this->kubun($this->kubun_equivalent['kaisaibasho'], 2),   // 開催場所
            'ketteijiki'    => $this->kubun($this->kubun_equivalent['ketteijiki']),       // 決定時期
            'sankashasu2'   => $this->kubun($this->kubun_equivalent['sankashasu2'], 3),   // 参加者数集計
        );

        if (isset($post['page_type'])) {
            // This is for the confirm page for the form
            $kaitou_keys = array();
            $post = $this->add_param($post, 'dantai');
        } elseif (empty($post)) {
            // for session - which is to display the data from the database in index.php
            // dantai data
            $tel = explode('-', $all_data[0]['tel']);
            $fax = explode('-', $all_data[0]['fax']);
            $post = array(
                'dantai_cd'      => $all_data[0]['dantai_code'],      // 団体コード
                'dantai_kbn'     => $all_data[0]['dantai_kbn'],       // 団体区分
                'jusho1'         => $all_data[0]['jusho1'],           // 住所１
                'jusho2'         => $all_data[0]['jusho2'],           // 住所２
                'dantaimei'      => $all_data[0]['dantaimei'],        // 団体名
                'hojinkakucode'  => $all_data[0]['hojinkakucode'],    // 区分マスタ（法人格.コード）
                'furigana'       => $all_data[0]['furigana'],         // フリガナ
                'yubinbango'     => $all_data[0]['yubinbango'],       // 郵便番号
                'toshi_code'     => $all_data[0]['toshi_code'],       // 都市コード
                'yakushokumei'   => $all_data[0]['yakushokumei'],     // 役職名
                'tantoshamei'    => $all_data[0]['tantoshamei'],      // 担当者名
                'naisen'         => $all_data[0]['naisen'],           // 内線
                'tel_1'          => !isset($tel[0]) || strlen($tel[0])<1?'':$tel[0],     // tel_1
                'tel_2'          => !isset($tel[1]) || strlen($tel[1])<1?'':$tel[1],     // tel_2
                'tel_3'          => !isset($tel[2]) || strlen($tel[2])<1?'':$tel[2],     // tel_3
                'fax_1'          => !isset($fax[0]) || strlen($fax[0])<1?'':$fax[0],     // fax_1
                'fax_2'          => !isset($fax[1]) || strlen($fax[1])<1?'':$fax[1],     // fax_2
                'fax_3'          => !isset($fax[2]) || strlen($fax[2])<1?'':$fax[2],     // fax_3
                'e_mail'         => $all_data[0]['e_mail'],           // e_mail
                'url'            => $all_data[0]['url'],              // url
                'sasshi'         => $all_data[0]['sasshi'],           // 冊子
                'sasshi_support' => $all_data[0]['sasshi_support'],   // 支援内容について知りたい, 1:知りたい;0:知りたくない
            );

            // 団体区分2
            $post['dantai_kbn2'] = !empty($all_data[0]['dantai_kbn'])?$this->get_post_kubun_code('dantai_kbn', $all_data[0]['dantai_kbn']): '';

            // 都道府県
            $post['todofuken']   = strlen($all_data[0]['toshi_code'])>0?$this->get_post_kubun_code('toshi_code', $all_data[0]['toshi_code']):NULL;

            // 回答
            if (is_array($all_data)) {
                foreach($all_data as $key => $val) {
                    $item = $key + 1;
                    $key_nm = 'kaitou_form_'.$item.'_';
                    $post[$key_nm.'kaitou_id']        = $val['id'];                                   // 回答ID
                    $post[$key_nm.'convention_mei']   = $val['convention_mei'];                       // コンベンション名
                    $post[$key_nm.'convention_url']   = $val['convention_url'];                       // コンベンションURL
                    $post[$key_nm.'kaigi_shubetsu']   = $val['kaigi_shubetsu'];                       // 会議の種別
                    $post[$key_nm.'sankashasu']       = $val['sankashasu'];                           // 参加者数（手入力）
                    $post[$key_nm.'sankashasu2']      = $val['sankashasu2'];                          // 参加者数2（範囲）
                    $post[$key_nm.'gaikokujinsu']     = $val['gaikokujinsu'];                         // 外国人数
                    $post[$key_nm.'sankakokusu']      = $val['sankakokusu'];                          // 参加国数
                    $post[$key_nm.'gyojinaiyo']       = $this->get_code($val['gyojinaiyo'], $code);   // 行事内容表示
                    $post[$key_nm.'type_e_ext']       = $val['type_e_ext'];                           // 行事内容のその他手入力内容
                    $post[$key_nm.'kaisaikaijo']      = $this->get_code($val['kaisaikaijo'], $code);  // 開催会場表示
                    $post[$key_nm.'type_f_ext']       = $val['type_f_ext'];                           // 開催会場のその他手入力内容
                    $post[$key_nm.'sokai_hole']       = $val['sokai_hole'];                           // 総会ホール
                    $post[$key_nm.'recep_hole']       = $val['recep_hole'];                           // レセプホール
                    $post[$key_nm.'shitsusu']         = $val['shitsusu'];                             // 室数
                    $post[$key_nm.'hiroma']           = $val['hiroma'];                               // 広間
                    $post[$key_nm.'kaisaijiki']       = $this->get_code($val['kaisaijiki'], $code);   // 開催時期表示
                    $post[$key_nm.'kaisaibasho']      = $this->get_code($val['kaisaibasho'], $code);  // 開催場所表示
                    $post[$key_nm.'ketteijiki']       = $this->get_code($val['ketteijiki'], $code);   // 開催場所表示
                    $post[$key_nm.'type_i_ext']       = $val['type_i_ext'];                           // 決定時期のその他手入力内容
                    $post[$key_nm.'yosan']            = $val['yosan'];                                // 予算
                    $post[$key_nm.'uchikifukin']      = $val['uchikifukin'];                          // 内寄付金

                    // 開催実績
                    $post[$key_nm.'area_2yearsago'] = $val['area_2yearsago'];   // _17地域
                    $post[$key_nm.'code_2yearsago'] = $val['code_2yearsago'];   // 17コード
                    $post[$key_nm.'name_2yearsago'] = $val['name_2yearsago'];   // _17名
                    $post[$key_nm.'area_1yearago']  = $val['area_1yearago'  ];  // _18地域
                    $post[$key_nm.'code_1yearago']  = $val['code_1yearago'  ];  // 18コード
                    $post[$key_nm.'name_1yearago']  = $val['name_1yearago'  ];  // _18名
                    $post[$key_nm.'area_thisyear']  = $val['area_thisyear'  ];  // _19地域
                    $post[$key_nm.'code_thisyear']  = $val['code_thisyear'  ];  // 19コード
                    $post[$key_nm.'name_thisyear']  = $val['name_thisyear'  ];  // _19名

                    // 開催予定
                    $post[$key_nm.'area_1yearlater']  = $val['area_1yearlater'];   // _20地域
                    $post[$key_nm.'code_1yearlater']  = $val['code_1yearlater'];   // _20名
                    $post[$key_nm.'name_1yearlater']  = $val['name_1yearlater'];   // 20コード
                    $post[$key_nm.'area_2yearslater'] = $val['area_2yearslater'];  // _21地域
                    $post[$key_nm.'code_2yearslater'] = $val['code_2yearslater'];  // 21コード
                    $post[$key_nm.'name_2yearslater'] = $val['name_2yearslater'];  // _21名
                    $post[$key_nm.'area_3yearslater'] = $val['area_3yearslater'];  // _22地域
                    $post[$key_nm.'code_3yearslater'] = $val['code_3yearslater'];  // 22コード
                    $post[$key_nm.'name_3yearslater'] = $val['name_3yearslater'];  // _22名
                    $post[$key_nm.'area_4yearslater'] = $val['area_4yearslater'];  // _23地域
                    $post[$key_nm.'code_4yearslater'] = $val['code_4yearslater'];  // 23コード
                    $post[$key_nm.'name_4yearslater'] = $val['name_4yearslater'];  // _23名
                    $post[$key_nm.'area_5yearslater'] = $val['area_5yearslater'];  // _24地域
                    $post[$key_nm.'code_5yearslater'] = $val['code_5yearslater'];  // 24コード
                    $post[$key_nm.'name_5yearslater'] = $val['name_5yearslater'];  // _24名
                }
            }
        } else {

            // 確認ページのため
            $data = array();

            // just add parameter
            $post = $this->add_param($post, 'dantai');
            foreach($post as $key => $items) {
                // 冊子
                if ($key === 'sasshi' && !empty($items)) {
                    $data[$key] = $items;
                    continue;
                }

                // 冊子サポート
                if ($key === 'sasshi_support') {
                    $data[$key] = isset($items[0])?(int)$items[0]:0;
                    continue;
                }

                if (is_array($items)) {
                    foreach ($items as $keyitem => $val) {
                        $val = $this->add_param($val, 'kaitou');
                        // if no コンベンション名 then don't include
                        if (empty($val['convention_mei'])) {
                            continue;
                        }

                        foreach ($val as $keyval => $info) {
                            // $kaitou_equiv = array(
                            //     'sankashasu2'   => 'J',   // 参加者数集計
                            // );

                            // if (in_array($keyval, array_keys($kaitou_equiv))) {
                            //     $data[$key][$keyitem][$keyval] = $this->get_post_kubun_code($keyval, $info);
                            //     continue;
                            // }

                            if (is_array($info)) {
                                $data[$key][$keyitem][$keyval] = implode(', ', $info);
                                continue;
                            }

                            if (is_string($info)) {
                                $data[$key][$keyitem][$keyval] = isset($info) && strlen($info) > 0?$info:'';
                                continue;
                            }

                            if (is_int($info)) {
                                $data[$key][$keyitem][$keyval] = isset($info)?$info:'';
                            }

                        }
                    }

                    continue;
                }

                $equiv = array(
                    'hojinkakucode' => 'A',   // 法人格 / 団体名
                    'toshi_code'    => 'B',   // 都道府県名
                    'area_code'     => 'C',   // 地域
                    'dantai_kbn'    => 'D',   // 団体区分
                );

                if (in_array($key, array_keys($equiv))) {
                    $data[$key] = $this->get_post_kubun_code($key, $items);
                    continue;
                }

                if (!in_array($key, $this->input)) {
                    $data[$key] = '';
                    continue;
                }

                // for string and int
                $data[$key] = isset($items) && strlen($items) > 0?$items:'';
            }

            $post = $data;
        }

        return array(
            'options' => $this->options,
            'post'    => $post
        );
    }

    /**
     * Add kubun code
     *
     * @param string $kubun
     * @param string $param
     * @return string
     */
    public function get_post_kubun_code($kubun, $param) {
        //
        $value = $this->options[$kubun];

        //
        $get_second = $this->get_second($value);

        //
        $kubun_code = isset($get_second[$param])?$get_second[$param]:'';

        if ($kubun === 'hojinkakucode'
            || $kubun === 'toshi_code'
            || $kubun === 'area_code'
            || $kubun === 'sankashasu2'
        ) {
            return $kubun_code;
        }

        return mb_substr($kubun_code, 2);
    }

    /**
     * Add params to remove the isset
     * in index and confirm page
     *
     * @param array $post
     * @return array
     */
    private function add_param($post, $type) {
        if ($type === 'dantai') {
            $post_keys = array_keys($post);
            $dantai_keys = $this->input;
            unset($dantai_keys['kaitou_form']);
            $add_post = array_diff($dantai_keys, $post_keys);
            if (!empty($add_post)) {
                foreach($add_post as $val) {
                    if ($val === 'kaitou_form') {
                        continue;
                    }

                    $post[$val] = '';
                }
            }
        } elseif ($type === 'kaitou') {
            $kaitou_keys = $this->input['kaitou_form'];
            $add_kaitou_post = array_diff($kaitou_keys, array_keys($post));
            if (!empty($add_kaitou_post)) {
                foreach($add_kaitou_post as $add_kaitou_post_val) {
                    $post[$add_kaitou_post_val] = '';
                }
            }
        }

        return $post;
    }

    /**
     * Get dantai cd
     *
     * @param int $id
     * @return int
     */
    private function all_data($id) {
        $sql = 'SELECT * FROM dantai LEFT JOIN kaitou ON dantai.id = kaitou.dantai_id WHERE dantai.ID = ?';
        $param = array(
            'id' => $id,
        );

        $res = $this->db->select($sql, $param);
        return empty($res)?0:$res;
    }

    /**
     * Get the data and put it in array.
     * It will only get the code and
     * the value.
     *
     * @param string $type It's either 'A', 'B', etc.
     * @return array $data data from db
     */
    private function kubun($type, $half = 0) {

        $sql = 'SELECT * FROM kubun WHERE type = ? AND delflag = ?';
        $param = array(
            'type'    => $type,
            'delflag' => 0,
        );
        $res = $this->db->select($sql, $param);

        $data = array();
        $key = 0;
        foreach($res as $key => $val) {
            if ($half > 0) {
                if ($key < $half) {
                    $data[0][$val['code']] = $val['value'];
                } else {
                    $data[1][$val['code']] = $val['value'];
                }

                continue;
            }

            $data[$val['code']] = $val['value'];

        }

        return $data;
    }

    /**
     * Get the kubun code
     * which is the data from db.
     * Since the value given from
     * the excel was string. like
     * c. 講演会（一般対象） e. その他（.
     * So i exploded it by dot(.)
     * and get the code type
     *
     * @param string  $param data from the db
     * @param array   $code  kubun code
     * @return string empty or the kubun code like 'a' or 'b'.
     */
    private function get_code($param, $code) {
        $explode = explode("\n", $param);
        $data = array();
        foreach($explode as $key => $val) {
            $data[] = substr($val, 0, 1);
        }

        return !empty($data)?implode(', ', $data):'';
    }

    /**
     * Get the kubun code
     *
     * @return array
     */
    private function get_kubun_code() {
        $res = $this->db->select('SELECT code FROM kubun', array());
        return $res;
    }


    /**
     * Get the second
     *
     * @param array $param
     * @return array
     */
    private function get_second($param) {
        $data = array();
        foreach ($param as $itemkey => $items) {
            if (!is_array($items)) {
                $data[$itemkey] = $items;
                continue;
            }

            foreach ($items as $key => $item) {
                $data[$key] = $item;
            }
        }

        return $data;
    }
}
