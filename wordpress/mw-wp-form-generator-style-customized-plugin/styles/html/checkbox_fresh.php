<?php

/**
 * This is the template html
 * for the checkbox with background
 *
 * @param int     $key          index
 * @param array   $item         field data
 * @param string  $require      html require
 * @param string  $description  html desc
 * @param string  $notes        html notes
 * @return string $content      html combined
 */
$field_name = isset($item['field_name']) && !empty($item['field_name']) ? $item['field_name'] : 'doui';
$content .= sprintf(
    '<div class="c_semi_doui">
        [mwform_checkbox name="checkbox" id="ppc_doui_' . $field_name . '" children="' . $item['display_name'] . '" separator=","]
    </div>
    ',
    wpautop($item['shortcode'])
);
