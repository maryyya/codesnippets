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
    '<dl class="c_form_dl_01 ppc_mw_input ppc_mw_name">
    <dt>氏名' . $span . '</dt>
    <dd class="ppc_form_name">
        <div class="c_formlabel01 c_form_group">
            <label for="">姓　</label>
            [mwform_text name="ppc_sei_participant_name_' . $field_name . '" show_error="false"]
            <label for="">名　</label>
            [mwform_text name="ppc_mei_participant_name_' . $field_name . '" show_error="false"]
        </div>
        <div class="ppc_disp_flex">
            <div class="name_sei_error">[mwform_error keys="ppc_sei_participant_name_' . $field_name . '" class="sei"]</div>
            <div class="name_mei_error">[mwform_error keys="ppc_mei_participant_name_' . $field_name . '" class="mei"]</div>
        </div>
        <div class="c_formlabel01 c_form_group">
            <label for="">セイ</label>
            [mwform_text name="ppc_sei_kana_participant_name_' . $field_name . '" show_error="false"]
            <label for="">メイ</label>
            [mwform_text name="ppc_mei_kana_participant_name_' . $field_name . '" show_error="false"]
        </div>
        <div class="ppc_disp_flex">
            <div class="name_sei_error">[mwform_error keys="ppc_sei_kana_participant_name_' . $field_name . '" class="sei"]</div>
            <div class="name_mei_error">[mwform_error keys="ppc_mei_kana_participant_name_' . $field_name . '" class="mei"]</div>
        </div>
    </dd>
    </dl>
    ',
    sprintf('%02d', $key - 1)
);
