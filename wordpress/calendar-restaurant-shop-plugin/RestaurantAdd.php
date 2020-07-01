<?php

/**
 * This will hold the adding
 * and editing of the 食堂売店
 * calendar.
 */
class RestaurantAdd
{
    /**
     * Main function
     * It will choose whether a check of add.
     *
     * @return array
     */
    public function main($param)
    {
        $res = array(
            'status' => 'ng',
            'msg'    => '',
            'data'   => array()
        );

        if ($this->insert($param)) {
            $res = array(
                'status' => 'ok',
                'msg'    => '',
                'data'   => array()
            );
        }

        return $res;
    }

    /**
     * Insert into menu
     *
     * @param  array $param
     * @return boolean true if inserted, otherwise false.
     */
    private function insert($param)
    {
        global $wpdb;

        $resMetaCols = array(
            'post_id'
            , 'menu_order'
            , 'label'
            , 'menu'
            , 'price'
            , 'dtcreate'
        );

        $resShop     = array();
        $resShopMeta = array();

        foreach ($param as $key => $value) {
            if ($key === 'weekdays') {
                if (!empty($value)) {
                    $resShop[] = array_filter($value);
                }
            } elseif ($key === 'info') {
                if (!empty($value)) {
                    $resShop[] = $value;
                }
            } elseif ($key === 'data') {
                if (!empty($value)) {
                    $resShopMeta[] = $value;
                }
            }
        }
        $cols        = array_merge_recursive($resShop[1], $resShopMeta[0]);

        $resMeta = true;
        $wpdb->query('START TRANSACTION');
            $resShops    = $this->insertResShop($resShop, $param['term_id']);
            $resShopMeta = $this->insertResShopMeta($cols, $param['term_id']);
            if (is_array($resShopMeta)) {
                if ($resShopMeta[0] === false || $resShopMeta[1] === false) {
                    $resMeta = false;
                }
            } else {
                if ($resShopMeta === false) {
                    $resMeta = false;
                }
            }

        $wpdb->query('COMMIT');

        if ($resShops === false || $resMeta === false) {
            $wpdb->query('ROLLBACK');
            return false;
        } else {
            return true;
        }
    }

    /**
     * This functions holds the inserting of
     * the main data like the info「textarea」 only.
     * This only inserts on fukuri_restaurant_shop
     * table only.
     *
     * @param  array $resShop info data only「textarea on the calendar」
     * @param  int   $termID  term id
     * @return boolean        If there's no error in inserting then true otherwise false.
     */
    private function insertResShop($resShop, $termID)
    {
        $cols = array_merge_recursive($resShop[1], $resShop[0]);

        $newCols = array();
        foreach ($cols as $key => $value) {
            if (is_array($value)) {
                $newCols[$value[0]][] = $value;
            } else {
                $newCols[$value][] = $value;
            }
        }

        $infoData = $this->getInfoData($newCols, $termID);
        $insertVal = implode(",\r\n", $infoData['insert']);
        $updateVal = implode("\r\n", $infoData['update']);

        return $this->updateInsertInfo($infoData, $updateVal, $insertVal);
    }

    /**
     * Get the info「textarea on calendar」to be
     * inserted or updated.
     *
     * @param  array $newCols data from input
     * @param  int   $termID  term id of the 食堂売店
     * @return array          This data is already arranged in way that it can be inserted/updated directly.
     */
    private function getInfoData($newCols, $termID)
    {
        global $wpdb;
        $sql = '';
        $updateParam = $insertParam = $insert = $update = array();
        $timezone = new DateTime(null, new DateTimeZone('Asia/Tokyo'));
        $dtcreate = $timezone->format('Y-m-d H:i:s');

        foreach ($newCols as $key => $value) {
            $sql = <<<__SQL
SELECT count(*) as cnt FROM fukuri_restaurant_shop WHERE weekday = %s AND term_id = %d
__SQL;
            $prepare = $wpdb->prepare($sql, $key, $termID);
            $res = $wpdb->get_results($prepare);
            $resVal = $res[0]->cnt;

            if ((int)$resVal > 0) {
                if (is_array($value[0])) {
                    $update[] = 'WHEN weekday = %s AND term_id = %d THEN %s';
                    $updateParam[] = $value[0][0];
                    $updateParam[] = $termID;
                    $updateParam[] = $value[0][1];
                }
            } else {
                $insert[] = '(%d, %s, %s, %s)';
                $insertParam[] = $termID;
                $insertParam[] = $value[0][0];
                $insertParam[] = $value[0][1];
                $insertParam[] = $dtcreate;
            }
        }

        return array(
            'updateParam' => $updateParam,
            'insertParam' => $insertParam,
            'insert'      => $insert,
            'update'      => $update
        );
    }

