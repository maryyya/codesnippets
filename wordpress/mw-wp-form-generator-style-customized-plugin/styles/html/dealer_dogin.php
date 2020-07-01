<?php

/**
 * This is the template html
 * for the お取引店
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
$field_name = isset($item['field_name']) && !empty($item['field_name']) ? $item['field_name'] : 'name';
$content .= sprintf(
    '
    <dl class="c_semi_form_dl_04 ppc_mw_input ppc_mw_dealer">
        <dt>お取引店</dt>
        <dd class="ppc_dealer">
            <div class="c_form_group">
                <label for="">北海道銀行　支店名</label>
                [mwform_text name="ppc_hokuriku_' . $field_name . '" show_error="false"]
            </div>
        </dd>
    </dl>
    ',
    sprintf('%02d', $key - 1)
);
