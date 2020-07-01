<?php
namespace App\ORM\Master;

use App\ORM\AppBaseORM;
use DB;
use CdLog;
use Exception;


/**
 * m_client ORM
 *
 * @author generator
 */
class MClient extends AppBaseORM
{

    protected $table = 'm_client';

    protected $primaryKey = 'CLIENTID';

    protected $fillable = [
        'CLIENTCOMPANYID',
        'CLIENTPAYMENTID',
        'AGENTCLIENTCOMPANYID',
        'VIEWINVOICE',
        'CLIENTCODE',
        'CLIENTNAME',
        'CLIENTNAMEKANA',
        'RESPONSIBLENAME',
        'STUFFNAME',
        'TEL',
        'FAX',
        'ZIPCODE',
        'AREAID',
        'LOCALGOVERNMENTCODE',
        'ADDRESS2',
        'ADDRESS3',
        'ADDRESS4',
        'MAIL1',
        'MAIL2',
        'MAIL3',
        'MAIL4',
        'MAIL5',
        'STUFFMOBILETEL',
        'CLIENTRANK',
        'CENTERID',
        'CARRIERSTYPE',
        'CUSHONINGTYPE',
        'MARKETSITEPASSWORD',
        'THRESHOLD',
        'POSTAGE',
        'ORDERFLG',
        'COOKDELIUSERID',
        'COOKDELIOFFICEID',
        'CLIENTSOURCE',
        'SEPARATEITEMDISCOUNTTYPE',
        'SEPARATEITEMDISCOUNTVALUE',
        'CLIENTCAPACITY',
        'STARTTRADINGDATE',
        'ENDTRADINGDATE',
        'ORDERLIMITDAYNUM',
        'LASTLOGINDATETIME',
        'REMARKS',
        'CREATEDATETIME',
        'CREATEUSERID',
        'UPDATEDATETIME',
        'UPDATEUSERID',
        'DELETEFLAG'
    ];

    /**
     * 削除フラグON
     *
     * @var integer
     */
    private $delFlgOn = 1;