    /**
     * This function holds the query for updating
     * and inserting into info「textarea on the calendar」 only.
     *
     * @param  array  $infoData  The data to be inserted.
     * @param  string $updateVal The additional query that will be in the update query.
     * @param  string $insertVal The additional query that will be in the insert query.
     * @return boolean           This will return the query.
     */
    private function updateInsertInfo($infoData, $updateVal, $insertVal)
    {
        global $wpdb;
        $updateRes = $insertRes = true;

        $updateSql = <<<__SQL
UPDATE fukuri_restaurant_shop
    SET info = CASE
        {$updateVal}
        ELSE info
    END
__SQL;
        $inSertSql = <<<__SQL
INSERT INTO fukuri_restaurant_shop (term_id, weekday, info, dtcreate)
VALUES {$insertVal}
__SQL;

        if (!empty($insertVal)) {
            $insertPrepare = $wpdb->prepare($inSertSql, $infoData['insertParam']);
            return $wpdb->query($insertPrepare);
        } else {
            $updatePrepare = $wpdb->prepare($updateSql, $infoData['updateParam']);
            return $wpdb->query($updatePrepare);
        }
    }

    /**
     * This function holds the inserting and updating
     * of the calendar's menu, label, and price.
     *
     * @param  array $param  Data input by the user from admin page.
     * @param  int   $termID term id of the category
     * @return boolean       query of the insert or update
     */
    private function insertResShopMeta($param, $termID)
    {
        global $wpdb;

        $data           = $this->getMetaData($param, $termID);
        $updateMenuVal  = $data['updateMenuVal'];
        $updateLabelVal = $data['updateLabelVal'];
        $updatePriceVal = $data['updatePriceVal'];
        $insertVal      = $data['insertVal'];

        $updateSql = <<<__SQL
UPDATE fukuri_restaurant_shop_meta
    SET menu = CASE
        {$updateMenuVal}
        ELSE menu
    END,
    label = CASE
        {$updateLabelVal}
        ELSE label
    END,
    price = CASE
        {$updatePriceVal}
        ELSE price
    END
__SQL;

        $inSertSql = <<<__SQL
INSERT INTO fukuri_restaurant_shop_meta (post_id, menu_order, label, menu, price, dtcreate)
VALUES {$insertVal}
__SQL;

        $updateRes = $insertRes = true;
        if (empty($insertVal) && empty($updateLabelVal)) {
            return true;
        } elseif (empty($insertVal)) {
            $merge = array();
            $merge[] = $data['updateMenuPar'];
            $merge[] = $data['updateLabelPar'];
            $merge[] = $data['updatePricePar'];

            $updatePrepare = $wpdb->prepare($updateSql, call_user_func_array('array_merge', $merge));

            return $wpdb->query($updatePrepare);
        } elseif (!empty($updateMenuVal) && !empty($updateLabelVal) && !empty($updatePriceVal)) {
            $insertPrepare = $wpdb->prepare($inSertSql, $data['insertPar']);
            $insertRes = $wpdb->query($insertPrepare);

            $merge = array();
            $merge[] = $data['updateMenuPar'];
            $merge[] = $data['updateLabelPar'];
            $merge[] = $data['updatePricePar'];

            $updatePrepare = $wpdb->prepare($updateSql, call_user_func_array('array_merge', $merge));
            $updateRes     = $wpdb->query($updatePrepare);

            return array($insertRes, $updateRes);
        } else {
            $insertPrepare = $wpdb->prepare($inSertSql, $data['insertPar']);

            return $wpdb->query($insertPrepare);
        }
    }

