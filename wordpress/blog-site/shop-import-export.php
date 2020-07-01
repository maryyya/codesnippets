<?php

/**
 * 使い方
 *
 * If there is no paramater which is
 * like tokyo or miyagi or saitama
 * then the default parameter is tokyo.
 * If the parameter is not tokyo or
 * miyagi or saitama then it will cancel
 * the whole process or operation.
 * This is run by the crontab.
 *
 * in /home dir the command is:
 * 東京
 * php shop-import-export.php tokyo
 *
 * 宮城
 * php shop-import-export.php miyagi
 *
 * 埼玉
 * php shop-import-export.php saitama
 *
 */
class Shop
{
    /**
     * Path for the wordpress.
     * This will change when
     * it's time to honapp.
     */
    const PATH = '/usr/share/nginx/html';

    /**
     * This is the plugin name.
     */
    const PLUGIN_NAME = 'shop-import-export';

    /**
     * Site name is tokyo
     */
    const SITE = 'tokyo';

    /**
     * wordpressテーブル接頭辞
     */
    const PREFIX = 'tokyo';

    /**
     * データベースホスト名
     */
    const DB_HOST = 'localhost';

    /**
     * ログパス
     */
    const SHOP_LOG_DIR = '/var/log/shoplog';

    /**
     * table prefix
     *
     * @var string
     */
    private $prefix;

    /**
     * データベース名前
     *
     * @var string
     */
    private $db_name;

    /**
     * データベースユーザ名
     *
     * @var string
     */
    private $db_user;

    /**
     * データベースパスワード
     *
     * @var string
     */
    private $db_pass;

    /**
     * filename for the log file
     *
     * @var string
     */
    private $log_file_date;

    /**
     * データベース名前
     * 東京、埼玉、宮城
     *
     * @var array
     */
    private $db_name_list = array(
        'tokyo'   => 'wp_honban_1008',
        'saitama' => 'saitama_0626',
        'miyagi'  => 'miyagiwp_0627',
        'osaka'   => 'osaka',
        'fukuoka' => 'fukuoka',
    );

    /**
     * Array of site list.
     * This will change when putting
     * in production.
     *
     * @var array
     */
    private $site_list = array(
        'tokyo'   => 'wp',
        'saitama' => 'saitama',
        'miyagi'  => 'miyagiwp',
        'osaka'   => 'osaka',
        'fukuoka' => 'fukuoka',
    );

    /**
     * データベースユーザ名
     * 東京、埼玉、宮城
     *
     * @var array
     */
    private $db_user_list = array(
        'tokyo'   => '',
        'saitama' => '',
        'miyagi'  => '',
        'osaka'   => '',
        'fukuoka' => '',
    );

    /**
     * データベースパスワード
     * 東京、埼玉、宮城
     *
     * @var array
     */
    private $db_pass_list = array(
        'tokyo'   => '',
        'saitama' => '',
        'miyagi'  => '',
        'osaka'   => '',
        'fukuoka' => '',
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

        'sales-time-remarks-1'  => 'field_5db0f51c0fb3f',
        'sales-time-remarks-2'  => 'field_5db0f5350fb40',
        'sales-time-remarks-3'  => 'field_5db13ac456fdb',

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

        'sales-time-remarks-1'  => 'field_5db0f51c0fb3f',
        'sales-time-remarks-2'  => 'field_5db0f5350fb40',
        'sales-time-remarks-3'  => 'field_5db13ac456fdb',

        'ppc_api_id'            => 'field_5c6dfc643eff3',
        'ppc_api_source'        => 'field_5c6bc5ac2688a',
        'ppc_api_source_fdoc'   => 'field_5c6cf3cc9cf55',
        'ppc_api_source_haisha' => 'field_5c6cf433def05',
    );

