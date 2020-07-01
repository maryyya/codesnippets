<?php

/**
 * This is the template html
 * for the 新入社員 schedule.
 * The schedules will be in
 * checkbox form.
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
        [mwform_checkbox name="checkbox" class="' . $field_name . '" id="ppc_doui_participant_checkbox_' . $field_name . '" children="' . $item['display_name'] . '" separator=","]
    </div>
    ',
    wpautop($item['shortcode'])
);