    /**
     * 得意先の基本情報を取得
     * 主にエクスポートに使用CSVされます。
     * There are times when a tables alternative
     * name is in alphabet because it will
     * depend on the setSearchParam().
     *
     * @param  array $request POSTリクエストパラメータ
     * @return array $res     取得結果
     */
    public function getClientBasicCsvData($request)
    {
        $sql = <<<SQL
        SELECT
            NULL, -- TYPE
            -- 得意先法人情報
            A.CLIENTID, -- 得意先ID
            CONCAT('\'', A.CLIENTCODE) AS CLIENTCODE, -- お客様番号

            -- 請求情報
            CONCAT('\'', company.PAYMENTCODE) AS COMPANYCODE,   -- 得意先法人コード
            company.PAYMENTNAME as COMPANYNAME,                 -- 得意先法人名
            CONCAT('\'', C.PAYMENTCODE) AS PAYMENTCODE,         -- 請求先コード
            C.PAYMENTNAME,                                      -- 請求先名
            A.VIEWINVOICE AS VIEWINVOICEKBN,                    -- 請求書の閲覧コード
            CASE
                WHEN A.VIEWINVOICE = 0 THEN '禁止'
                WHEN A.VIEWINVOICE = 1 THEN '許可'
                ELSE NULL END AS VIEWINVOICE, -- 請求書の閲覧名称

            -- 基本情報
            A.CLIENTNAME,      -- 得意先名
            A.CLIENTNAMEKANA,  -- 得意先名カナ
            A.RESPONSIBLENAME, -- 責任者名
            A.STUFFNAME,       -- 担当者名
            A.TEL,             -- 代表電話番号
            A.FAX,             -- FAX番号
            CONCAT('\'', A.ZIPCODE) AS ZIPCODE, -- 郵便番号
            CONCAT('\'', A.LOCALGOVERNMENTCODE) AS LOCALGOVERNMENTCODE, -- 住所1（都道府県コード）
            client_gov.LOCALGOVERNMENTNAME,-- 住所1（都道府県名）
            A.ADDRESS2,        -- 住所2（市区町村）
            A.ADDRESS3,        -- 住所3（町域）
            A.ADDRESS4,        -- 住所4
            A.MAIL1,           -- メールアドレス1
            A.MAIL2,           -- メールアドレス2
            A.MAIL3,           -- メールアドレス3
            A.MAIL4,           -- メールアドレス4
            A.MAIL5,           -- メールアドレス5
            A.STUFFMOBILETEL,  -- 担当者携帯番号
            A.REMARKS,         -- 備考

            -- 販売情報
            rank.CLIENTRANKID AS CLIENTRANKID,  -- 得意先ランクコード
            rank.CLIENTRANK AS CLIENTRANKNAME,  -- 得意先ランク名称
            A.MARKETSITEPASSWORD, -- 販売サイトパスワード
            A.THRESHOLD, -- 閾値
            A.POSTAGE,   -- 送料
            A.ORDERFLG AS ORDERFLAGCODE,  -- 受注フラグコード
            CASE
                WHEN A.ORDERFLG = 1 THEN '受注可能'
                WHEN A.ORDERFLG = 2 THEN '受注不可'
                ELSE NULL END AS ORDERFLG,  -- 受注フラグ名称
            CONCAT('\'', E.CODE) AS USERCODE, -- クックデリ担当者コード
            E.NAME AS USERNAME,             -- クックデリ担当者名前
            A.AREAID,                       -- エリアID
            CONCAT(area.AREA1NAME, '/', area.AREA2NAME, '/', area.AREA3NAME, '/', area.AREA4NAME, '/', area.AREA5NAME, '/', area.AREA6NAME) AS AREANAME, -- エリア名称
            A.CLIENTSOURCE,                 -- 得意先ソースコード
            source.CLIENTSOURCENAME,        -- 得意先ソース名
            A.SEPARATEITEMDISCOUNTTYPE AS SEPARATEITEMDISCOUNTTYPEKUBUN, -- 単品商品値引区分コード
            CASE
                WHEN A.SEPARATEITEMDISCOUNTTYPE = 0 THEN '値引きなし'
                WHEN A.SEPARATEITEMDISCOUNTTYPE = 1 THEN '金額値引'
                WHEN A.SEPARATEITEMDISCOUNTTYPE = 2 THEN '%値引'
                ELSE  NULL END AS SEPARATEITEMDISCOUNTTYPE, -- 単品商品値引区分名称
            A.CLIENTCAPACITY, -- 定員数
            DATE_FORMAT(A.STARTTRADINGDATE, '%Y/%m/%d') AS STARTTRADINGDATE,   -- 取引開始日
            DATE_FORMAT(A.ENDTRADINGDATE, '%Y/%m/%d') AS ENDTRADINGDATE,       -- 取引終了日

            -- 販売情報
            delivery.CLIENTDELIVERYID,  -- 納品先ID
            delivery.RECEIPTNAME,       -- 納品先名
            delivery.RECEIPTNAMEKANA,   -- 納品先名カナ
            CONCAT('\'', delivery.RECEIPTZIPCODE) AS RECEIPTZIPCODE, -- 納品先郵便番号
            CONCAT('\'', delivery.RECEIPTLOCALGOVERNMENTCODE) AS RECEIPTLOCALGOVERNMENTCODE, -- 納品先住所１（都道府県）コード
            receipt_gov.LOCALGOVERNMENTNAME AS RECEIPTLOCALGOVERNMENTNAME, -- 納品先住所１（都道府県）
            delivery.RECEIPTADDRESS2,   -- 納品先住所２（市区町村）
            delivery.RECEIPTADDRESS3,   -- 納品先住所３（町域）
            delivery.RECEIPTADDRESS4,   -- 納品先住所４
            delivery.RECEIPTTEL,        -- 納品先電話番号
            delivery.RECEIPTFAX,        -- 納品先FAX番号
            delivery.RECEIPTMAIL,       -- 納品先メールアドレス
            CLIENT_DELIVERY_SUNDAY.DELIVERYTIMEID AS DELIVERY_SUNDAY,                 -- 日曜日納品時間コード
            CLIENT_DELIVERY_SUNDAY.DELIVERYTIMENAME AS DELIVERY_SUNDAY_TIMENAME,      -- 日曜日納品時間名称
            CLIENT_DELIVERY_MONDAY.DELIVERYTIMEID AS DELIVERY_MONDAY,                 -- 月曜日納品時間コード
            CLIENT_DELIVERY_MONDAY.DELIVERYTIMENAME AS DELIVERY_MONDAY_TIMENAME,      -- 月曜日納品時間名称
            CLIENT_DELIVERY_TUESDAY.DELIVERYTIMEID AS DELIVERY_TUESDAY,               -- 火曜日納品時間コード
            CLIENT_DELIVERY_TUESDAY.DELIVERYTIMENAME AS DELIVERY_TUESDAY_TIMENAME,    -- 火曜日納品時間名称
            CLIENT_DELIVERY_WEDNESDAY.DELIVERYTIMEID AS DELIVERY_WEDNESDAY,           -- 水曜日納品時間コード
            CLIENT_DELIVERY_WEDNESDAY.DELIVERYTIMENAME AS DELIVERY_WEDNESDAY_TIMENAME,-- 水曜日納品時間名称
            CLIENT_DELIVERY_THURSDAY.DELIVERYTIMEID AS DELIVERY_THURSDAY,             -- 木曜日納品時間コード
            CLIENT_DELIVERY_THURSDAY.DELIVERYTIMENAME AS DELIVERY_THURSDAY_TIMENAME,  -- 木曜日納品時間名称
            CLIENT_DELIVERY_FRIDAY.DELIVERYTIMEID AS DELIVERY_FRIDAY,                 -- 金曜日納品時間名称
            CLIENT_DELIVERY_FRIDAY.DELIVERYTIMENAME AS DELIVERY_FRIDAY_TIMENAME,      -- 金曜日納品時間名称
            CLIENT_DELIVERY_SATURDAY.DELIVERYTIMEID AS DELIVERY_SATURDAY,             -- 土曜日納品時間コード
            CLIENT_DELIVERY_SATURDAY.DELIVERYTIMENAME AS DELIVERY_SATURDAY_TIMENAME,  -- 土曜日納品時間名称
            delivery.HOLIDAYDELIVERYFLAG AS HOLIDAYDELIVERYFLAGKUBUN, -- 祝日配送フラグコード
            CASE
                WHEN delivery.HOLIDAYDELIVERYFLAG = 0 THEN '祝日配送しない'
                WHEN delivery.HOLIDAYDELIVERYFLAG = 1 THEN '祝日配送する'
                ELSE NULL END AS HOLIDAYDELIVERYFLAG, -- 祝日配送フラグ名称

            A.ORDERLIMITDAYNUM, -- 注文可能期限日数
            center.CENTERID,    -- センターID
            center.CENTERNAME,  -- センター名
            A.CARRIERSTYPE AS CARRIERSTYPEKUBUN, -- 運送会社コード
            CASE
                WHEN A.CARRIERSTYPE = 0 THEN 'ヤマト運輸'
                WHEN A.CARRIERSTYPE = 1 THEN '佐川急便'
                WHEN A.CARRIERSTYPE = 2 THEN 'ルート便'
                ELSE NULL END AS CARRIERSTYPE, -- 運送会社名
            A.CUSHONINGTYPE AS CUSHONINGTYPEKUBUN, -- 緩衝材区分コード
            CASE
                WHEN A.CUSHONINGTYPE = 0 THEN 'なし'
                WHEN A.CUSHONINGTYPE = 1 THEN 'エアパッキン'
                WHEN A.CUSHONINGTYPE = 2 THEN 'ミラマット'
                ELSE NULL END AS CUSHONINGTYPE -- 緩衝材区分名称
        FROM
            m_client A

            -- 得意先配送設定マスタ
            INNER JOIN m_client_delivery delivery ON A.CLIENTID = delivery.CLIENTID AND delivery.DELETEFLAG = 0

            -- 得意先ランク
            LEFT JOIN m_client_rank rank ON A.CLIENTRANK = rank.CLIENTRANKID AND rank.DELETEFLAG = 0

            -- 都道府県名取得
            INNER JOIN m_localgovernment client_gov ON client_gov.CODE = A.LOCALGOVERNMENTCODE AND client_gov.DELETEFLAG = 0

            -- 配送情報の都道府県名取得
            INNER JOIN m_localgovernment receipt_gov ON receipt_gov.CODE = delivery.RECEIPTLOCALGOVERNMENTCODE AND receipt_gov.DELETEFLAG = 0

            -- 得意先ソース名取得
            LEFT JOIN m_client_source source ON A.CLIENTSOURCE = source.CLIENTSOURCEID AND source.DELETEFLAG = 0

            -- センター名取得
            INNER JOIN m_center center ON A.CENTERID = center.CENTERID AND center.DELETEFLAG = 0

            -- ユーザーマスタ
            INNER JOIN m_user E ON A.COOKDELIUSERID = E.USERID AND E.DELETEFLAG = 0

            -- 得意先法人情報
            INNER JOIN m_client_company company ON company.CLIENTCOMPANYID = A.CLIENTCOMPANYID AND company.DELETEFLAG = 0

            -- 請求先
            INNER JOIN m_client_company C ON C.CLIENTCOMPANYID = A.CLIENTPAYMENTID AND C.DELETEFLAG = 0

             -- エリアマスタ
            LEFT JOIN m_area area ON area.ID = A.AREAID AND area.DELETEFLAG = 0

            -- 納品曜日・時間 日曜日
            LEFT JOIN
                (SELECT client_delivery_weekday.CLIENTID, client_delivery_weekday.DELIVERYTIMEID, delivery_time.DELIVERYTIMENAME
                FROM m_client_delivery_weekday client_delivery_weekday
                INNER JOIN m_delivery_time delivery_time ON client_delivery_weekday.DELIVERYTIMEID = delivery_time.DELIVERYTIMEID
                WHERE client_delivery_weekday.WEEKDAYTYPE = 0 AND delivery_time.DELETEFLAG = 0) AS CLIENT_DELIVERY_SUNDAY ON CLIENT_DELIVERY_SUNDAY.CLIENTID = A.CLIENTID

            -- 納品曜日・時間 月曜日
            LEFT JOIN
                (SELECT client_delivery_weekday.CLIENTID, client_delivery_weekday.DELIVERYTIMEID, delivery_time.DELIVERYTIMENAME FROM m_client_delivery_weekday client_delivery_weekday
                INNER JOIN m_delivery_time delivery_time ON client_delivery_weekday.DELIVERYTIMEID = delivery_time.DELIVERYTIMEID
                WHERE client_delivery_weekday.WEEKDAYTYPE = 1 AND delivery_time.DELETEFLAG = 0) AS CLIENT_DELIVERY_MONDAY ON CLIENT_DELIVERY_MONDAY.CLIENTID = A.CLIENTID

            -- 納品曜日・時間 火曜日
            LEFT JOIN
                (SELECT client_delivery_weekday.CLIENTID, client_delivery_weekday.DELIVERYTIMEID, delivery_time.DELIVERYTIMENAME FROM m_client_delivery_weekday client_delivery_weekday
                INNER JOIN m_delivery_time delivery_time ON client_delivery_weekday.DELIVERYTIMEID = delivery_time.DELIVERYTIMEID
                WHERE client_delivery_weekday.WEEKDAYTYPE = 2 AND delivery_time.DELETEFLAG = 0) AS CLIENT_DELIVERY_TUESDAY ON CLIENT_DELIVERY_TUESDAY.CLIENTID = A.CLIENTID

            -- 納品曜日・時間 水曜日
            LEFT JOIN
                (SELECT client_delivery_weekday.CLIENTID, client_delivery_weekday.DELIVERYTIMEID, delivery_time.DELIVERYTIMENAME FROM m_client_delivery_weekday client_delivery_weekday
                INNER JOIN m_delivery_time delivery_time ON client_delivery_weekday.DELIVERYTIMEID = delivery_time.DELIVERYTIMEID
                WHERE client_delivery_weekday.WEEKDAYTYPE = 3 AND delivery_time.DELETEFLAG = 0) AS CLIENT_DELIVERY_WEDNESDAY ON CLIENT_DELIVERY_WEDNESDAY.CLIENTID = A.CLIENTID

            -- 納品曜日・時間 木曜日
            LEFT JOIN
                (SELECT client_delivery_weekday.CLIENTID, client_delivery_weekday.DELIVERYTIMEID, delivery_time.DELIVERYTIMENAME FROM m_client_delivery_weekday client_delivery_weekday
                INNER JOIN m_delivery_time delivery_time ON client_delivery_weekday.DELIVERYTIMEID = delivery_time.DELIVERYTIMEID
                WHERE client_delivery_weekday.WEEKDAYTYPE = 4 AND delivery_time.DELETEFLAG = 0) AS CLIENT_DELIVERY_THURSDAY ON CLIENT_DELIVERY_THURSDAY.CLIENTID = A.CLIENTID

            -- 納品曜日・時間 金曜日
            LEFT JOIN
                (SELECT client_delivery_weekday.CLIENTID, client_delivery_weekday.DELIVERYTIMEID, delivery_time.DELIVERYTIMENAME FROM m_client_delivery_weekday client_delivery_weekday
                INNER JOIN m_delivery_time delivery_time ON client_delivery_weekday.DELIVERYTIMEID = delivery_time.DELIVERYTIMEID
                WHERE client_delivery_weekday.WEEKDAYTYPE = 5 AND delivery_time.DELETEFLAG = 0) AS CLIENT_DELIVERY_FRIDAY ON CLIENT_DELIVERY_FRIDAY.CLIENTID = A.CLIENTID

            -- 納品曜日・時間 土曜日
            LEFT JOIN
                (SELECT client_delivery_weekday.CLIENTID, client_delivery_weekday.DELIVERYTIMEID, delivery_time.DELIVERYTIMENAME FROM m_client_delivery_weekday client_delivery_weekday
                INNER JOIN m_delivery_time delivery_time ON client_delivery_weekday.DELIVERYTIMEID = delivery_time.DELIVERYTIMEID
                WHERE client_delivery_weekday.WEEKDAYTYPE = 5 AND delivery_time.DELETEFLAG = 0) AS CLIENT_DELIVERY_SATURDAY ON CLIENT_DELIVERY_SATURDAY.CLIENTID = A.CLIENTID
        WHERE
            A.DELETEFLAG = :DELETE_FLAG
SQL;

        // パラメータ
        $params[':DELETE_FLAG'] = config('const.DELETE_FLAG.OFF');

        // 検索条件を設定する
        $this->setSearchParam($request, $sql, $params);
        $sql.= "\n ORDER BY A.CLIENTID ASC";
        $res = parent::select($sql, $params);
        return $res;
    }

