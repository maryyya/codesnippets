<?php

/**
 * This is the template html
 * for the 氏名
 *
 * Mind that all these fields are
 * required. The code for requiring
 * the fields are in
 * <plugindir/mw-wp-form-generator-style-doginsoken.php>
 * in add_validation().
 *
 * @param int     $key          index
 * @param array   $item         field data
 * @param string  $require      html require
 * @param string  $description  html desc
 * @param string  $notes        html notes
 * @return string $content      html combined
 */
$name = 'participant_name_' . $item['field_name'];
$val_rules = isset($rules[$name]['noempty']) ? (int) $rules[$name]['noempty'] : 0;
$span = $val_rules > 0 ? '<span class="c_required">必須</span>' : '';
$field_name = isset($item['field_name']) && !empty($item['field_name']) ? $item['field_name'] : 'name';
$content .= sprintf(
    '<input type="hidden" name="ppc_participant_separator_%d" value="'.$field_name.'">',
    sprintf('%02d', $field_name)
);
