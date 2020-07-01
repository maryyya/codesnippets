<?php

require_once 'Config.php';
require __DIR__ . "/../vendor/autoload.php";

use App\Modules\Insert;
use App\Modules\FormList;

/**
 * Class Base
 */
class Base
{
    /**
     * データを取得
     *
     * @param  array $param フォームデータ
     * @return array $data  corresponding data according to 検索条件
     */
    public function getData($param)
    {
        $list = new FormList();
        $data = $list->getAllData($param);
        $data['formTypeList'] = unserialize(FORM_TYPE);

        return $data;
    }

    /**
     * Insert フォームデータ
     *
     * @param int $type フォームタイプ ex.1 - マスクフォーム
     * @return boolean       true if success, else false.
     */
    public function insert($type)
    {
        return (new Insert())->insertFormData($type);
    }
}