    /**
     * 得意先のユニット情報を取得
     * 主にエクスポートに使用CSVされます。
     * There are times when a tables alternative
     * name is in alphabet because it will
     * depend on the setSearchParam().
     *
     * @param  array $request POSTリクエストパラメータ
     * @return array $res     取得結果
     */
    public function getClientUnitCsvData($request)
    {
        $menuOrderCntColumns = [
            'BASICSUNBREAKFASTORDERCOUNT',  // 基本日曜朝食注文数
            'BASICSUNLUNCHORDERCOUNT',      // 基本日曜昼食注文数
            'BASICSUNDINNERORDERCOUNT',     // 基本日曜夕食注文数
            'BASICSUNELSEORDERCOUNT',       // 基本日曜その他注文数
            'BASICMONBREAKFASTORDERCOUNT',  // 基本月曜朝食注文数
            'BASICMONLUNCHORDERCOUNT',      // 基本月曜昼食注文数
            'BASICMONDINNERORDERCOUNT',     // 基本月曜夕食注文数
            'BASICMONELSEORDERCOUNT',       // 基本月曜その他注文数
            'BASICTUEBREAKFASTORDERCOUNT',  // 基本火曜朝食注文数
            'BASICTUELUNCHORDERCOUNT',      // 基本火曜昼食注文数
            'BASICTUEDINNERORDERCOUNT',     // 基本火曜夕食注文数
            'BASICTUEELSEORDERCOUNT',       // 基本火曜その他注文数
            'BASICWEDBREAKFASTORDERCOUNT',  // 基本水曜朝食注文数
            'BASICWEDLUNCHORDERCOUNT',      // 基本水曜昼食注文数
            'BASICWEDDINNERORDERCOUNT',     // 基本水曜夕食注文数
            'BASICWEDELSEORDERCOUNT',       // 基本水曜その他注文数
            'BASICTHUBREAKFASTORDERCOUNT',  // 基本木曜朝食注文数
            'BASICTHULUNCHORDERCOUNT',      // 基本木曜昼食注文数
            'BASICTHUDINNERORDERCOUNT',     // 基本木曜夕食注文数
            'BASICTHUELSEORDERCOUNT',       // 基本木曜その他注文数
            'BASICFRIBREAKFASTORDERCOUNT',  // 基本金曜朝食注文数
            'BASICFRILUNCHORDERCOUNT',      // 基本金曜昼食注文数
            'BASICFRIDINNERORDERCOUNT',     // 基本金曜夕食注文数
            'BASICFRIELSEORDERCOUNT',       // 基本金曜その他注文数
            'BASICSATBREAKFASTORDERCOUNT',  // 基本土曜朝食注文数
            'BASICSATLUNCHORDERCOUNT',      // 基本土曜昼食注文数
            'BASICSATDINNERORDERCOUNT',     // 基本土曜夕食注文数
            'BASICSATELSEORDERCOUNT'        // 基本土曜その他注文数
        ];
        $sql = <<<SQL
        SELECT
            NULL, -- TYPE
            A.CLIENTID,                            -- 得意先ID
            unit.ID AS UNITID,                     -- ユニットID
            unit.CLIENTUNITNAME,                   -- ユニット名
            unit.ORDERCAPACITY,                    -- 発注上限数
            business_cat.CLIENTBUSINESSCATEGORYID, -- 業種ID
            business_cat.CATEGORYNAME,             -- 業種
            menu_cnt.BASICSUNBREAKFASTORDERCOUNT,  -- 基本日曜朝食注文数
            menu_cnt.BASICSUNLUNCHORDERCOUNT,      -- 基本日曜昼食注文数
            menu_cnt.BASICSUNDINNERORDERCOUNT,     -- 基本日曜夕食注文数
            menu_cnt.BASICSUNELSEORDERCOUNT,       -- 基本日曜その他注文数
            menu_cnt.BASICMONBREAKFASTORDERCOUNT,  -- 基本月曜朝食注文数
            menu_cnt.BASICMONLUNCHORDERCOUNT,      -- 基本月曜昼食注文数
            menu_cnt.BASICMONDINNERORDERCOUNT,     -- 基本月曜夕食注文数
            menu_cnt.BASICMONELSEORDERCOUNT,       -- 基本月曜その他注文数
            menu_cnt.BASICTUEBREAKFASTORDERCOUNT,  -- 基本火曜朝食注文数
            menu_cnt.BASICTUELUNCHORDERCOUNT,      -- 基本火曜昼食注文数
            menu_cnt.BASICTUEDINNERORDERCOUNT,     -- 基本火曜夕食注文数
            menu_cnt.BASICTUEELSEORDERCOUNT,       -- 基本火曜その他注文数
            menu_cnt.BASICWEDBREAKFASTORDERCOUNT,  -- 基本水曜朝食注文数
            menu_cnt.BASICWEDLUNCHORDERCOUNT,      -- 基本水曜昼食注文数
            menu_cnt.BASICWEDDINNERORDERCOUNT,     -- 基本水曜夕食注文数
            menu_cnt.BASICWEDELSEORDERCOUNT,       -- 基本水曜その他注文数
            menu_cnt.BASICTHUBREAKFASTORDERCOUNT,  -- 基本木曜朝食注文数
            menu_cnt.BASICTHULUNCHORDERCOUNT,      -- 基本木曜昼食注文数
            menu_cnt.BASICTHUDINNERORDERCOUNT,     -- 基本木曜夕食注文数
            menu_cnt.BASICTHUELSEORDERCOUNT,       -- 基本木曜その他注文数
            menu_cnt.BASICFRIBREAKFASTORDERCOUNT,  -- 基本金曜朝食注文数
            menu_cnt.BASICFRILUNCHORDERCOUNT,      -- 基本金曜昼食注文数
            menu_cnt.BASICFRIDINNERORDERCOUNT,     -- 基本金曜夕食注文数
            menu_cnt.BASICFRIELSEORDERCOUNT,       -- 基本金曜その他注文数
            menu_cnt.BASICSATBREAKFASTORDERCOUNT,  -- 基本土曜朝食注文数
            menu_cnt.BASICSATLUNCHORDERCOUNT,      -- 基本土曜昼食注文数
            menu_cnt.BASICSATDINNERORDERCOUNT,     -- 基本土曜夕食注文数
            menu_cnt.BASICSATELSEORDERCOUNT        -- 基本土曜その他注文数
        FROM
            m_client A

            -- 請求先
            INNER JOIN m_client_company C ON C.CLIENTCOMPANYID = A.CLIENTPAYMENTID AND C.DELETEFLAG = 0

            -- ユーザーマスタ
            INNER JOIN m_user E ON A.COOKDELIUSERID = E.USERID AND E.DELETEFLAG = 0

            -- 得意先ユニットマスタ
            INNER JOIN m_client_unit unit ON A.CLIENTID = unit.CLIENTID AND unit.DELETEFLAG = 0

            -- 得意先業種マスタ
            LEFT JOIN m_client_business_category business_cat ON unit.CLIENTBUSINESSCATEGORYID = business_cat.CLIENTBUSINESSCATEGORYID AND business_cat.DELETEFLAG = 0

            -- 得意先献立基本食数マスタ
            LEFT JOIN m_client_menu_ordercount menu_cnt ON unit.ID = menu_cnt.CLIENTUNITID AND menu_cnt.DELETEFLAG = 0
        WHERE
            A.DELETEFLAG = :DELETEFLAG
SQL;

        // パラメータ
        $params[':DELETEFLAG'] = config('const.DELETE_FLAG.OFF');

        // 検索条件を設定する
        $this->setSearchParam($request, $sql, $params);
        $sql.= "\n ORDER BY A.CLIENTID ASC";
        $res = parent::select($sql, $params);

        return [
            'records'            => $res,
            'hasParams'          => count($params) > 1??true,
            'menuOrderCntColumns'=> $menuOrderCntColumns,
        ];
    }

