<?php

/**
 * This is the template html
 * for the 住所
 *
 * @param int     $key          index
 * @param array   $item         field data
 * @param string  $require      html require
 * @param string  $description  html desc
 * @param string  $notes        html notes
 * @return string $content      html combined
 */
$pref_list = ',' . implode(',', $this->pref_list());
$field_name = isset($item['field_name']) && !empty($item['field_name']) ? $item['field_name'] : 'place';
$content .= sprintf(
    '<dl class="c_semi_form_dl_01 ppc_mw_input ppc_mw_place">
    <dt>住所<span class="c_required">必須</span></dt>
        <dd>
            <div class="c_form_group">
                <label for="">郵便番号</label>
                [mwform_text placeholder="000-0000" name="ppc_zip_' . $field_name . '" show_error="false" class="ppc_width250 ppc_zip_class"]
                <span class="c_addauto" id="ppc_auto_zip">住所を自動入力</span>
                <span class="ppc_place_loader ppc_display_none"><img src="' . get_template_directory_uri() . '/common/images/ppc_loader.gif' . '"></span>
                <!-- <p class="c_note">※半角英数字 例。000-0000 OR 0000000</p> -->
                [mwform_error keys="ppc_zip_' . $field_name . '"]
            </div>
            <div class="c_form_group">
                <label for="">都道府県</label>
                <div class="select-wrap select-inverse">
                    [mwform_select class="ppc_prefecture_class" name="ppc_pref_' . $field_name . '" id="ppc_pref_' . $field_name . '" show_error="false" children=":選択してください' . $pref_list . '" post_raw="true"]
                </div>
                [mwform_error keys="ppc_pref_' . $field_name . '"]
            </div>
            <div class="c_form_group">
                <label for="">市町村名</label>
                [mwform_text name="ppc_municipal_' . $field_name . '" show_error="false" class="ppc_municipal"]
                [mwform_error keys="ppc_municipal_' . $field_name . '"]
            </div>
            <div class="c_form_group">
                <label for="">番地・建物名</label>
                [mwform_text name="ppc_building_' . $field_name . '" show_error="false" class="ppc_building"]
                [mwform_error keys="ppc_building_' . $field_name . '"]
            </div>
        </dd>
    </dl>
    ',
    sprintf('%02d', $key - 1)
);
