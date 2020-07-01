<?php

/**
 * Plugin Name: MW WP Form Generator Style Doginsoken
 * Description: Style for MW WP Form Generator. This is for doginsoken. Originally created by PPC group. The css file used is in themes/{theme_name}/common/css/*
 * Version: 1.0
 * Author: PPC
 * Author URI: https://www.pripress.co.jp/products/web/02.html
 * Text Domain: mw-wp-form-generator-style-doginsoken
 * Created : January 10, 2020
 */
class MW_WP_Form_Generator_Style_Doginsoken
{
	/**
	 * NAME
	 */
	const NAME = 'mw-wp-form-generator-style-doginsoken';

	/**
	 * $styles
	 */
	protected $styles = array();

	/**
	 * __construct
	 */
	public function __construct()
	{
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		if (
			is_plugin_active('mw-wp-form/mw-wp-form.php') ||
			is_plugin_active('mw-wp-form-generator/mw-wp-form-generator.php')
		) {
			add_action('plugins_loaded', array($this, 'plugins_loaded'));
		}

		require_once(plugin_dir_path(__FILE__) . 'modules/customize-setting.php');
	}

	/**
	 * plugins_loaded
	 */
	public function plugins_loaded()
	{
		add_filter('mwform_styles', array($this, 'mwform_styles'));
		add_filter('mw-wp-form-generator-templates', array($this, 'templates'));

		// デフォルトのフォームスタイルの定義
		$this->styles = array(
			'doginsoken' => array(
				'css' => '',
				'template' => plugin_dir_path(__FILE__) . 'styles/template.php',
			),
		);

		// add the generating fields js
		add_filter('admin_enqueue_scripts', array($this, 'ppc_include_js'));

		// add customization to all mw wp form
		$customize_setting = new Customize_setting;
	}

	/**
	 * mwform_styles
	 *
	 * @param array $styles
	 * @return array $styles
	 */
	public function mwform_styles($styles)
	{
		global $post;
		foreach ($this->styles as $style_name => $style) {
			$styles[$style_name] = $style['css'];
		}
		return $styles;
	}

	/**
	 * templates
	 *
	 * @param array $templates
	 * @return array $templates
	 */
	public function templates($templates)
	{
		global $post;

		foreach ($this->styles as $style_name => $style) {
			$templates[$style_name] = $style['template'];
		}

		return $templates;
	}

	/**
	 * Include the generating fields js
	 */
	public function ppc_include_js()
	{
		global $pagenow;
		$post = isset($_GET['post']) ? $_GET['post'] : '';
		$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';

		// mw wp form
		// if (($pagenow === 'post-new.php' || $pagenow === 'post.php') && 'mw-wp-form' === get_post_type($post)) {
		if (($pagenow === 'post-new.php' && $post_type === 'mw-wp-form') || ($pagenow === 'post.php' && 'mw-wp-form' === get_post_type($post))) {
			wp_register_script('ppc-generate-fields-js', plugin_dir_url(__FILE__) . 'styles/generate-fields.js', array('jquery'), 1.0, true);
			wp_enqueue_script('ppc-generate-fields-js');

			if (!empty($_GET['post'])) {
				$post_id = htmlspecialchars($_GET['post']);
				$meta_data = get_post_meta($post_id, 'ppc_application_custom_fields');
				if (empty($meta_data[0])) {
					return;
				}
				echo "<input type='hidden' name='ppc_application_custom_fields' value='" . json_encode($meta_data[0], JSON_UNESCAPED_UNICODE) . "'>";
			}
			echo "<input type='hidden' name='ppc_theme_dir' value='" . get_stylesheet_directory_uri() . "'>";
		}
	}
}
$MW_WP_Form_Generator_Style_Doginsoken = new MW_WP_Form_Generator_Style_Doginsoken();