    /**
     * 得意先の献立情報を取得
     * 主にエクスポートに使用CSVされます。
     * There are times when a tables alternative
     * name is in alphabet because it will
     * depend on the setSearchParam().
     *
     * @param  array $request POSTリクエストパラメータ
     * @return array $res     取得結果
     */
    public function getClientMenuCsvData($request)
    {
        $sql = <<<SQL
        SELECT
            NULL, -- TYPE
            A.CLIENTID,                 -- 得意先ID
            unit.ID AS UNITID,          -- 得意先ユニットID
            menu_type.CLIENTMENUTYPEID, -- 得意先献立種類ID
            menu_type.PARENTCLIENTMENUTYPEID, -- 親得意先献立プランID
            menu_type.MENUTYPEID,       -- 献立プランID
            na_menu_type.MENUTYPENAME,  -- 献立プラン名
            menu_type.OPTIONMENUTYPEID, -- オプション献立プランID
            option_na_menu_type.OPTIONMENUTYPENAME, -- オプション献立プラン名
            menu_type.AUTOORDERFLAG, -- 自動発注フラグ名称
            CASE WHEN menu_type.AUTOORDERFLAG = 0 THEN '自動発注しない' ELSE '自動発注する' END AS AUTOORDERFLAGCODE, -- 自動発注フラグコード
            DATE_FORMAT(menu_type.APPLICATIONDATEFROM, '%Y/%m/%d') AS APPLICATIONDATEFROM,  -- 適用開始日
            DATE_FORMAT(menu_type.APPLICATIONDATETO, '%Y/%m/%d') AS APPLICATIONDATETO,      -- 適用終了日
            menu_type_price.ID AS MENUTYPEPRICEID, -- 得意先ユニット献立種類価格情報ID

            -- 朝食
            menu_type_price.DISCOUNTTYPEBREAKFAST AS DISCOUNTTYPEBREAKFASTKUBUN, -- 朝食値引種別コード
            CASE
                WHEN menu_type_price.DISCOUNTTYPEBREAKFAST = 0 THEN 'なし'
                WHEN menu_type_price.DISCOUNTTYPEBREAKFAST = 1 THEN '金額値引き'
                WHEN menu_type_price.DISCOUNTTYPEBREAKFAST = 2 THEN '％値引き'
                ELSE NULL END AS DISCOUNTTYPEBREAKFAST, -- 朝食値引種別名称
            menu_type_price.DISCOUNTVALUEBREAKFAST, -- 朝食値引値
            menu_type_price.BREAKFASTPRICE,         -- 朝食基準価格
            menu_type.BREAKFAST_ENABLE AS BREAKFAST_ENABLECODE, -- 朝食注文有無コード
            CASE WHEN menu_type.BREAKFAST_ENABLE = 0 THEN '無効' ELSE '有効' END AS BREAKFAST_ENABLE, -- 朝食注文有無名称

            -- 昼食
            menu_type_price.DISCOUNTTYPELUNCH AS DISCOUNTTYPELUNCHKUBUN, -- 昼食値引種別区分
            CASE
                WHEN menu_type_price.DISCOUNTTYPELUNCH = 0 THEN 'なし'
                WHEN menu_type_price.DISCOUNTTYPELUNCH = 1 THEN '金額値引き'
                WHEN menu_type_price.DISCOUNTTYPELUNCH = 2 THEN '％値引き'
                ELSE NULL END AS DISCOUNTTYPELUNCH, -- 昼食値引種別
            menu_type_price.DISCOUNTVALUELUNCH, -- 昼食値引値
            menu_type_price.LUNCHPRICE,         -- 昼食基準価格
            menu_type.LUNCH_ENABLE AS LUNCH_ENABLECODE, -- 昼食注文有無コード
            CASE WHEN menu_type.LUNCH_ENABLE = 0 THEN '無効' ELSE '有効' END AS LUNCH_ENABLE, -- 昼食注文有無名称

            -- 夕食
            menu_type_price.DISCOUNTTYPEDINNER AS DISCOUNTTYPEDINNERKUBUN, -- 夕食値引種別区分
            CASE
                WHEN menu_type_price.DISCOUNTTYPEDINNER = 0 THEN 'なし'
                WHEN menu_type_price.DISCOUNTTYPEDINNER = 1 THEN '金額値引き'
                WHEN menu_type_price.DISCOUNTTYPEDINNER = 2 THEN '％値引き'
                ELSE NULL END AS DISCOUNTTYPEDINNER, -- 夕食値引種別
            menu_type_price.DISCOUNTVALUEDINNER, -- 夕食値引値
            menu_type_price.DINNERPRICE,        -- 夕食基準価格
            menu_type.DINNER_ENABLE AS DINNER_ENABLECODE, -- 夕食注文有無コード
            CASE WHEN menu_type.DINNER_ENABLE = 0 THEN '無効' ELSE '有効' END AS DINNER_ENABLE, -- 夕食注文有無名称

            -- その他
            menu_type_price.DISCOUNTTYPEELSE AS DISCOUNTTYPEELSEKUBUN, -- 夕食値引種別区分
            CASE
                WHEN menu_type_price.DISCOUNTTYPEELSE = 0 THEN 'なし'
                WHEN menu_type_price.DISCOUNTTYPEELSE = 1 THEN '金額値引き'
                WHEN menu_type_price.DISCOUNTTYPEELSE = 2 THEN '％値引き'
                ELSE NULL END AS DISCOUNTTYPEELSE, -- その他値引種別
            menu_type_price.DISCOUNTVALUEELSE,  -- その他値引値
            menu_type_price.ELSEPRICE,          -- その他基準価格
            menu_type.ELSE_ENABLE AS ELSE_ENABLECODE, -- その他注文有無コード
            CASE WHEN menu_type.ELSE_ENABLE = 0 THEN '無効' ELSE '有効' END AS ELSE_ENABLE -- その他注文有無名称
        FROM
            m_client A

            -- 得意先ユニットマスタ
            INNER JOIN m_client_unit unit ON A.CLIENTID = unit.CLIENTID AND unit.DELETEFLAG = 0

            -- 得意先献立種類マスタ
            LEFT JOIN m_client_menu_type menu_type ON unit.ID = menu_type.CLIENTUNITID AND menu_type.DELETEFLAG = 0

            -- 献立種類マスタ
            LEFT JOIN m_na_menu_type na_menu_type ON na_menu_type.MENUTYPEID = menu_type.MENUTYPEID AND na_menu_type.DELETEFLAG = 0

            -- 献立種類マスタ
            LEFT JOIN m_na_option_menu_type option_na_menu_type ON option_na_menu_type.OPTIONMENUTYPEID = menu_type.OPTIONMENUTYPEID AND option_na_menu_type.DELETEFLAG = 0

            -- 得意先献立種類価格情報マスタ
            LEFT JOIN m_client_menu_type_price menu_type_price ON menu_type.CLIENTMENUTYPEID = menu_type_price.CLIENTMENUTYPEID AND menu_type_price.DELETEFLAG = 0

            -- 請求先
            INNER JOIN m_client_company C ON C.CLIENTCOMPANYID = A.CLIENTPAYMENTID AND C.DELETEFLAG = 0

            -- ユーザーマスタ
            INNER JOIN m_user E ON A.COOKDELIUSERID = E.USERID AND E.DELETEFLAG = 0
        WHERE
            A.DELETEFLAG = :DELETEFLAG
SQL;
        // パラメータ
        $params[':DELETEFLAG'] = config('const.DELETE_FLAG.OFF');

        // 検索条件を設定する
        $this->setSearchParam($request, $sql, $params);
        $sql.= "\n ORDER BY A.CLIENTID ASC, unit.ID ASC, menu_type.CLIENTMENUTYPEID ASC, menu_type.MENUTYPEID DESC, menu_type.OPTIONMENUTYPEID DESC";
        // $sql.= "\n ORDER BY A.CLIENTID ASC, menu_type.MENUSEQUENCE ASC, menu_type.MENUTYPEID DESC, menu_type.OPTIONMENUTYPEID DESC";
        $res = parent::select($sql, $params);

        return $res;
    }




