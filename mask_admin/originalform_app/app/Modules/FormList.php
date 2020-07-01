<?php

namespace App\Modules;

/**
 * Class FormList
 *
 * @package App\Modules
 */
class FormList
{
    /**
     * Default limit
     */
    const LIMIT = 50;

    /**
     * Default offset
     */
    const OFFSET = 0;

    /**
     * Default order
     */
    const ORDER = 'ID';

    /**
     * Default order by
     */
    const ORDERBY = 'ASC';

    /**
     * @var Db
     */
    private $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db  = new Db();
    }

    /**
     * テーブル情報とテーブルデータを取得
     *
     * @param array $request フォームポスト
     * @return array          全データ
     */
    public function getAllData($request)
    {
        $return = array(
            'names'    => array(),
            'data'     => array(),
            'count'    => 0,
            'pages'    => 0,
            'formType' => '',
        );

        if (empty($request['type'])) {
            return $return;
        }

        $btn_type  = isset($request['btn_type']) && $request['btn_type'] === 'csv' ? 'csv' : ''; // buttonタイプ csvかsearch
        $tableName = $this->db->getTable($request['type']);                                      // テーブル名
        $tableData = $this->getTableInfo($tableName);                                            // テーブル情報
        $res       = $this->getDbData($request, $tableName, $btn_type);                         // データベースデータ

        $formTypeList  = unserialize(FORM_TYPE);
        $formTypeLabel = $formTypeList[(int) $request['type']]['label'];

        if ($btn_type === 'csv') {
            $param        = array(
                'tableName' => $formTypeLabel,
                'header'    => $tableData['columnComment'],
                'data'      => $res['data'],
            );
            (new Csv)->download($param);
        } else {
            $return['names']    = $tableData['columnComment'];             // テーブルヘッダのため
            $return['data']     = $res['data'];                            // dbデータ
            $return['count']    = $res['count'];                           // 件数
            $return['pages']    = (int) ceil($res['count'] / self::LIMIT); // ページ件数
            $return['formType'] = $formTypeLabel . 'データ';                  // フォームタイプ

        }

        return $return;
    }

    /**
     * テーブル情報
     *
     * @param string $table テーブル名
     * @return array  $tableData テーブル情報
     */
    private function getTableInfo($table)
    {
        $tableData = array();
        $param     = array(
            ':dbname'    => DATABASE_NAME,
            ':tablename' => $table,
        );

        $sql = "SELECT COLUMN_NAME, COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :dbname AND TABLE_NAME = :tablename";
        $res = $this->db->query($sql, $param);
        foreach ($res as $val) {
            $tableData['columnName'][]    = $val['COLUMN_NAME'];
            $tableData['columnComment'][] = $val['COLUMN_COMMENT'];
        }

        return $tableData;
    }

    /**
     * 条件によると、データを取得します。
     *
     * @param array  $request フォームポスト
     * @param string $table   テーブル名
     * @param string $type    csvのためOR検索のため
     * @return array          テーブルデータと件数
     */
    private function getDbData($request, $table, $type)
    {
        $limit = $order = array();
        $limit['limit']   = self::LIMIT;
        $limit['offset']  = isset($request['page_hidden']) && strlen($request['page_hidden']) > 0 ? ($request['page_hidden'] - 1) * self::LIMIT : self::OFFSET;
        $order['order']   = isset($request['order']) ? isset($request['order']) : self::ORDER;
        $order['orderby'] = isset($request['by']) ? isset($request['orderby']) : self::ORDERBY;

        $limitOrder = array(
            'LIMIT' => array($limit['offset'], $limit['limit']),
            'ORDER' => array($order['order'] => $order['orderby']),
        );

        $colAndCond = $this->getColAndSearchCond($request);
        $cols = $colAndCond['cols'];
        $where = $colAndCond['where'];

        if ($type === 'csv') {
            $data  = $this->db->select($table, $cols, $where);
            $count = $this->db->count($table, $where);
        } elseif (!empty($where)) {
            $data  = $this->db->select($table, $cols, $where, $limitOrder);
            $count = $this->db->count($table, $where);
        } else {
            $data  = $this->db->select($table, $cols, array(), $limitOrder);
            $count = $this->db->count($table);
        }

        return array('data' => $data, 'count' => $count);
    }

    /**
     * 検索条件とカラムを取得
     *
     * @param array $request フォームポスト
     * @return array          条件とカラム
     */
    private function getColAndSearchCond($request)
    {
        // note: if all then use * else use array like array(ID, name)
        $cols  = '*';
        $where = array();

        // 受付番号FROM
        if (!empty($request['fromReceiptNo'])) {
            $where["receiptno[>=]"] = $request['fromReceiptNo'];
        }

        // 受付番号TO
        if (!empty($request['toReceiptNo'])) {
            $where["receiptno[<=]"] = $request['toReceiptNo'];
        }

        // 受付日時FROM
        if (!empty($request['fromDate'])) {
            $where["dt_created[>=]"] = $request['fromDate'].' 00:00:00';
        }

        // 受付日時TO
        if (!empty($request['toDate'])) {
            $where["dt_created[<=]"] = $request['toDate'].' 23:59:59';
        }

        return array('where' => $where, 'cols' => $cols);
    }
}
