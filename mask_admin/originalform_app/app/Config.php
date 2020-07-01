<?php

namespace App;

/**
 * データベース名
 */
// define('DATABASE_NAME', 'test');
define('DATABASE_NAME', '');

/**
 * データベースホスト
 */
// define('DATABASE_HOST', 'localhost');
define('DATABASE_HOST', '');

/**
 * データベースユーザ
 */
// define('DATABASE_USERNAME', 'root');
define('DATABASE_USERNAME', '');

/**
 * データベースパスワード
 */
// define('DATABASE_PASSWORD', '');
define('DATABASE_PASSWORD', '');

/**
 * ログファイルディレクトリ
 */
define('LOG_FILE_DIR', dirname(__FILE__) . '/Logs/');

/**
 * フォームタイプ
 */
define("FORM_TYPE", serialize(
    array(
        1 => array(
            'label'     => '衛生用品（マスク・ジェル等）',
            'table'     => 'originalform_mask',
            'className' => 'InsertMask',
        ),
        2 => array(
            'label'     => '印刷アイデアBOOK',
            'table'     => 'originalform_ideabook',
            'className' => 'InsertIdeaBook',
        ),
    )
));