    /**
     * 対象の得意先1レコードを取得
     *
     * @param string $clientId [得意先ID]
     *
     * @return array
     */
    public function getClientInfoById($clientId)
    {
        $params = [];

        $sql = "SELECT *";
        $sql .= " FROM m_client";
        $sql .= " WHERE";
        $sql .= " CLIENTID = :CLIENTID";
        $sql .= " AND DELETEFLAG = :DELETEFLAG";

        $params[':CLIENTID'] = $clientId;
        $params[':DELETEFLAG'] = config('const.DELETE_FLAG.OFF');

        $result = DB::select($sql, $params);

        $result = json_decode(json_encode($result), true);

        return $result;
    }


    /**
     * 対象の得意先のコードを取得
     *
     * @param string $clientId [得意先ID]
     *
     * @return array
     */
    public function getClientCode($clientId)
    {
        $params = [];

        $sql = "SELECT CLIENTCODE";
        $sql .= " FROM m_client";
        $sql .= " WHERE";
        $sql .= " CLIENTID = :CLIENTID";
        $sql .= " AND DELETEFLAG = :DELETEFLAG";

        $params[':CLIENTID'] = $clientId;
        $params[':DELETEFLAG'] = config('const.DELETE_FLAG.OFF');

        $result = DB::select($sql, $params);
        if (empty($result)) {
            return false;
        }

        $result = json_decode(json_encode($result[0]), true);

        return $result['CLIENTCODE'];

    }

