<?php
/**
 *
 */
class GetApiData {
    /**
     * グルメのCLIENT
     */
    const GOURMET_CLIENT_ID = '';

    /**
     * グルメのCLIENT SECRET
     */
    const GOURMET_CLIENT_SECRET = '';

    /**
     * 歯医者-歯科APP ID
     */
    const HAISHA_ID = '';

    /**
     * 歯医者-病院APP ID
     */
    const FDOC_ID = '';

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
    const API_LOG_DIR = '/var/log/apilog';

    /**
     * データベース名前
     * 東京、埼玉、宮城
     *
     * @var array
     */
    private $db_name_list = array(
        'tokyo'   => 'wp_honban_0418',
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
        'tokyo'   => 'root',
        'saitama' => 'root',
        'miyagi'  => 'root',
        'osaka'   => 'root',
        'fukuoka' => 'root',
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
     * get param(site name)
     */
    public function __construct($argv) {
        // get param
        // set param to $prefix
        // use CONST PREFIX, if there is not param
        $type = isset($argv[1])?$argv[1]:self::PREFIX;
        $this->prefix = $type.'_';
        $this->db_name = isset($this->db_name_list[$type])?$this->db_name_list[$type]:'';
        $this->db_user = isset($this->db_user_list[$type])?$this->db_user_list[$type]:'';
        $this->db_pass = isset($this->db_pass_list[$type])?$this->db_pass_list[$type]:'';
    }

    /**
     * Execute api
     */
    public function execute() {
        // check if prefix exists
        if (!$this->check_prefix()) {
            $this->log('"ERROR: 「'.$this->prefix.'」パラメータプレフィックスがありませんでした。"', __LINE__);
            return false;
        }

        $shopid_list = $this->get_shop_id();

        if (empty($shopid_list)) {
            $this->log('"ERROR: 店舗情報が取得できませんでした。処理を終了します。"', __LINE__);
            return false;
        }

        try {
            $this->log('"INFO : 店舗情報をDBより取得しました。APIデータ取得を開始します。"', __LINE__);
            $this->mysqli()->query('BEGIN');

            // insert backup
            $this->insert_bk();

            // delete api data from db first
            $this->delete_api();

            // get the api data
            $api_list = $this->get_api($shopid_list);

            // insert api data to db
            $this->insert_api($api_list);
            $this->mysqli()->query('COMMIT');
            $this->log('"INFO : DBへのAPIデータ登録が完了しました。"', __LINE__);
         } catch (Exception $e) {
            $this->mysqli()->rollback();
            $this->log('"ERROR: 例外が発生しましたのでロールバックを実行しました。"', __LINE__);
            $this->log($e->getMessage(), __LINE__);
        }
    }

    /**
     * Check the prefix if exists
     * since this is used for miyagi
     * or tokyo also
     *
     * @return boolean|object false if prefix does not exists, else object
     */
    private function check_prefix()
    {
        $sql = "SELECT count(*) as cnt FROM ".$this->prefix."api_data";
        return $this->mysqli()->query($sql);
    }

    /**
     * Create back up database
     */
    private function insert_bk() {
        $this->log('"INFO : バックアップデータ削除を開始します。['.$this->prefix.'api_data_bk]"', __LINE__);
        $sql = "DELETE FROM ".$this->prefix."api_data_bk;";
        $res = $this->mysqli()->query($sql);
        if (!$res) {
            $this->log('"WARN : バックアップ既存APIデータの削除に失敗しました。['.$this->prefix.'api_data_bk]"', __LINE__);
        } else {
            $this->log('"INFO : バックアップデータ削除を実行しました。['.$this->prefix.'api_data_bk]"', __LINE__);
        }

        $duplicate_sql = "INSERT INTO ".$this->prefix."api_data_bk SELECT * FROM ".$this->prefix."api_data;";
        $duplicate_res = $this->mysqli()->query($duplicate_sql);
        $this->log('"INFO : バックアップデータベースに挿入します。['.$this->prefix.'api_data_bk]"', __LINE__);
        if (!$duplicate_res) {
            $this->log('"WARN : バックアップデータベースへのデータの挿入に失敗しました。['.$this->prefix.'api_data_bk]"', __LINE__);
        } else {
            $this->log('"INFO : バックアップデータベースへのデータの挿入を実行しました。['.$this->prefix.'api_data_bk]"', __LINE__);
        }
    }

    /**
     * Delete from tokyo_api_data or miyagi_api_data
     * So that new data can be inserted
     */
    private function delete_api() {
        $this->log('"INFO : データ削除を開始します。['.$this->prefix.'api_data]"', __LINE__);
        $sql = "DELETE FROM ".$this->prefix."api_data;";
        $res = $this->mysqli()->query($sql);
        if (!$res) {
            $this->log('"WARN : 既存APIデータの削除に失敗しました。['.$this->prefix.'api_data]"', __LINE__);
        } else {
            $this->log('"INFO : データ削除を実行しました。['.$this->prefix.'api_data]"', __LINE__);
        }
    }

    /**
     * Get api data
     *
     * @param  array $shopid_list list of shop id
     * @return arrat $data        list of api data
     */
    private function get_api($shopid_list) {
        $this->log('"INFO : APIへのアクセスを開始します。"', __LINE__);
        $data = array();
        while($row = $shopid_list->fetch_assoc()) {
            $api_data = $this->request_api($row);
            if (empty($api_data)) {
                $this->log($this->organize_msg($row['api_id'], $row['type'], 'OK', 'APIデータがありません。'), __LINE__);
                continue;
            }

            $data[] = array(
                'id'   => $row['api_id'],
                'type' => $row['type'],
                'data' => $api_data
           );
        }

        $this->log('"INFO : APIへのアクセスが終了しました。"', __LINE__);
        return $data;
        // return array_unique($data, SORT_REGULAR);
    }

    /**
     * Insert api data
     *
     * @param  array $data list of api data
     */
    private function insert_api($data) {
        $this->log('"INFO : DBへのAPIデータINSERTを開始します。"', __LINE__);

        $timezone = new DateTime(null, new DateTimeZone('Asia/Tokyo'));
        $dtcreate = $timezone->format('Y-m-d H:i:s');

        $min = $i = $flg = 0;
        $max = 100;
        foreach ($data as $key => $value) {
            $i++;
            if ($i % $max == 0) {
                $flg = 1;
            }

            if ($i >= $min && $i <= $max) {
                $review = empty($value['data']['review'])?null:$this->mysqli()->real_escape_string($value['data']['review']);
                $data   = empty($value['data']['data'])?null:$this->mysqli()->real_escape_string($value['data']['data']);

                $sql = "INSERT INTO ".$this->prefix."api_data (api_id, type, api_data, api_data_review, dtcreate) VALUES";
                $sql .= ' ("'.$value['id'].'", "'.$value['type'].'", "'.$data.'", "'.$review.'", "'.$dtcreate.'")';
                $res = $this->mysqli()->query($sql);
                if (!$res) {
                    $this->log($this->organize_msg($value['id'], $value['type'], 'OK', 'APIデータのINSERTに失敗しました！'), __LINE__);
                } else {
                    $this->log($this->organize_msg($value['id'], $value['type'], 'OK', 'APIデータをINSERTしました。'), __LINE__);
                }
            }
            if ($flg == 1) {
                $flg = 0;
                $max = $max+100;
                $min = $key+1;
            }
        }
    }

    /**
     * Request for the api id
     * Like the curl
     *
     * @param  array $row contains 'api_id' and 'type'
     * @return array $res
     */
    private function request_api($row) {
        $res = array();

        switch ($row['type']) {
            case 'gourmet':
                $cmd = 'curl -d "grant_type=client_credentials&client_id='.self::GOURMET_CLIENT_ID.'&client_secret='.self::GOURMET_CLIENT_SECRET.'&scope=epark.shop.readonly epark.genre.readonly epark.cities.readonly" https://auth.eparkgourmet.com/api/access_token';
                $get_access_token = json_decode(shell_exec($cmd));
                if (!(isset($get_access_token->access_token))) {
                    $this->log($this->organize_msg($row['api_id'], $row['type'], 'OK', 'WARN : グルメのアクセストークンがありません。処理をスキップします。'), __LINE__);
                    break;
                }
                $access_token = $get_access_token->access_token;

                // get the data
                $cmd = 'curl -H "Authorization: Bearer '.$access_token.'" "https://api.eparkgourmet.com/api/ex1/shop/'.$row['api_id'].'"';
                $arr_res = json_decode(shell_exec($cmd), JSON_UNESCAPED_UNICODE);
                // empty data or egCD does not exists
                if (isset($arr_res['code']) && isset($arr_res['message'])) {
                    $this->log($this->organize_msg($row['api_id'], $row['type'], $arr_res['code'], $arr_res['message']), __LINE__);
                    break;
                }

                $res['data'] = json_encode($arr_res, JSON_UNESCAPED_UNICODE);

                break;

            case 'dental':
            case 'fdoc_dental':
                if ($row['type'] === 'dental') {
                    $cmd        = 'curl "https://='.self::HAISHA_ID.'&id='.$row['api_id'].'"';
                    $review_cmd = 'curl "https://='.self::HAISHA_ID.'&id='.$row['api_id'].'&rows=2&sort=0"';
                } else {
                    $cmd        = 'curl "https://='.self::FDOC_ID.'&id='.$row['api_id'].'"';
                    $review_cmd = 'curl "https://='.self::FDOC_ID.'&id='.$row['api_id'].'&rows=2&sort=0"';
                }
                $arr_res        = json_decode(shell_exec($cmd), JSON_UNESCAPED_UNICODE);
                $review_arr_res = json_decode(shell_exec($review_cmd), JSON_UNESCAPED_UNICODE);

                if ($arr_res['status'] > 0 && isset($arr_res['errors'])) {
                    $this->log($this->organize_msg($row['api_id'], $row['type'], $arr_res['status'], $arr_res['errors']), __LINE__);
                    break;
                }

                // check if api id has review
                if ((int)$review_arr_res['num_found'] > 0) {
                    $res['review'] = json_encode($review_arr_res, JSON_UNESCAPED_UNICODE);
                }

                // if both is empty then return empty string
                if (empty($arr_res) && !isset($res['review'])) {
                    $this->log($this->organize_msg($row['api_id'], $row['type'], $arr_res['status'], 'WARN : このIDはAPIデータとレビューがありません。'), __LINE__);
                    break;
                }

                $res['data'] = json_encode($arr_res, JSON_UNESCAPED_UNICODE);
                break;

            default:
                $this->log($this->organize_msg($row['api_id'], $row['type'], 'OTHER', '"typeが不正です"'), __LINE__);
                break;
        }

        return $res;
    }

    /**
     * Get the shop id first
     *
     * @return object|array empty array if no record found
     */
    private function get_shop_id() {
        $sql = "SELECT
    DISTINCT post_meta.meta_value AS api_id,
    (SELECT
            postmeta3.meta_value
        FROM
            {$this->prefix}postmeta postmeta3
        WHERE
            postmeta3.post_id = post.ID
                AND postmeta3.meta_key = 'ppc_api_source' ORDER BY post.ID LIMIT 1) AS type
FROM
    {$this->prefix}posts post
        INNER JOIN
    {$this->prefix}postmeta post_meta ON post.ID = post_meta.post_id
WHERE
    post.post_type = 'shop'
        AND post.post_status = 'private'
        AND post_meta.meta_key = 'ppc_api_id'";

        $res = $this->mysqli()->query($sql);
        return $res->num_rows > 0?$res:array();
    }

    /**
     * Mysqli connect
     */
    private function mysqli() {
        $conn = new mysqli(self::DB_HOST, $this->db_user, $this->db_pass, $this->db_name);
        $conn->set_charset("utf8");
        if ($conn->connect_error) {
            $this->log('Error: Connection failed: ' . mysqli_connect_error(), __LINE__);
            exit;
        }

        return $conn;
    }

    /**
     * Organize the log message for requesting the
     * api data.
     *
     * @param  int    $id     api id
     * @param  string $type   gourmet, dental and fdoc_dental
     * @param  string $status api data status, if there's error then it will give 400 or 500 or 0 if none
     * @param  string $msg    error message
     * @return string         complete and organized log message
     */
    private function organize_msg($id, $type, $status, $msg) {
        return str_pad($id, 13).' '.str_pad('「'.$type.'」', 20).' '.str_pad($status, 5).'"'.$msg.'"';
    }

    /**
     * Access and error log
     * 使い方: $this->log('message', __LINE__);
     *
     * @param  string $msg  Error or access log
     * @param  string $line Line number
     */
    private function log($msg, $line) {
        $timezone = new DateTime(null, new DateTimeZone('Asia/Tokyo'));
        $date = $timezone->format('Y-m-d');
        $fullDate = $timezone->format('Y-m-d H:i:s');
        $fullpath = self::API_LOG_DIR.'/'.$date.'-'.str_replace('_', '', $this->prefix).'.log';
        try {
            if (!file_exists(self::API_LOG_DIR)) {
                mkdir(self::API_LOG_DIR, 0777, true);
            }

             // ファイルが存在を確認します。
            $mode = !file_exists($fullpath)?'w':'a';

            $fp = fopen($fullpath, $mode);

            fwrite($fp, $fullDate . ' ' . str_pad('LINE#' . $line, 13) . ' ' . $msg . PHP_EOL);

            fclose($fp);
        } catch (\Exception $e) {

        }

    }

}

$api = new GetApiData($argv);
$api->execute();

