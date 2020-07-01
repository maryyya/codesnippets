<?php

/**
 * Mysql class
 */
class Mysql
{
    /**
     * データベースホスト名
     */
    const DB_HOST = '';

    /**
     * データベース名前
     */
    const DB_NAME = '';

    /**
     * データベースホスト名
     */
    const DB_USER = '';

    /**
     * データベースパスワード
     */
    const DB_PASS = '';

    /**
     * For log class
     *
     * @var object
     */
    public $log;

    /**
     * Constructor
     */
    public function __construct() {
        require_once('log.php');
        $this->log = new Log;
    }

    /**
    * Mysqli connect
    */
    public function conn() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli(self::DB_HOST, self::DB_USER, self::DB_PASS, self::DB_NAME);
        $conn->set_charset("utf8");
        if ($conn->connect_error) {
            $this->log->write('ERROR MESSAGE: MYSQL CONNECTION ERROR', __LINE__, 'mysql.php');
            exit;
        }


        return $conn;
    }

    /**
     * Select query that
     * returns array. This is
     * without parameters.
     *
     * @param string $sql
     * @return array database data in array form.
     */
    public function select_res_arr($sql) {
        $mysql = $this->conn();
        $res = array();
        try {
            $stmt = $mysql->query($sql);
            if ($stmt->num_rows > 0) {
                $key = 0;
                while($row = $stmt->fetch_assoc()) {
                    $res[$key] = $row;
                    $key++;
                }
            }
        } catch (\Exception $e) {
            $this->log->write('ERROR MESSAGE:'.$e->getMessage(), __LINE__, 'mysql.php');
            return array();
        }

        return $res;
    }

    /**
     * For select query. With
     * parameters.
     *
     * @param string $sql
     * @param array $param
     * @return array
     */
    public function select($sql, $param) {
        $mysql = $this->conn();
        $res = array();

        try {
            if (empty($param)) {
                $stmt = $mysql->query($sql);
                if ($stmt->num_rows > 0) {
                    $key = 0;
                    while($row = $stmt->fetch_assoc()) {
                        $res[$key] = $row['code'];
                        $key++;
                    }
                }
            } else {
                $type = $this->prepare_type($param);
                $stmt_param = $this->clean_param($param);
                $stmt = $mysql->prepare($sql);
                $stmt->bind_param($type, ...$stmt_param);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }
        } catch (\Exception $e) {
            $this->log->write('ERROR MESSAGE:'.$e->getMessage(), __LINE__, 'mysql.php');
            return array();
        }

        return $res;
    }

    /**
     * For update query
     *
     * @param string $sql
     * @param array $param
     * @return array
     */
    public function update($sql, $param) {
        $mysql = $this->conn();
        try {
            $type = $this->prepare_type($param);
            $stmt_param = $this->clean_param($param);
            $stmt = $mysql->prepare($sql);
            $stmt->bind_param($type, ...$stmt_param);
            $stmt->execute();
            if ($stmt->error) {
                $this->log->write('ERROR MESSAGE: UPDATE ERROR', __LINE__, 'mysql.php');
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            $this->log->write('ERROR MESSAGE IN UPDATE QUERY:'.$e->getMessage(), __LINE__, 'mysql.php');
            return false;
        }

        return false;
    }

    /**
     * For Insert query
     *
     * @param string $sql
     * @param array $param
     * @return array
     */
    public function insert($sql, $param) {
        $mysql = $this->conn();
        try {
            $type = $this->prepare_type($param);
            $stmt_param = $this->clean_param($param);
            $stmt = $mysql->prepare($sql);
            $stmt->bind_param($type, ...$stmt_param);
            $stmt->execute();
            if ($stmt->error) {
                $this->log->write('ERROR MESSAGE: INSERT ERROR', __LINE__, 'mysql.php');
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            $this->log->write('ERROR MESSAGE IN INSERT QUERY:'.$e->getMessage(), __LINE__, 'mysql.php');
            return false;
        }

        return false;
    }

    /**
     * To escape string type
     *
     * @param array $param post param
     * @return array $data escaped string value from array
     */
    private function clean_param($param) {
        $data = array();
        $mysql = $this->conn();
        foreach($param as $key => $val) {
            if (gettype($val) === 'string') {
                $data[$key] = $mysql->real_escape_string($val);
            }

            $data[$key] = $val;
        }

        return array_values($data);
    }

    /**
     * This is to prepare the type.
     *
     * @param array $param post params
     * @return string $type its like ss or si or di
     */
    private function prepare_type($param) {
        $type = '';
        foreach($param as $val) {
            switch (gettype($val)) {
                case 'string':
                    $type.= 's';
                    break;

                case 'int':
                    $type.= 'i';

                    break;

                case 'double':
                    $type.= 'd';

                    break;

                default:
                    $type.= 's';
                    break;
            }
        }

        return $type;
    }
}
