<?php

/**
 * To clean params before entering
 * into the db or sending into post.
 */
class Clean_Param
{
    /**
     * To clean the parameters.
     * And protect from attacks.
     */
    // public function clean_all($name, $param) {
    //     $data = array();
    //     if (!is_array($param)) {
    //         $data[$name] = $this->clean_string($param);
    //     } else {
    //         foreach($param as $key_item => $item) {
    //             $this->clean_all($key_item, $item);
    //         }
    //     }

    //     return $data;
    // }

    /**
     * To clean the parameters.
     * And protect from attacks.
     */
    public function clean_all($param_key = '', $param, $type = '') {
        $data = array();
        foreach($param as $key_items => $items) {
            if (!is_array($items)) {
                $data[$key_items] = $this->clean_string($items, $type);
                continue;
            }

            foreach($items as $key_item => $item) {
                if (!is_array($item)) {
                    $data[$key_items][$key_item] = $this->clean_string($item, $type);
                    continue;
                }

                foreach($item as $key_val => $val) {
                    if (!is_array($val)) {
                        $data[$key_items][$key_item][$key_val] = $this->clean_string($val, $type);
                        continue;
                    }

                    foreach($val as $key_info => $info) {
                        if (!is_array($info)) {
                            $data[$key_items][$key_item][$key_val][$key_info] = $this->clean_string($info, $type);
                            continue;
                        }

                        foreach($info as $other_key => $other) {
                            $data[$key_items][$key_item][$key_val][$key_info][$other_key] = $this->clean_string($other, $type);
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Clean everything before insert
     *
     * @param string $param
     * @return string $str cleaned param
     * @return string $type database or none
     */
    public function clean_string($param, $type = '') {
        $mb_convert = mb_convert_encoding(trim($param), 'UTF-8', 'UTF-8');
        $str = $type === 'database'? $mb_convert : htmlentities($mb_convert, ENT_QUOTES, 'UTF-8');
        return $str;
    }
}