    /**
     * get param(site name)
     */
    public function __construct($argv)
    {
        // get param
        // set param to $prefix
        // use CONST PREFIX, if there is not param
        $type = isset($argv[1]) ? $argv[1] : self::PREFIX;
        if (!in_array($type, array_keys($this->site_list))) {
            $this->prefix = $type;
            $this->log('"ERROR: サイトがありません。処理を終了します。"', __LINE__);
            exit;
        }

        $this->site    = $this->site_list[$type];
        $this->prefix  = $type . '_';
        $this->db_name = isset($this->db_name_list[$type]) ? $this->db_name_list[$type] : '';
        $this->db_user = isset($this->db_user_list[$type]) ? $this->db_user_list[$type] : '';
        $this->db_pass = isset($this->db_pass_list[$type]) ? $this->db_pass_list[$type] : '';
    }

    /**
     * Execute the queries
     */
    public function exec()
    {
        // check if path exists and will return the official path
        $data = $this->check();
        if (!$data) {
            return false;
        }

        // this is for the log file name
        $timezone            = new DateTime(null, new DateTimeZone('Asia/Tokyo'));
        $date                = $timezone->format('Y-m-d-His');
        $this->log_file_date = $date;

        try {
            $this->log('"INFO : 開始します。"', __LINE__, '', $data['kind']);
            $this->log('"INFO : csvファイル：' . $data['path'] . '"', __LINE__, '', $data['kind']);

            // to create backup
            if (!$this->insert_bk($data)) {
                exit;
            }

            $this->mysqli()->query('BEGIN');

            // to start executing the query files
            $this->insert_update($data['path']);

            $this->mysqli()->query('COMMIT');
            $this->log('"INFO : DBへの店舗データ登録が完了しました。"', __LINE__, '', $data['kind']);

            // $this->log('"INFO : クエリファイル「insertとupdate」を削除します。"', __LINE__, '', $data['kind']);
            // $this->remove_tmp_files($data['path'], $data['kind']);
        } catch (Exception $e) {
            $this->mysqli()->rollback();
            $this->log('"ERROR: 例外が発生しましたのでロールバックを実行しました。"', __LINE__, '', $data['kind']);
            $this->log($e->getMessage(), __LINE__, '', $data['kind']);
        }
    }

    /**
     * Create back up database
     * file in the bk directory
     * inside plugins/shop-import-export
     * directory.
     */
    private function insert_bk($param)
    {
        $kind     = $param['kind'] === 'sched' ? 'shop' : 'postmeta';
        $tmp_path = str_replace('tmp', 'bk', $param['path']);
        $filename = $tmp_path . '/' . $this->prefix . $kind . '_' . date('YmdHis') . '.dump';
        $table    = $this->prefix . $kind;

        $cmd = '/usr/bin/mysqldump';
        $cmd .= ' -u ' . $this->db_user;
        $cmd .= " -p'" . $this->db_pass . "'";
        $cmd .= ' --single-transaction --quick';
        $cmd .= ' ' . $this->db_name;
        $cmd .= ' ' . $this->prefix . $kind;
        $cmd .= ' > ' . $filename;

        $this->log('"INFO : バックアップデータを開始します。[' . $table . ']"', __LINE__, '', $param['kind']);
        $this->log('"INFO : PATH[ ' . $filename . ' ]"', __LINE__, '', $param['kind']);
        $res = exec($cmd, $output, $return);
        if ($return < 1) {
            $this->log('"INFO : バックアップファイルが正常に作成されました。[' . $table . ']"', __LINE__, '', $param['kind']);
            return true;
        } else {
            $this->log('"WARN : バックアップファイルの作成に失敗しました。[' . $table . ']"', __LINE__, '', $param['kind']);
            return false;
        }
    }

    /**
     * To have necessary checks to
     * avoid errors.
     *
     * @return boolean|string false if has error else the path to query
     */
    private function check()
    {
        // /usr/share/nginx/html/wp/manager/wp-content/plugins/shop-import-export - 開発環境のため
        $path = self::PATH . '/' . $this->site . '/manager/wp-content/plugins/' . self::PLUGIN_NAME . '/tmp';
        if (!file_exists($path)) {
            // $this->log('"ERROR: プラグインディレクトリがありません。処理を終了します。"', __LINE__);
            return false;
        }

        // flag directory
        $flag_dir = self::PATH.'/'.$this->site.'/manager/wp-content/plugins/'.self::PLUGIN_NAME.'/flag/';
        // check if there are files inside flag file
        if (count(scandir($flag_dir)) > 2) {
            // $this->log('"ERROR: ディレクトリが空です。 SQLファイルはありません。処理を終了します。"', __LINE__);
            return false;
        }

        // check if there are files inside the directory
        if (count(scandir($path)) < 3) {
            // $this->log('"ERROR: ディレクトリが空です。 SQLファイルはありません。処理を終了します。"', __LINE__);
            return false;
        }

        $kind = '';
        foreach (scandir($path) as $key => $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            // only execute sql files
            if (strpos($file, '.sql') === false) {
                continue;
            }

            if (strpos($file, 'sched') > -1) {
                $kind = 'sched';
            }
        }

        return array('path' => $path, 'kind' => $kind);
    }

