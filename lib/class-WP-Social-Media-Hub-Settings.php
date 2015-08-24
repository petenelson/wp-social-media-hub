<?php
if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists('WP_Social_Media_Hub_Settings' ) ) {

	class WP_Social_Media_Hub_Settings	{


		var $settings_page          = 'wp-smhub-settings';
		var $settings_key_smhub     = 'wp-smhub-settings-properties';
		var $plugin_settings_tabs   = array();
		var $jquery_chosen_enabled  = false;


		public function plugins_loaded() {
			// admin menus
			add_action( 'admin_init', array($this, 'admin_init' ) );
			add_action( 'admin_menu', array($this, 'admin_menu' ) );

			add_filter( 'wp-smhub-setting-is-enabled', array($this, 'setting_is_enabled'), 10, 3 );
			add_filter( 'wp-smhub-setting-get', array($this, 'setting_get'), 10, 3 );

		}


		function admin_init() {
			$this->register_smhub_settings();
		}


		function setting_is_enabled($enabled, $key, $setting) {
			return '1' === $this->setting_get('0', $key, $setting);
		}


		function setting_get($value, $key, $setting) {

			$args = wp_parse_args( get_option($key),
				array(
					$setting => $value,
				)
			);

			return $args[$setting];
		}


		function register_smhub_settings() {
			$key = $this->settings_key_smhub;
			$this->plugin_settings_tabs[$key] = 'Social Media Hub';

			register_setting( $key, $key );

			$section = 'smhub';


			add_settings_section( $section, '', array( $this, 'section_header' ), $key );

			$properties = array(
				'facebook' => 'Facebook',
				'twitter' => 'Twitter',
				'youtube' => 'YouTube',
				'instagram' => 'Instagram',
				'flickr' => 'Flickr',
			);

			foreach ($properties as $property => $name) {
				add_settings_field( $property . '-enabled', $name . ' Enabled', array( $this, 'settings_yes_no' ), $key, $section,
					array('key' => $key, 'name' => $property . '-enabled' ));
			}


			add_settings_field( 'facebook-app-id', 'Facebook: App ID', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'facebook-app-id', 'size' => 40, 'maxlength' => 100));

			add_settings_field( 'facebook-app-secret', 'Facebook: App Secret', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'facebook-app-secret', 'size' => 40, 'maxlength' => 100));

			add_settings_field( 'facebook-page-id', 'Facebook: Page ID', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'facebook-page-id', 'size' => 40, 'maxlength' => 100));

			add_settings_field( 'twitter-consumer-key', 'Twitter: Consumer Key', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'twitter-consumer-key', 'size' => 40, 'maxlength' => 100));

			add_settings_field( 'twitter-consumer-secret', 'Twitter: Consumer Secret', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'twitter-consumer-secret', 'size' => 40, 'maxlength' => 100));

			add_settings_field( 'twitter-screen-name', 'Twitter: Screen Name', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'twitter-screen-name', 'size' => 40, 'maxlength' => 100));

			add_settings_field( 'instagram-client-id', 'Instagram: Client ID', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'instagram-client-id', 'size' => 40, 'maxlength' => 100));

			add_settings_field( 'instagram-user-id', 'Instagram: User ID', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'instagram-user-id', 'size' => 40, 'maxlength' => 100));

			add_settings_field( 'youtube-channel-name', 'YouTube: Channel Name', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'youtube-channel-name', 'size' => 40, 'maxlength' => 100));

			add_settings_field( 'flickr-api-key', 'Flickr: API Key', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'flickr-api-key', 'size' => 40, 'maxlength' => 100));

			add_settings_field( 'flickr-user-id', 'Flickr: User ID', array( $this, 'settings_input' ), $key, $section,
				array('key' => $key, 'name' => 'flickr-user-id', 'size' => 40, 'maxlength' => 100));

			// add_settings_field( 'setting-key', 'description', array( $this, 'settings_input' ), $key, $section,
			// 	array('key' => $key, 'name' => 'setting-key', 'size' => 80, 'maxlength' => 250));

		}


		function admin_menu() {
			add_options_page( 'WP Social Media Hub Settings', 'WP Social Media Hub Settings', 'manage_options', $this->settings_page, array($this, 'options_page' ), 30);
		}


		function options_page() {
			wp_enqueue_script( 'jquery' );
			$tab = filter_var( isset( $_GET['tab'] ) ? $_GET['tab'] : $this->settings_key_smhub, FILTER_SANITIZE_STRING );
			?>
			<div class="wrap">
				<?php $this->plugin_options_tabs(); ?>
				<form method="post" action="options.php" class="options-form">
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php do_action('wp-smhub-settings-post-sections_' . $tab) ?>
					<?php submit_button('Save Settings', 'primary', 'submit', true); ?>
				</form>

				<form method="post" action="?page=<?php echo $this->settings_page . '&amp;tab=' . $tab ?>" class="post-save-options-form">
					<?php wp_nonce_field( 'wp-smhub-settings-post-save-buttons' ); ?>
					<input type="hidden" name="action" value="wp-smhub-settings-post-save-button-click" />
					<?php do_action('wp-smhub-settings-post-save-buttons_' . $tab); ?>
				</form>

				<style>
					.post-save-options-form .button {
						margin-right: 0.5em;
					}
				</style>

			</div>


			<?php

			if ( filter_input( INPUT_GET, 'settings-updated') === 'true' ) {
				do_action( 'wp-social-media-hub-flush-status-transients' );
				do_action( 'wp-social-media-hub-refresh-all-feeds' );
			}

		}


		function plugin_options_tabs() {
			$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->settings_key_product_search;
			echo '<h2>WP Social Media Hub Settings</h2><h2 class="nav-tab-wrapper">';
			foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->settings_page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
			}
			echo '</h2>';
		}


		function section_header($args) {
			//echo $args['title'];
		}


		function settings_dropdown($args) {

			// jQuery chosen options are here http://harvesthq.github.io/chosen/options.html
			// sample code for pages and categories

			// $args = array(
			// 	'key' => $key,
			// 	'name' => 'some-test-page',
			// 	'multiple' => false,
			// 	'wp_dropdown_callback' => 'wp_dropdown_pages',
			// 	'wp_dropdown_callback_args' => array(
			// 	),
			// 	'jquery_chosen_args' => array(
			// 		'placeholder_text_multiple' => 'Select Pages',
			// 	),
			// );

			// add_settings_field( $args['name'], 'Some Test Page', array( $this, 'settings_dropdown' ), $key, $section, $args);

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'multiple' => false,
					'options_list' => false,
					'wp_dropdown_callback' => '',
					'wp_dropdown_callback_args' => array(),
				)
			);

			$name = $args['name'];
			$key = $args['key'];
			$multiple = $args['multiple'];
			$input_name = "{$key}[{$name}]";

			$option = get_option($key);

			if ($multiple)
				$value = isset($option[$name]) ? $option[$name] : array();
			else
				$value = isset($option[$name]) ? $option[$name] : '';


			if (!empty($args['wp_dropdown_callback'])) {

				$args['wp_dropdown_callback_args']['name'] = $input_name;

				if ($multiple)
					$args['wp_dropdown_callback_args']['name'] .= '[]';

				$args['wp_dropdown_callback_args']['selected'] = $value;
				$args['wp_dropdown_callback_args']['echo'] = 0;

				$output = call_user_func($args['wp_dropdown_callback'], $args['wp_dropdown_callback_args']);
				if ($multiple)
					$output = str_replace('<select ', '<select multiple="multiple" ', $output);
				$output = str_replace('<select ', '<select class="wp-smhub-chosen-select ' . esc_attr($name) . '" ', $output);

				echo $output;

				if ($multiple && is_array($value)) {
					?>
					<script type="text/javascript">
						jQuery(document).ready(function() {
							<?php foreach ($value as $selected) { ?>
								jQuery('.<?php echo esc_attr($name) ?> option[value="<?php echo esc_attr($selected) ?>"]').attr('selected', 'selected');
							<?php } ?>
						});
					</script>
					<?php
				}


			}
			else if (false !== $args['options_list'] && is_array($args['options_list'])) {

				$options_html = '<select class="wp-smhub-chosen-select ' . esc_attr($name) . '"';
				if ($multiple)
					$options_html .= ' multiple="multiple"';
				$options_html .= ' name="' . $input_name . '">';

				if (!is_array($value))
					$value = array($value);


				foreach ($args['options_list'] as $key => $text) {

					$options_html  .= '<option value="' . esc_attr( $key ) . '"';

					if (in_array($key, $value))
						$options_html .= ' selected="selected"';

					$options_html .= '>' . esc_attr( $text ) . '</option>';

				}


				$options_html .'</select>';
				echo $options_html;

			}



			if ($this->jquery_chosen_enabled) {
			?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('.<?php echo esc_attr($name); ?>').chosen(<?php if (isset($args['jquery_chosen_args'])) echo json_encode($args['jquery_chosen_args']); ?>);
					});
				</script>
			<?php
			}

		}


		function settings_input($args) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'maxlength' => 50,
					'size' => 30,
					'after' => '',
				)
			);


			$name = $args['name'];
			$key = $args['key'];
			$size = $args['size'];
			$maxlength = $args['maxlength'];

			$option = get_option($key);
			$value = isset($option[$name]) ? esc_attr($option[$name]) : '';

			echo "<input id='{$name}' name='{$key}[{$name}]'  type='text' value='" . $value . "' size='{$size}' maxlength='{$maxlength}' />";
			if (!empty($args['after']))
				echo '<div>' . __($args['after'], 'wp-smhub') . '</div>';
		}


		function settings_yes_no($args) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'after' => '',
				)
			);

			$name = $args['name'];
			$key = $args['key'];

			$option = get_option($key);
			$value = isset($option[$name]) ? esc_attr($option[$name]) : '';

			if (empty($value))
				$value = '0';

			echo "<label><input id='{$name}_1' name='{$key}[{$name}]'  type='radio' value='1' " . ('1' === $value ? " checked=\"checked\"" : "") . "/>Yes</label> ";
			echo "<label><input id='{$name}_0' name='{$key}[{$name}]'  type='radio' value='0' " . ('0' === $value ? " checked=\"checked\"" : "") . "/>No</label> ";

			if ( ! empty( $args['after'] ) ) {
				echo '<div>' . __($args['after'], 'wp-smhub') . '</div>';
			}


		}

	}
}