    /**
     * 受注管理/商品一括差替え時のメール送信用
     * 得意先と代理店のメールアドレスを取得
     *
     */
    public function getCientMailInfo($clientId)
    {
        $params = [];

        $sql = "SELECT";
        $sql .= " CLI.CLIENTCODE"; // 得意先コード
        $sql .= " ,CLI.CLIENTNAME"; // 得意先名
        $sql .= " ,CLI.MAIL1 AS CLIENTMAIL"; // 得意先メールアドレス
        $sql .= " ,AGNT.PAYMENTMAIL AS AGENCYMAIL"; // 代理店メールアドレス
        $sql .= " FROM m_client CLI";
        $sql .= " LEFT OUTER JOIN m_client_company AGNT ON CLI.AGENTCLIENTCOMPANYID = AGNT.CLIENTCOMPANYID"; // 代理店
        $sql .= " AND AGNT.DELETEFLAG = :AGNT_DELETEFLAG";
        $sql .= " WHERE";
        $sql .= " CLIENTID = :CLIENTID";
        $sql .= " AND CLI.DELETEFLAG = :CLI_DELETEFLAG";

        $params[':CLIENTID'] = $clientId;
        $params[':CLI_DELETEFLAG'] = config('const.DELETE_FLAG.OFF');
        $params[':AGNT_DELETEFLAG'] = config('const.DELETE_FLAG.OFF');

        $result = DB::select($sql, $params);
        if (empty($result)) {
            return false;
        }

        $result = json_decode(json_encode($result[0]), true);

        return $result;

    }