    /**
     * Execute the insert and update
     * query files.
     *
     * @param string $path The path to the query files which is in the shop-import-export directory.
     */
    private function insert_update($path)
    {
        $files = scandir($path);
        foreach ($files as $key => $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            // this is to determine that there's is file executing so skip it if the time is 5min again.
            $flag_dir = self::PATH.'/'.$this->site.'/manager/wp-content/plugins/'.self::PLUGIN_NAME.'/flag/';
            $flag_file = $flag_dir.$file;
            if (!file_exists($flag_file)) {
                $this->log('"INFO: '.$flag_file.'のフラグファイルを作ります。"', __LINE__, '');
                $flag_handle = fopen( $flag_file, 'w' );
                fwrite($flag_handle, '1');
                fclose( $flag_handle );
            } else {
                $this->log('"INFO: '.$path.'/'.$file.'のファイルをスキップします。実行中です。"', __LINE__, '');
                return false;
            }

            // カスタム営業時間のため
            if (strpos($file, 'sched') > -1) {
                $this->sched_insert_update($path, $file, $flag_file);
                continue;
            }

            // /usr/share/nginx/html/wp/manager/wp-content/plugins/shop-import-export - 開発環境のため
            $filepath = $path . '/' . $file;
            $handle   = fopen($filepath, 'r');
            if ($handle) {
                $linecnt = 0;
                while (($sql = fgets($handle, 1000)) !== false) {
                    $pattern = '/(WHERE ID = [0-9]*;)|(WHERE post_id = [0-9]* AND)|(VALUES \(( |)[0-9]*,)/u';
                    preg_match_all($pattern, $sql, $matches);
                    $post_id = !empty($matches[0][0]) ? preg_replace('/\D/', '', $matches[0][0]) : 0;
                    $type    = strpos($sql, 'UPDATE') !== false ? 'update' : 'insert';
                    $line    = 'ファイル: ' . $file . ' LINE#: ' . $linecnt . '    ';

                    if ($type === 'update') {
                        $metakey_pattern = '/meta_key = \'[a-z-A-Z_\-0-9]*\'/';
                        preg_match_all($metakey_pattern, $sql, $metakey_matches);
                        if (!empty($metakey_matches[0][0])) {
                            $metakey = str_replace("meta_key = '", '', substr_replace($metakey_matches[0][0], '', -1));
                        }

                        $metavalue_pattern = '/meta_value = \'(.*)\' WHERE/';
                        preg_match_all($metavalue_pattern, $sql, $metavalue_matches);
                        if (!empty($metavalue_matches[0][0])) {
                            $metavalue = str_replace("meta_value = '", '', str_replace("' WHERE", '', $metavalue_matches[0][0]));
                        }

                        if (strpos($sql, 'post_title') > -1) {
                            $metavalue_pattern = '/post_title = \'(.*)\' WHERE/';
                            preg_match_all($metavalue_pattern, $sql, $metavalue_matches);
                            if (!empty($metavalue_matches[0][0])) {
                                $metavalue = str_replace("post_title = '", '', str_replace("' WHERE", '', $metavalue_matches[0][0]));
                            }

                            $metakey = 'post_title';
                        }

                    } else {
                        // VALUES \( [0-9]*, '[a-z-A-z_]*', \'(.*)\'\);
                        $metavalue_pattern = '/VALUES \( {post_id}, \'([a-z-A-z_\-0-9]*)\', \'(.*)\'\);/';
                        preg_match_all($metavalue_pattern, $sql, $metavalue_matches);
                        $metakey   = !empty($metavalue_matches[1][0]) ? $metavalue_matches[1][0] : '';
                        $metavalue = !empty($metavalue_matches[2][0]) ? $metavalue_matches[2][0] : '';

                        // to change the pattern {post_id} to post id, this is for insert only
                        if (strpos($sql, 'INSERT INTO ' . $this->prefix . 'posts') > -1) {
                            $last_post_id = $this->get_last_post_id();
                            $insert_sql   = preg_replace('/{post_id}/', $last_post_id, $sql);
                            $insert_res   = $this->mysqli()->query($insert_sql);
                            if (!$insert_res) {
                                $this->log($this->organize_msg((int) $last_post_id, $sql, $type, '{Status: NG}', '店舗データの登録に失敗しました！'), $line, 'query');
                            } else {
                                $this->log($this->organize_msg((int) $last_post_id, $sql, $type, '{Status: OK}', '店舗データを登録しました。'), $line, 'query');
                            }

                            $post_id = $last_post_id;
                            $sql     = preg_replace('/{post_id}/', $last_post_id, $sql);
                        }

                        $post_id = $last_post_id;
                        $sql     = preg_replace('/{post_id}/', $last_post_id, $sql);
                    }

                    $metadata = array(
                        'postid'    => (int) $post_id,
                        'metakey'   => isset($metakey) ? $metakey : '',
                        'metavalue' => isset($metavalue) ? $metavalue : '',
                        'sql'       => $sql,
                    );

                    $add_sql = '';
                    // check if meta data exists one by one, this is for the update
                    if ($type === 'update' && $metakey !== 'post_title' && $metakey !== '') {
                        $check = $this->check_metadata_exists((int) $post_id, $metakey);
                        if ($check < 1) {
                            $type    = 'insert';
                            $content = json_encode($metavalue, JSON_UNESCAPED_UNICODE);
                            $sql     = "INSERT INTO " . $this->prefix . 'postmeta (post_id, meta_key, meta_value) VALUES (' . $post_id . ", '" . $metakey . "', " . $content . ");\n";
                            if ($this->site === 'miyagiwp') {
                                if (isset($this->acf_miyagi_equivalent[$metakey])) {
                                    $add_sql = "INSERT INTO " . $this->prefix . "postmeta (post_id, meta_key, meta_value) VALUES ( " . $post_id . ", '_" . $metakey . "', '" . $this->acf_miyagi_equivalent[$metakey] . "');\n";
                                }
                            } else {
                                if (isset($this->acf_equivalent[$metakey])) {
                                    $add_sql = "INSERT INTO " . $this->prefix . "postmeta (post_id, meta_key, meta_value) VALUES ( " . $post_id . ", '_" . $metakey . "', '" . $this->acf_equivalent[$metakey] . "');\n";
                                }
                            }

                        }
                    }

                    if (!empty($metakey !== '')) {
                        $res = $this->mysqli()->query($sql);
                        if (!$res) {
                            $this->log($this->organize_msg((int) $post_id, json_encode($metadata, JSON_UNESCAPED_UNICODE), $type, '{Status: NG}', '店舗データの登録に失敗しました！'), $line, 'query');
                        } else {
                            $this->log($this->organize_msg((int) $post_id, json_encode($metadata, JSON_UNESCAPED_UNICODE), $type, '{Status: OK}', '店舗データを登録しました。'), $line, 'query');
                        }
                    }

                    if (!empty($add_sql)) {
                        $add_res = $this->mysqli()->query($add_sql);
                        if ($this->site === 'miyagiwp') {
                            $add_metadata = array(
                                'postid'    => (int) $post_id,
                                'metakey'   => '_' . $metakey,
                                'metavalue' => $this->acf_miyagi_equivalent[$metakey],
                                'sql'       => $add_sql,
                            );
                        }{
                            $add_metadata = array(
                                'postid'    => (int) $post_id,
                                'metakey'   => '_' . $metakey,
                                'metavalue' => $this->acf_equivalent[$metakey],
                                'sql'       => $add_sql,
                            );
                        }

                        if (!$add_res) {
                            $this->log($this->organize_msg((int) $post_id, json_encode($add_metadata, JSON_UNESCAPED_UNICODE), $type, '{Status: NG}', '店舗データの登録に失敗しました！'), $line, 'query');
                        } else {
                            $this->log($this->organize_msg((int) $post_id, json_encode($add_metadata, JSON_UNESCAPED_UNICODE), $type, '{Status: OK}', '店舗データを登録しました。'), $line, 'query');
                        }
                    }

                    $linecnt++;
                }
            }

            fclose($handle);

            unlink($path.'/'.$file);
            $this->log('"INFO: '.$path.'/'.$file.'のファイルを削除しました。"', __LINE__, '');

            unlink($flag_file);
            $this->log('"INFO: '.$flag_file.'のファイルを削除しました。"', __LINE__, '');
        }
    }

