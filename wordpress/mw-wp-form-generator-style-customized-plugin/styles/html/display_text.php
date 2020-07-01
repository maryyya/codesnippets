<?php

/**
 * This is the template html
 * テキストのみ
 *
 * @param int     $key          index
 * @param array   $item         field data
 * @param string  $require      html require
 * @param string  $description  html desc
 * @param string  $notes        html notes
 * @return string $content      html combined
 */
$content .= sprintf(
    '<dl class="c_semi_form_dl_%s ppc_mw_input">
        <dt>%s%s</dt>
        <dd>
            %s
            %s
            %s
        </dd>
    </dl>',
    sprintf('%02d', $key - 1),
    $item['display_name'],
    $require,
    $description,
    nl2br($item['shortcode']),
    $notes
);
