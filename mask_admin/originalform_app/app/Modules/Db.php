<?php

namespace App\Modules;

use App\Library\Medoo;

/**
 * Class Db
 *
 * @package App\Modules
 */
class Db
{
    /**
     * DBタイプ
     */
    const DATABASE_TYPE     = 'mysql';
    const DATABASE_NAME     = DATABASE_NAME;
    const DATABASE_HOST     = DATABASE_HOST;
    const DATABASE_USERNAME = DATABASE_USERNAME;
    const DATABASE_PASSWORD = DATABASE_PASSWORD;
    const DATABASE_CHARSET  = 'utf8';

    /**
     * db instance
     *
     * @var App\Library\Medoo
     */
    protected $db;

    /**
     * Log instance
     *
     * @var \App\Modules\Log
     */
    protected $log;

    /**
     * Db constructor.
     */
    public function __construct()
    {
        // We instantiate Medoo
        $this->db = new Medoo(array(
            'database_type' => self::DATABASE_TYPE,
            'database_name' => self::DATABASE_NAME,
            'server'        => self::DATABASE_HOST,
            'username'      => self::DATABASE_USERNAME,
            'password'      => self::DATABASE_PASSWORD,
            'charset'       => self::DATABASE_CHARSET,
        ));

        // Log instance
        $this->log = new Log();
    }

    /**
     * テーブルの名前を取得
     *
     * @param int $type 1 or 2
     * @return string        corresponding table name
     */
    public function getTable($type)
    {
        $tableList = unserialize(FORM_TYPE);
        return $tableList[(int) $type]['table'];
    }

    /**
     * 件数
     *
     * @param  string $table テーブル名
     * @param  array  $cond  条件
     * @return int    $count 行の数
     */
    public function count($table, $cond = array())
    {
        $count = $this->db->count($table, $cond);
        $query = $this->db->last();
        $error = $this->db->error();

        // $query = 'SELECT * FROM originalform_ideabook';
        // $error = array(
        //     0 => '42S22'
        //     1 => '1054',
        //     2 => "Unknown column 'sname' in 'field list"
        // );

        if (empty($error[2])) {
            $this->log->info('[' . $table . ']件数(COUNT)：' . $query);
        } else {
            $this->log->error('[' . $table . ']エラー件数(COUNT)：' . json_encode($error));
            $this->log->error('[' . $table . ']エラークエリ件数(COUNT)：' . $query);
        }

        return $count;
    }

    /**
     * カスタムクエリ
     *
     * @param  string $sql   SQLステートメント
     * @param  array  $param パラメータ
     * @return array  $res   DBデータ
     */
    public function query($sql, $param = array())
    {
        if (empty($param)) {
            $res = $this->db->query($sql)->fetchAll();
        } else {
            $res = $this->db->query($sql, $param)->fetchAll();
        }

        $query = $this->db->last();
        $error = $this->db->error();

        if (empty($error[2])) {
            $this->log->info('QUERY：' . $query);
        } else {
            $this->log->error('エラー「QUERY」：' . json_encode($error));
            $this->log->error('エラークエリ「QUERY」：' . $query);
        }

        return $res;
    }

    /**
     * Select
     *
     * @param  string       $table      テーブル名
     * @param  array|string $col        カラム一覧
     * @param  array        $cond       条件
     * @param  array        $limitOrder LIMITとOFFSETとORDER
     * @return array|bool               エラーがあったら、FALSE。else 列データ
     */
    public function select($table, $col, $cond = array(), $limitOrder = array())
    {
        $res   = $this->db->select($table, $col, array_merge($cond, $limitOrder));

        $query = $this->db->last();
        $error = $this->db->error();

        if (empty($error[2])) {
            $this->log->info('[' . $table . ']SELECT：' . $query);
        } else {
            $this->log->error('[' . $table . ']エラー「SELECT」：' . json_encode($error));
            $this->log->error('[' . $table . ']エラークエリ「SELECT」：' . $query);
        }

        return $res;
    }

    /**
     * Insert
     *
     * @param  string $table テーブル名
     * @param  array  $param パラメータ
     * @return bool   $res   エラーがあたらFALSE, else TRUE
     */
    public function insert($table, $param)
    {
        $res = false;
        $this->db->insert($table, $param);

        $error = $this->db->error();
        $lastInsertedId = $this->db->id();

        if (empty($error[2]) && !empty($lastInsertedId)) {
            $this->log->info('[' . $table . ']新データ：[ID:' . $lastInsertedId . ']' . json_encode($param, JSON_UNESCAPED_UNICODE));
            $res = true;
        } else {
            $this->log->error('[' . $table . ']エラー「INSERT」：' . json_encode($error));
        }

        return $res;
    }
}
