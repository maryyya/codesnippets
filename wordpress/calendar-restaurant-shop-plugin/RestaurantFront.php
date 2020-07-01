<?php

/**
 * This class contains the
 * getting of the contents.
 */
class RestaurantFront
{
    /**
     * Get the information for the 食堂・売店施設
     * It doesnt get the calendar.
     * It's only for the additional information
     * for the 食堂・売店施設.
     *
     * @param array $param It contains the tabid and termids
     * @return json
     */
    public function getInfo($param)
    {
        $res      = array(
            'status' => '',
            'msg'    => '',
            'data'   => array()
        );

        if ($this->checkID((int)$param['term_id']) <= 0) {
            $res = array(
                'status' => 'ng',
                'msg'    => 'Category does not exists.',
            );
        } else {
            $children = get_term_children( $param['term_id'], 'restaurant_cat' );
            $childdetail = array();
            foreach ($children as $key => $value) {
                $child = get_term_by('id', $value, 'restaurant_cat');
                $childdetail[] = array('id'=>$value, 'name'=>$child->name);
            }

            $res = array(
                'status' => 'ok',
                'msg'    => '',
                'data'   => array(
                    'id'       => $param['term_id'],
                    'termName' => (int)$param['term_id'] === 52 ? str_replace('札幌', '', get_term($param['term_id'])->name) : str_replace('札幌市', '', get_term($param['term_id'])->name),
                    'siteUrl'  => $param['site_url'],
                    'dir'      => $param['dir'],
                    'type'     => 'calendar-front',
                    'content'  => $this->getContents($param),
                    'children' => $childdetail,
                )
            );
        }

        return $res;
    }

    /**
     * This will get the calendar information for
     * before and after button.
     *
     * @param  array  $param  Data needed to get the calendar info
     * @param  object $search Dependency injection for Search Class from restaurant plugin
     * @return array  $res    this will have the calendar with date
     */
    public function getNavContent($param, $search)
    {
        $mainParam = array(
            'term_id'      => $param['term_id'],
            'term'         => $param['term'],
            'site_url'     => $param['site_url'],
            'dir'          => $param['dir'],
            'display_type' => 'front',
            'nav_type'     => $param['nav_type'],
            'sunday'       => $param['sunday'],
            'monday'       => $param['monday']
        );
        $res = array(
            'status' => 'ok',
            'msg'    => '',
            'data'   => array(
                'tab_id'   => $param['tab_id'],
                'site_url' => $param['site_url'],
                'term_id'  => $param['term_id'],
                'term'     => $param['term'],
                // 'term'     => strpos($param['term'], '売店') === false ? '日替り定食' : 'お弁当',
                'res'      => $search->mainSearch($mainParam),
            )
        );

        return $res;
    }

    /**
     * Get the contents of the term
     *
     * @param  array $param It contains term_id.
     * @param  object $search Dependency injection for Search Class from restaurant plugin
     * @return array | boolean Array if term has data else false.
     */
    public function getCalendar($param, $search)
    {
        $calendar = $children   = $content = array();
        $childrenID = get_term_children($param['id'], 'restaurant_cat');

        foreach ($childrenID as $key => $value) {
            $children[$value] = get_term($value)->name;
        }

        foreach ($children as $key => $value) {
            if (strpos($value, '売店') === false || $key === 54) {
                $mainParam = array(
                    'term_id'      => $key,
                    'term'         => $value,
                    'site_url'     => $param['site_url'],
                    'dir'          => $param['dir'],
                    'display_type' => 'front',
                );

                $content[$key.'|'.$value.'|'.$param['site_url'].'|'.$param['dir']] = $search->mainSearch($mainParam);
            }
        }

        $res      = array(
            'status' => 'ok',
            'msg'    => '',
            'data'   => $content
        );

        return $res;
    }

    /**
     * Check the term id if it exists.
     *
     * @param  int $id term_id
     * @return int id or 0 if id does not exists.
     */
    private function checkID($id)
    {
        global $wpdb;
        $sql = <<<__SQL
SELECT count(term_id) as cnt
FROM fukuri_terms
WHERE term_id = %d
__SQL;
        $prepare = $wpdb->prepare($sql, $id);
        $res = $wpdb->get_results($prepare);

        return (int)$res[0]->cnt;
    }

    /**
     * Get the contents of the term
     *
     * @param  array $param It contains term_id.
     * @return array | boolean Array if term has data else false.
     */
    private function getContents($param)
    {
        $calendar = $children   = $content = array();
        $childrenID = get_term_children($param['term_id'], 'restaurant_cat');
        $childrenNm = array_map(function($val) { return get_term($val)->name; }, $childrenID);

        foreach ($childrenID as $key => $value) {
            $children[$value] = get_term($value)->name;
        }

        foreach ($children as $key => $value) {
            $pdf = get_field('menu_pdf', 'restaurant_cat_'.$key);
            $filesize = $pdf['id'] !== false ? size_format(filesize( get_attached_file( $pdf['id'] ) ) ) : '';
            $pdf_detail = get_term_by('id', $key, 'restaurant_cat');
            $pdf_file   = explode('_', $pdf_detail->slug);
            $content[] = array(
                'id'             => $key,
                'name'           => $value,
                'term'           => strpos($value, '売店') === false ? '日替り' : 'お弁当',
                'place'          => get_field('place', 'restaurant_cat_'.$key),
                'holiday'        => get_field('holiday', 'restaurant_cat_'.$key),
                'business_hours' => get_field('business_hours', 'restaurant_cat_'.$key),
                'capacity'       => get_field('capacity', 'restaurant_cat_'.$key),
                'menu_pdf'       => get_field('menu_pdf', 'restaurant_cat_'.$key),
                'feature_img'    => get_field('feature_img', 'restaurant_cat_'.$key),
                'operator'       => get_field('operator', 'restaurant_cat_'.$key),
                'site_url'       => $param['site_url'],
                'filesize'       => $filesize,
                'pdf_file'       => 'menu_'.$pdf_file[0],
            );
        }

        return count($content) > 0 ? $content: false;
    }
}
