<?php

/**
 * This is the template html
 * for the テキストエリアラベルなし
 *
 * @param int     $key          index
 * @param array   $item         field data
 * @param string  $require      html require
 * @param string  $description  html desc
 * @param string  $notes        html notes
 * @return string $content      html combined
 */
$field_name = isset($item['field_name']) && !empty($item['field_name']) ? $item['field_name'] : 'ppc_msg';
$content .= sprintf(
    '<p>%s</p>
    [mwform_textarea name="' . $field_name . '" rows="10"]
    ',
    wpautop($item['shortcode'])
);
