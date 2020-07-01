<?php

/**
 * This is the template html
 * for the h3タグ
 *
 * @param int     $key          index
 * @param array   $item         field data
 * @param string  $require      html require
 * @param string  $description  html desc
 * @param string  $notes        html notes
 * @return string $content      html combined
 */
$class = (int) $item['position'] > 1 ? 'c_mt_50' : '';
$content .= sprintf('<h3 class="%s">%s</h3>', $class, $item['shortcode']);