    /**
     * Get the metadata for the inserting/updating
     * in fukuri_restaurant_shop_meta table.
     *
     * @param  array $param  data from input
     * @param  int   $termID term id of a category
     * @return array         data that will be inserted/updated.
     */
    private function getMetaData($param, $termID)
    {
        global $wpdb;

        $insert = $updateMenu = $updateLabel = $updatePrice = array();
        $timezone = new DateTime(null, new DateTimeZone('Asia/Tokyo'));
        $dtcreate = $timezone->format('Y-m-d H:i:s');
        $updateMenuParam = $updateLabelParam = $updatePriceParam = $insertParam = array();

        foreach ($param as $key => $value) {
            $val = array_filter($value);
            foreach ($val as $keys => $values) {
                if ($keys === 0) {
                    $sql = <<<__SQL
SELECT ID FROM fukuri_restaurant_shop WHERE weekday = %s AND term_id = %d
__SQL;
                    $prepare = $wpdb->prepare($sql, $values, $termID);
                    $res = $wpdb->get_results($prepare);
                    $checkResShopMeta = $res[0]->ID;
                }

                if (is_array($values)) {
                    if (!empty($values['menu']) && !empty($values['label']) && !empty($values['price'])) {
                        $order = (int)$values['order'];
                        $sqlz  = <<<__SQL
SELECT count(*) as cnt FROM fukuri_restaurant_shop_meta WHERE post_id = %d AND menu_order = %d
__SQL;
                        $preparez = $wpdb->prepare($sqlz, $checkResShopMeta, $order);
                        $resz     = $wpdb->get_results($preparez);
                        $checkRes = $resz[0]->cnt;

                        if ((int)$checkRes > 0) {
                            $updateMenu[]  = 'WHEN post_id = %d AND menu_order = %d THEN %s';
                            $updateLabel[] = 'WHEN post_id = %d AND menu_order = %d THEN %s';
                            $updatePrice[] = 'WHEN post_id = %d AND menu_order = %d THEN %s';

                            $updateMenuParam[]  = array($checkResShopMeta, $order, $values['menu']);
                            $updateLabelParam[] = array($checkResShopMeta, $order, $values['label']);
                            $updatePriceParam[] = array($checkResShopMeta, $order, $values['price']);
                        } else {
                            $insert[] = '(%d, %d, %s, %s, %s, %s)';
                            $insertParam[] = array($checkResShopMeta, $order, $values['label'], $values['menu'], $values['price'], $dtcreate);
                        }
                    } else {
                        $order = (int)$values['order'];
                        $sqlz = <<<__SQL
SELECT count(*) as cnt FROM fukuri_restaurant_shop_meta WHERE post_id = %d AND menu_order = %d
__SQL;
                        $preparez = $wpdb->prepare($sqlz, $checkResShopMeta, $order);
                        $resz = $wpdb->get_results($preparez);
                        $checkRes = $resz[0]->cnt;

                        if ((int)$checkRes > 0) {
                            $updateMenu[]  = 'WHEN post_id = %d AND menu_order = %d THEN %s';
                            $updateLabel[] = 'WHEN post_id = %d AND menu_order = %d THEN %s';
                            $updatePrice[] = 'WHEN post_id = %d AND menu_order = %d THEN %s';

                            $updateMenuParam[]  = array($checkResShopMeta, $order, $values['menu']);
                            $updateLabelParam[] = array($checkResShopMeta, $order, $values['label']);
                            $updatePriceParam[] = array($checkResShopMeta, $order, $values['price']);
                        }
                    }
                }
            }
        }

        $data = array(
            'updateMenu'       => $updateMenu,
            'updateMenuParam'  => $updateMenuParam,
            'updateLabel'      => $updateLabel,
            'updateLabelParam' => $updateLabelParam,
            'updatePrice'      => $updatePrice,
            'updatePriceParam' => $updatePriceParam,
            'insert'           => $insert,
            'insertParam'      => $insertParam
        );

        return $this->arrangeParamData($data);
    }

    /**
     * This function will arrange the paramters
     * for the query in inserting/updating in
     * fukuri_restaurant_shop_meta
     *
     * @return array arranged data that will be inserted/updated
     */
    private function arrangeParamData($param)
    {
        $insertPar = $updateMenuPar = $updateLabelPar = $updatePricePar = array();
        $updateMenuVal  = implode("\r\n", $param['updateMenu']);
        if (!empty($updateMenuVal)) {
            $updateMenuPar = call_user_func_array('array_merge', $param['updateMenuParam']);
        }

        $updateLabelVal = implode("\r\n", $param['updateLabel']);
        if (!empty($updateLabelVal)) {
            $updateLabelPar = call_user_func_array('array_merge', $param['updateLabelParam']);
        }

        $updatePriceVal = implode("\r\n", $param['updatePrice']);
        if (!empty($updatePriceVal)) {
            $updatePricePar = call_user_func_array('array_merge', $param['updatePriceParam']);
        }

        $insertVal = implode(",\r\n", $param['insert']);
        if (!empty($insertVal)) {
            $insertPar = call_user_func_array('array_merge', $param['insertParam']);
        }

        return array(
            'updateMenuPar'  => $updateMenuPar,
            'updateLabelPar' => $updateLabelPar,
            'updatePricePar' => $updatePricePar,
            'updateMenuVal'  => $updateMenuVal,
            'updateLabelVal' => $updateLabelVal,
            'updatePriceVal' => $updatePriceVal,
            'insertVal'      => $insertVal,
            'insertPar'      => $insertPar
        );
    }
}



