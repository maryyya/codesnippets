<?php

/**
 * MW_WP_Form_Generator_Template_Doginsoken
 * Version    : 1.0
 * Author     : PPC
 * Created    : January 10, 2020
 */
class MW_WP_Form_Generator_Template_Doginsoken extends MW_WP_Form_Generator_Template_Base
{
	/**
	 * 都道府県一覧
	 */
	const PREFECTURE_FILE = 'prefecture.json';

	/**
	 * お問い合わせID
	 * The PPC_CONTACT_FORM_ID is in
	 * wp-config.php
	 */
	const CONTACT_POST_ID = PPC_CONTACT_FORM_ID;

	/**
	 * Plugin path
	 * Ex. /<root_path>/doginsoken/manager/wp-content/plugins/mw-wp-form-generator-style-doginsoken/styles/
	 *
	 * @var string
	 */
	private $plugin_path;

	/**
	 * HTML path
	 * Ex. /<root_path>/doginsoken/manager/wp-content/plugins/mw-wp-form-generator-style-doginsoken/styles/html
	 *
	 * @var string
	 */
	private $html_path;

	/**
	 * To create content
	 *
	 * @param string  $content     本文
	 * @param array   $items       フォーム項目
	 * @param array   $other_items エラー要素などの非フォーム項目
	 * @return string $content
	 */
	public function create_content($content, $items, $other_items)
	{
		global $post;
		$this->plugin_path = plugin_dir_path(__FILE__);
		$this->html_path   = $this->plugin_path . '/html';

		// now add the customized fields
		$this->ppc_get_application_custom_fields($post->ID, $items);
		$rules = ppc_get_validation_rules($post->ID);

		// お問い合わせフォーム
		if ($post->ID === self::CONTACT_POST_ID) {
			return $content;
		}

		$content = '<div class="c_forminner">';
		foreach ($items as $key => $item) {
			$require = '';
			if (isset($item['require']) && $item['require'] === true) {
				$require = sprintf(
					'<span class="c_required">必須</span>',
					esc_html__('REQUIRE', 'mw-wp-form-generator-style-doginsoken')
				);
			}

			$description = '';
			if (!empty($item['description'])) {
				$description = sprintf(
					'<div class="doginsoken-description">%s</div>',
					wpautop($item['description'])
				);
			}

			$notes = '';
			if (isset($item['notes'])) {
				$notes = sprintf('<span class="notes" data-display_name="' . $item['display_name'] . '">%s</span>', esc_attr($item['notes']));
			}

			if (!isset($item['name'])) {
				include($this->html_path . '/default.php');
			} else {
				$filenm = str_replace('mwform_ppc_', '', $item['name']) . '.php';
				$fullpth = $this->html_path . '/' . $filenm;
				if (!file_exists($fullpth)) {
					continue;
				}

				include($fullpth);
			}
		}

		$content .= '</div>';
		$content .= implode('', $other_items);
		$content .= '<div class="c_btnwrap">';
		$content .= '<p><input type="checkbox" name="" value="" id="confirmation"><label
				class="c_labelstyle" for="confirmation">上記を確認の上、<a
					href="' . get_template_directory_uri() . '/common/pdf/policy.pdf" target="_blank">個人情報保護方針<img
						src="' . get_template_directory_uri() . '/common/images/iconfinder_doc_pdf_16223.png"
						alt=""></a> に同意します。</label></p>
						<p class="confirm-btn-p">
						[mwform_bconfirm class="submitConfirmBtn c_kochirabtn" value="confirm"]<span>入力確認画面へ</span>[/mwform_bconfirm]
						[mwform_bback class="back c_kochirabtn" value="back"]<span>戻る</span>[/mwform_bback]
						[mwform_bsubmit name="send" class="send c_kochirabtn" value="send"]<span>送信</span>[/mwform_bsubmit]</p>
				</div>';
		return $content;
	}

	/**
	 * To get the custom fields like displaying
	 * the text for application form page.
	 * This is an original.
	 *
	 * @param int   $post_id
	 * @param array $items These are the default value from mw wp form
	 * @return void $items If there's custom fields then add the custom fields according to the position
	 */
	private function ppc_get_application_custom_fields($post_id, &$items)
	{
		$content = [];
		$meta_data = get_post_meta($post_id, 'ppc_application_custom_fields');
		if (empty($meta_data[0])) {
			return $content;
		}

		$custom_fields = $meta_data[0];

		// if empty then do nothing
		if (empty($custom_fields)) {
			return;
		}

		// add the custom fields to $items which are the default or inputted fields
		foreach ($custom_fields as $key => $val) {
			// replace value key with shortcode for preparation in displaying
			if (isset($val['data'])) {
				$val['display_name'] = isset($val['data']['display_name']) ? $val['data']['display_name'] : '';
				$val['field_name']   = isset($val['data']['field_name']) ? $val['data']['field_name'] : '';
				$val['shortcode']    = isset($val['data']['value']) ? $val['data']['value'] : '';
				unset($val['data']);
			}

			// start rearranging the position
			$position = (int) $val['position'];
			array_splice($items, $position, 0, [$val]);
		}
	}

	/**
	 * 都道府県一覧
	 * Used in html/place.php
	 *
	 * @return array
	 */
	private function pref_list()
	{
		$file_contents = file_get_contents($this->plugin_path . '/' . self::PREFECTURE_FILE);
		return json_decode($file_contents, JSON_UNESCAPED_UNICODE)['prefectures'];
	}
}
new MW_WP_Form_Generator_Template_Doginsoken();