    /**
     * カスタム営業時間
     *
     * @param string $path
     * @param string $file
     * @param string $flag_file
     * @return
     */
    private function sched_insert_update($path, $file, $flag_file)
    {
        $filepath = $path . '/' . $file;
        $handle   = fopen($filepath, 'r');
        if ($handle) {
            while (($sql = fgets($handle, 1000)) !== false) {
                $type = strpos($sql, 'UPDATE') !== false ? 'update' : 'insert';
                $line = 'ファイル: ' . $file . ' LINE#: ' . __LINE__ . '    ';

                if ($type === 'update') {
                    $item_pattern = '/WHERE ID = [0-9]*/';
                    preg_match_all($item_pattern, $sql, $item_matches);
                    if (!empty($item_matches[0][0])) {
                        $item_id = str_replace("WHERE ID = ", '', $item_matches[0][0]);
                    }

                    $post_pattern = '/AND post_id = [0-9]*/';
                    preg_match_all($post_pattern, $sql, $post_matches);
                    if (!empty($post_matches[0][0])) {
                        $post_id = str_replace("AND post_id = ", '', $post_matches[0][0]);
                    }
                } else {
                    // VALUES \( [0-9]*, '[a-z-A-z_]*', \'(.*)\'\);
                    $post_id_pattern = '/VALUES \([0-9]*,/';
                    preg_match_all($post_id_pattern, $sql, $post_id_matches);
                    if (!empty($post_id_matches[0][0])) {
                        $post_id_clean = preg_replace('/VALUES \(/', '', $post_id_matches[0][0]);
                        $post_id       = (int) preg_replace('/,/', '', $post_id_clean);
                    }
                    $item_id = '';

                }

                $metadata = array(
                    'item_id' => $item_id,
                    'postid'  => (int) $post_id,
                    'sql'     => $sql,
                );
                $json = json_encode($metadata, JSON_UNESCAPED_UNICODE);

                $res = $this->mysqli()->query($sql);
                if (!$res) {
                    $msg = str_pad('ID: ' . $item_id, 20) . ' ' . str_pad('店舗ID: ' . $post_id, 20) . ' ' . str_pad('{Status: NG}', 15) . '"店舗データの登録に失敗しました！"     ' . $json . ' ----end.';
                    $this->log($msg, $line, 'query', 'sched');
                } else {
                    $msg = str_pad('ID: ' . $item_id, 20) . ' ' . str_pad('店舗ID: ' . $post_id, 20) . ' ' . str_pad('{Status: OK}', 15) . '"店舗データを登録しました。"     ' . $json . ' ----end.';
                    $this->log($msg, $line, 'query', 'sched');
                }
            }
        }

        fclose($handle);

        unlink($path.'/'.$file);
        $this->log('"INFO: '.$path.'/'.$file.'のファイルを削除しました。"', __LINE__, '');

        unlink($flag_file);
        $this->log('"INFO: '.$flag_file.'のファイルを削除しました。"', __LINE__, '');
    }

