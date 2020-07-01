<?php

class Import
{
    /**
     * CSV FILE
     * This are in parts okay to avoid
     * memory limit, since csv rows
     * is many.
     */
    const CSV_FILE = 'csv/2019_09_25_kaitou_final.csv';

    /**
     * 団体コード
     */
    const CSV_DANTAI_FILE = 'csv/2019_09_30_dantai_final.csv';

    /**
     * This will determine if the sql file
     * will put a begin in the beginning of
     * the file which is for checking or
     * not and immediately inserts file.
     * 0: insert immediately.
     * 1: add begin in the first in beginning of file.
     * 2: その他 begin import insert immediately
     * 3: その他 import insert immediately
     * 4: 団体 begin import insert immediately
     * 5: 団体 import insert immediately
     */
    const FLG = 4;

    /**
     * SQL DIRECTORY
     */
    const SQL_DIR = 'sql';

    /**
     * SQL FILE FOR KAITOU
     */
    const SQL_FILE = self::FLG<1?self::SQL_DIR.'/insert_kaitou.sql':self::SQL_DIR.'/begin_insert_kaitou.sql';

    /**
     * SQL FILE FOR SONOTA
     */
    const SQL_SONOTA_FILE = self::FLG>2?self::SQL_DIR.'/insert_kaitou_sonota.sql':self::SQL_DIR.'/begin_insert_kaitou_sonota.sql';

    /**
     * SQL FILE FOR PREFECTURE
     */
    const SQL_DANTAIMEI_FILE = self::FLG===5?self::SQL_DIR.'/update_dantai.sql':self::SQL_DIR.'/begin_update_dantai.sql';


    /**
     * Csv columns
     *
     * @var array
     */
    private $csv_col = array(
        'no',
        'shin_kanri_no',
        'serial_code',
        'dantai_id',
        'data_kbn',
        'convention_mei',
        'kaigi_shubetsu',
        'sankashasu',
        'sankashasu2',
        'gaikokujinsu',
        'sankakokusu',
        'gyojinaiyo',
        'kaisaikaijo',
        'sokai_hole',
        'recep_hole',
        'shitsusu',
        'hiroma',
        'kaisaijiki',
        'kaisaibasho',
        'ketteijiki',
        'yosan',
        'uchikifukin',
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
        'biko',
    );

    /**
     * int type in the database
     *
     * @var array
     */
    private $int_val = array(
        3  => 'dantai_id',
        7  => 'sankashasu',
        9  => 'gaikokujinsu',
        10 => 'sankakokusu',
        13 => 'sokai_hole',
        14 => 'recep_hole',
        15 => 'shitsusu',
        16 => 'hiroma',
        20 => 'yosan',
        21 => 'uchikifukin',
    );

    /**
     * その他 cols
     *
     * @var array
     */
    private $sonota_cols = array(
        11 => 'gyojinaiyo',    // 行事内容
        12 => 'kaisaikaijo',   // 開催会場
        19 => 'ketteijiki',    // 決定時期
    );

        /**
     * その他 cols
     *
     * @var array
     */
    private $sonota_vals = array(
        'gyojinaiyo'  => 'e',   // 行事内容
        'kaisaikaijo' => 'f',   // 開催会場
        'ketteijiki'  => 'g',   // 決定時期
    );

    /**
     * 会議種別
     *
     * @var array
     */
    private $kaigi_shubetsu = array(
        '一般社団法人'      => '01',
        '公益社団法人'      => '02',
        '一般財団法人'      => '03',
        '公益財団法人'      => '04',
        '社会福祉法人'      => '05',
        '特定非営利活動法人'   => '06',
        '認定特定非営利活動法人' => '07',
    );

    /**
     * Import
     *
     * @return
     */
    public function import() {
        if (!file_exists(self::CSV_FILE)) {
            echo 'FILE DOES NOT EXISTS';
            return;
        }

        require_once('mysql.php');
        $this->db = new Mysql;
        $mysql = $this->db->conn();
        if (self::FLG == 4 || self::FLG == 5) {
            // fix data for 団体名
            $this->insert_dantaimei($mysql);
        } else if (self::FLG > 1) {
            // fix data for その他
            $this->insert_sonota($mysql);
        } else {
            // insert all
            $this->insert_all($mysql);
        }
    }

    /**
     * Insert all kaitou data
     *
     * @param object $mysql
     * @return
     */
    private function insert_all($mysql) {
        $sql = '';
        $sql.= self::FLG<1?'':"begin; \n";


        // $sql = 'INSERT INTO kaitou ('.implode(', ', $this->csv_col).') VALUES'."\n";
        $handle = fopen( self::CSV_FILE, 'r' );
        if ( $handle ) {
            $key = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if ($key > 0) {
                    $insert_val = array();
                    $insert_col = array();
                    foreach ($data as $key_val => $value) {
                        if (in_array($key_val, array_keys($this->int_val))) {
                            if (strlen($value) > 0) {
                                $insert_val[] = (int)$value;
                                $insert_col[] = $this->csv_col[$key_val];
                            }
                        } else {
                            $insert_col[] = $this->csv_col[$key_val];
                            $insert_val[] = '"'.$mysql->real_escape_string(trim($value)).'"';
                        }
                    }

                    $sql.= "INSERT INTO kaitou (".implode(', ', $insert_col).') VALUES (';
                    $sql.= implode(', ', $insert_val);
                    $sql.= "); \n";
                }

                $key++;
            }
        }

        fclose($handle);