    /**
     * 得意先に紐づく請求先の締め日を取得
     *
     * @param int $clientId 得意先ID
     *
     * @return object
     */
    public function getTimeLimitByClientId($clientId)
    {
        $sql = "SELECT";
        $sql .= " COMP.TIMELIMIT";
        $sql .= " FROM m_client CLT";
        $sql .= " INNER JOIN m_client_company COMP ON CLT.CLIENTPAYMENTID = COMP.CLIENTCOMPANYID";
        $sql .= " WHERE CLT.CLIENTID = :CLIENTID";
        $sql .= " AND CLT.DELETEFLAG = :CLT_DELETEFLAG";
        $sql .= " AND COMP.DELETEFLAG = :COMP_DELETEFLAG";

        $params = [
            ':CLIENTID' => $clientId,
            ':CLT_DELETEFLAG' => config('const.DELETE_FLAG.OFF'),
            ':COMP_DELETEFLAG' => config('const.DELETE_FLAG.OFF')
        ];

        $result = parent::select($sql, $params);

        return $result;
    }


    /**
     * 得意先に紐づく請求先情報を取得
     * 請求データ作成時に使用
     *
     * @param int $clientId
     *
     * @return object
     */
    public function getPaymentInfoByClientId($clientId)
    {
        $sql = <<<SQL
SELECT 
CLCP.CLIENTCOMPANYID AS PAYMENTID
,CLCP.BILLTYPE
,CLCP.TIMELIMIT
,CLCP.BILLDIVIDEFLAG
FROM m_client CLT
INNER JOIN m_client_company CLCP ON CLT.CLIENTPAYMENTID = CLCP.CLIENTCOMPANYID
WHERE CLIENTID = :CLIENTID
AND CLT.DELETEFLAG = :CLT_DELETEFLAG
AND CLCP.DELETEFLAG = :CLCP_DELETEFLAG
SQL;
        $params = [
            ':CLIENTID' => $clientId,
            ':CLT_DELETEFLAG' => config('const.DELETE_FLAG.OFF'),
            ':CLCP_DELETEFLAG' => config('const.DELETE_FLAG.OFF')
        ];

        $result = parent::select($sql, $params);

        return $result;

    }

}
