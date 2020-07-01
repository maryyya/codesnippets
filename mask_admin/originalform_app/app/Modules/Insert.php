<?php

namespace App\Modules;

use App\Modules\InsertInterface;

/**
 * Class Insert
 *
 * @package App\Modules
 */
class Insert
{
    /**
     * Insertフォームデータ
     * Config.phpにフォームタイプ一覧（FORM_TYPE）があります。
     *
     * @param int $type フォームタイプ ex.1 - マスクフォーム; 2 - 印刷アイデアBookフォーム
     * @return boolean        true if success, else false.
     */
    public function insertFormData($type)
    {
        $param     = $_SESSION;
        $tableList = unserialize(FORM_TYPE);
        $formTypeClass = 'App\\Modules\\'.$tableList[(int) $type]['className'];
        return $this->execInsertInterface($param, new $formTypeClass);
    }

    /**
     * Interfaceを実行
     *
     * @param array     $param フォームデータ
     * @param Interface \App\Modules\InsertInterface $insertInterface
     */
    private function execInsertInterface($param, InsertInterface $insertInterface)
    {
        $insertInterface->insert($param);
    }
}