    /**
     * Remove the tmp query files.
     * The insert and update files.
     *
     * @param string $path log file path
     * @param string $kind 'sched' or none. This is used in putting up the query logs for カスタム営業時間. Used only in sched_insert_update().
     * @return
     */
    private function remove_tmp_files($path, $kind)
    {
        $files = scandir($path);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filepath = $path . '/' . $file;

            $this->log('"INFO: ' . $filepath . 'のファイルを削除しました。"', __LINE__, '', $kind);
            unlink($filepath);
        }
    }

    /**
     * To get the last
     * post id for inserting a
     * new record in post type
     * shop.
     *
     * @return int post id
     */
    private function get_last_post_id()
    {
        $sql = 'SELECT ID FROM ' . $this->prefix . 'posts ORDER BY ID DESC LIMIT 1';
        $res = $this->mysqli()->query($sql);

        $data = array();
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return (int) $data[0]['ID'] + 1;
        // return (int)$data[0]['ID'];
    }

    /**
     * Check the meta data if it exists
     *
     * @param int    $post_id
     * @param string $meta_key
     * @return int   num of rows found of the meta data
     */
    private function check_metadata_exists($post_id, $meta_key)
    {
        $sql = 'SELECT post_id FROM ' . $this->prefix . 'postmeta ' . 'where post_id = ' . $post_id . ' and meta_key = "' . $meta_key . '"';
        $res = $this->mysqli()->query($sql);
        return $res->num_rows;
    }

    /**
     * Organize the log message for requesting the
     * shop data. This log will be displayed in the
     * admin page in 店舗情報のインポートエクスポート。
     *
     * @param  int    $id       shop id
     * @param  string $metadata it contains metakey, metavalue, and the query
     * @param  string $type     'insert' or 'update'
     * @param  string $status   data status, if there's error then it will give 400 or 500 or 0 if none
     * @param  string $msg      error message
     * @return string           complete and organized log message
     */
    private function organize_msg($id, $metadata, $type, $status, $msg)
    {
        return str_pad('店舗ID: ' . $id, 20) . ' ' . str_pad('「' . $type . '」', 20) . ' ' . str_pad($status, 15) . '"' . $msg . '"     ' . $metadata . ' ----end.';
    }

    /**
     * Mysqli connect
     */
    private function mysqli()
    {
        $conn = new mysqli(self::DB_HOST, $this->db_user, $this->db_pass, $this->db_name);
        $conn->set_charset("utf8");
        if ($conn->connect_error) {
            $this->log('Error: Connection failed: ' . mysqli_connect_error(), __LINE__);
            exit;
        }

        return $conn;
    }

    /**
     * Access and error log
     * 使い方: $this->log('message', __LINE__);
     *
     * @param  string $msg  Error or access log
     * @param  string $line Line number or the file and line data for the query logs
     * @param  string $type query or none. This is used in putting up the query logs in insert_update().
     * @param  string $kind 'sched' or none. This is used in putting up the query logs for カスタム営業時間. Used only in sched_insert_update().
     */
    private function log($msg, $line, $type = '', $kind = '')
    {
        $timezone = new DateTime(null, new DateTimeZone('Asia/Tokyo'));
        $date     = $this->log_file_date;
        $fullDate = $timezone->format('Y-m-d H:i:s');
        if (!empty($kind)) {
            $fullpath = self::SHOP_LOG_DIR . '/' . $date . '-' . str_replace('_', '', $this->prefix) . '-' . $kind . '-shop.log';
        } else {
            $fullpath = self::SHOP_LOG_DIR . '/' . $date . '-' . str_replace('_', '', $this->prefix) . '-shop.log';
        }
        try {
            if (!file_exists(self::SHOP_LOG_DIR)) {
                mkdir(self::SHOP_LOG_DIR, 0777, true);
            }

            // ファイルが存在を確認します。
            $mode = !file_exists($fullpath) ? 'w' : 'a';

            $fp = fopen($fullpath, $mode);

            // this is used in query logs to identify which file and which line
            if ($type === 'query') {
                fwrite($fp, $fullDate . ' ' . str_pad($line, 20) . ' ' . $msg . PHP_EOL);
            } else {
                fwrite($fp, $fullDate . ' ' . str_pad('LINE#' . $line, 13) . ' ' . $msg . PHP_EOL);
            }

            fclose($fp);
        } catch (\Exception $e) {

        }

    }
}

$shop = new Shop($argv);
$shop->exec();
