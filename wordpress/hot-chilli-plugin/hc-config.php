<?php

return array(
    /**
     * This will hold the limit for
     * the pagination on 一覧ページ.
     */
    'limit' => 10,

    /**
     * This will hold the limit for
     * the pagination on download
     * page on the front.
     */
    'dl_limit' => 9,

    // default response for json
    'response' => array(
        'status' => 'ng',
        'data'   => array(),
        'msg'    => ''
    ),

    // this is the tag list for the file adding
    'taglist' => array(
        'tag1'
        , 'tag2'
        , 'tag3'
        , 'tag4'
        , 'tag5'
        , 'tag6'
        , 'tag7'
        , 'tag8'
        , 'tag9'
        , 'tag10'
    ),

    /**
     * This holds all the messages for
     * the class member and the
     * hot-chilli page.
     */
    'msg' =>
        array(

            /**
             * サーバーエラー
             *
             * This msg is passed to the js file
             * if there is an error on the switch
             * case function in member method.
             */
            'ERR-01' => 'サーバーエラー',

            /**
             * 登録エラー
             *
             * This msg is passed to the js file
             * if there is an error on inserting the
             * new member.
             */
            'ERR-02' => '登録エラー',

            /**
             * 更新エラー
             *
             * This msg is passed to the js file
             * if there is an error on updating the
             * detail page of a member.
             */
            'ERR-03' => '更新エラー',

            /**
             * 削除エラー
             *
             * This msg is passed to the js file
             * if there is an error on deleting a
             * member.
             */
            'ERR-04' => '削除エラー',

            /**
             * id does not exists
             */
            'ERR-05' => 'IDは存在しません。',

            /**
             * id is deleted
             */
            'ERR-06' => 'メンバーIDは既に削除されています。'."\nリロードしてください。",

            /**
             * Loginid is empty
             */
            'ERR-07' => 'ログインIDを入力してください。',

            /**
             * Email is empty
             */
            'ERR-08' => 'メールアドレスを入力してください。',

            /**
             * Password is empty
             */
            'ERR-09' => 'パスワードを入力してください。',

            /**
             * Email validation
             */
            'ERR-10' => 'メールアドレスの形式ではありません。',

            /**
             * Password is lesser than 8
             */
            'ERR-11' => 'パスワードは文字数が足りません。（8桁以上）',

            /**
             * Password should be alphanumeric
             */
            'ERR-12' => 'パスワードは半角英字で入力してください。',

            /**
             * loginid already exists
             */
            'ERR-13' => "ログインID「{n}」は既に存在します。\nCase: This loginid was used but deleted. \n別のものを入力してください。",

            /**
             * Email already exists
             */
            'ERR-14' => "メールアドレス「{n}」は既に存在します。\nCase: This loginid was used but deleted. \n別のものを入力してください。",

            /**
             * If member is set and action is get
             */
            'ERR-15' => 'メンバーIDまたはアクションが設定されていません。',

            /**
             * Action is equal to edit
             */
            'ERR-16' => 'アクションが間違ってます。',

            /**
             * No member detail
             */
            'ERR-17' => 'データが空です。',

            /**
             * No search member input
             */
            'ERR-18' => '検索入力がありません。',

            /**
             * Title is empty
             */
            'ERR-19' => 'タイトルを入力してください。',

            /**
             * Contents is empty
             */
            'ERR-20' => '資料概要を入力してください。',

            /**
             * Date issued is empty
             */
            'ERR-21' => '発行日を入力してください。',

            /**
             * Tag is empty
             */
            'ERR-22' => 'タグを選択してください。',

            /**
             * Thumbnail or image is empty
             */
            'ERR-23' => 'サムネイルを選択してください。',

            /**
             * Date is not valid
             */
            'ERR-24' => '日付の形式ではありません。',

            /**
             * File id and action is not set
             */
            'ERR-25' => 'ファイルIDまたはアクションが設定されていません。',

            /**
             * There's no file id
             */
            'ERR-26' => 'ファイルIDがありません。',

            /**
             * There's no file inputted.
             */
            'ERR-27' => 'ファイルを選択してください。',

            /**
             * Loginid is empty.
             */
            'ERR-28' => 'ログインIDを入力してください。',

            /**
             * Password is empty
             */
            'ERR-29' => 'パスワードを入力してください。',

            /**
             * Login failed
             */
            'ERR-30' => 'ログインに失敗しました。',

            /**
             * Loginid does not exists
             */
            'ERR-31' => 'ログインIDは存在しません。',

            /**
             * File data is empty
             */
            'ERR-32' => 'ファイルデータがありません。',

            /**
             * File data is not complete
             */
            'ERR-33' => 'ファイルデータが完成していません。',

            /**
             * Data not found. This is for search
             */
            'ERR-34' => 'データが見つかりません。',

            /**
             * Multiple file download error
             */
            'ERR-35' => '一括ダウンロードエラー。',

            /**
             *
             */
            'ERR-36' => '',

            /**
             *
             */
            'ERR-37' => '',

            /**
             *
             */
            'ERR-38' => '',

            /**
             *
             */
            'ERR-39' => '',

            /**
             *
             */
            'ERR-40' => '',
        )
);
