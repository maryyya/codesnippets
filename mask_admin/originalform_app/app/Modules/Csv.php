<?php

namespace App\Modules;

/**
 * Class CsvDownload
 *
 * @package App\Modules
 */
class Csv
{
    /**
     * Encoding for the csv.
     * The first one.
     */
    const FIRST_ENCODING_TYPE = 'SJIS-win';

    /**
     * Encoding for the csv.
     * The second one.
     */
    const SECOND_ENCODING_TYPE = 'UTF-8';

    /**
     * CSVダウンロード
     *
     * @param array $param ヘッダ、テーブル名、データベースのデータ
     */
    public function download($param)
    {
        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=" . $param['tableName'] . '.csv');

        // to create a temporary csv file
        $handle = fopen('php://output', 'w');
        if ($handle !== false) {

            // first is the header
            fputcsv($handle, self::convert_encoding($param['header']));

            // the proceed with writing the data from the db
            foreach ($param['data'] as $key => $items) {
                $final_data = [];
                foreach ($items as &$val) {
                    $final_data[] = self::convert_encoding($val);
                }
                fputcsv($handle, $final_data);
            }
        }

        fpassthru($handle);
        exit;
    }

    /**
     * Convert encoding so that the
     * csv can be read.
     *
     * @param array|string $param Default data. Array is for the header and the string is for the db data.
     * @return array|string        Converted data
     */
    private function convert_encoding($param)
    {
        if (is_array($param)) {
            $data = array();
            foreach ($param as $key => $val) {
                $data[$key] = mb_convert_encoding($val, self::FIRST_ENCODING_TYPE, self::SECOND_ENCODING_TYPE);
            }

            return $data;
        } else {
            return mb_convert_encoding($param, self::FIRST_ENCODING_TYPE, self::SECOND_ENCODING_TYPE);
        }
    }
}