        $fp = fopen(self::SQL_FILE, 'a');
        fwrite($fp, $sql);
        fclose($fp);
    }

    /**
     * For その他 data of
     * 行事内容 - e gyojinaiyo
     * 開催会場 - f kaisaikaijo
     * 決定時期 - g ketteijiki
     *
     * @param object $mysql
     * @return
     */
    private function insert_sonota($mysql) {
        $sql = '';
        $sql.= self::FLG>2?'':"begin; \n";

        $handle = fopen( self::CSV_FILE, 'r' );
        if ( $handle ) {
            $key = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if ($key > 0) {
                    $insert_val = array();
                    $insert_col = array();
                    $no = sprintf('%04d', $data[0]);
                    foreach ($data as $key_val => $value) {
                        if (in_array($key_val, array_keys($this->sonota_cols))) {
                            $sonota = $this->sonota_cols[$key_val];
                            $sonota_key = $this->sonota_vals[$sonota];
                            if (strpos($value, $sonota_key.'. その他') !== false) {
                                if (preg_match('/'.$sonota_key.'. その他（(.*?)）/', $value, $match) == 1) {
                                    // var_dump($match[1]);
                                    // var_dump($no);
                                    // $insert_val[] = $match[1];
                                    $sonota_key_change = $sonota_key === 'g'?'i':$sonota_key;
                                    $insert_val[] = array(
                                        'col' => 'type_'.$sonota_key_change.'_ext',
                                        'val'  => $match[1]
                                    );
                                    // $insert_col[] = 'type_'.$sonota_key_change.'_ext';
                                    // $insert_val['type_'.$sonota_key_change.'_ext'][] = $match[1];
                                }
                            }
                        }
                    }


                    // if (!empty($insert_col[0]) && !empty($insert_val[0])) {
                    if (!empty($insert_val)) {
                        foreach ($insert_val as $insert_val_item) {
                            $sql.= "UPDATE kaitou SET ".$insert_val_item['col'].' = '.'"'.$mysql->real_escape_string(trim($insert_val_item['val'])).'"'.' WHERE no = "'.$no.'"';
                            $sql.= "; \n";
                        }
                    }
                }

                $key++;
            }
        }

        fclose($handle);

        $fp = fopen(self::SQL_SONOTA_FILE, 'a');
        fwrite($fp, $sql);
        fclose($fp);

        // echo '<pre>';
        // echo $sql;
        // echo '</pre>';
    }

    /**
     * Fix the 団体名データ
     *
     * @param object $mysql
     * @return
     */
    private function insert_dantaimei($mysql) {
        $sql = '';
        $sql.= self::FLG==4?"begin; \n":'';

        $handle = fopen( self::CSV_DANTAI_FILE, 'r' );
        if ( $handle ) {
            $key = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if ($key > 0) {
                    $update_dantaimei     = array();
                    $update_hojinkakucode = array();
                    $dantai_code = $data[1];
                    foreach ($data as $key_val => $value) {
                        if ($key_val == 6) {
                            if (strpos($value, '一般社団法人') === 0) {
                                $update_dantaimei[] = preg_replace('/一般社団法人/', '', $value);
                                $update_hojinkakucode[] = $this->kaigi_shubetsu['一般社団法人'];

                            } else if (strpos($value, '公益社団法人') === 0) {
                                $update_dantaimei[] = preg_replace('/公益社団法人/', '', $value);
                                $update_hojinkakucode[] = $this->kaigi_shubetsu['公益社団法人'];

                            } else if (strpos($value, '一般財団法人') === 0) {
                                $update_dantaimei[] = preg_replace('/一般財団法人/', '', $value);
                                $update_hojinkakucode[] = $this->kaigi_shubetsu['一般財団法人'];

                            } else if (strpos($value, '公益財団法人') === 0) {
                                $update_dantaimei[] = preg_replace('/公益財団法人/', '', $value);
                                $update_hojinkakucode[] = $this->kaigi_shubetsu['公益財団法人'];

                            } else if (strpos($value, '社会福祉法人') === 0) {
                                $update_dantaimei[] = preg_replace('/社会福祉法人/', '', $value);
                                $update_hojinkakucode[] = $this->kaigi_shubetsu['社会福祉法人'];

                            } else if (strpos($value, '特定非営利活動法人') === 0) {
                                $update_dantaimei[] = preg_replace('/特定非営利活動法人/', '', $value);
                                $update_hojinkakucode[] = $this->kaigi_shubetsu['特定非営利活動法人'];

                            } else if (strpos($value, '認定特定非営利活動法人') === 0) {
                                $update_dantaimei[] = preg_replace('/認定特定非営利活動法人/', '', $value);
                                $update_hojinkakucode[] = $this->kaigi_shubetsu['認定特定非営利活動法人'];
                            }
                        }
                    }

                    if (!empty($update_dantaimei[0]) && !empty($update_hojinkakucode[0])) {
                        $sql.= "UPDATE dantai SET dantaimei = ".'"'.$mysql->real_escape_string(trim($update_dantaimei[0])).'"';
                        $sql.= ", hojinkakucode = ".'"'.$mysql->real_escape_string(trim($update_hojinkakucode[0])).'"'.' WHERE dantai_code = "'.$dantai_code.'"';
                        $sql.= "; \n";
                    }
                }

                $key++;
            }
        }

        fclose($handle);

        $fp = fopen(self::SQL_DANTAIMEI_FILE, 'a');
        fwrite($fp, $sql);
        fclose($fp);

        // echo '<pre>';
        // echo $sql;
        // echo '</pre>';
    }
}

$import = new Import;
