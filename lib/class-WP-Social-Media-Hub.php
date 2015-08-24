<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if ( !class_exists( 'WP_Social_Media_Hub' ) ) {

	class WP_Social_Media_Hub {

		static $version = '2015-08-24-01';
		static $plugin_name = 'wp-social-media-hub';

		var $registered_properties = array();


		public function plugins_loaded() {

			$this->registered_properties = array();
			$this->load_feeds();

			add_action( 'init', array( $this, 'populate_feed_details' ) );

			add_action( 'wp_ajax_' . self::$plugin_name . '-latest-status', array($this, 'handle_ajax') );
			//add_action( 'wp_ajax_nopriv_' . self::$plugin_name . '-latest-status', array($this, 'handle_ajax') );

			//add_action( 'wp_ajax_' . self::$plugin_name . '-refresh-all-active-properties', array($this, 'handle_ajax_refresh_all_active_properties') );
			//add_action( 'wp_ajax_nopriv_' . self::$plugin_name . '-refresh-all-active-properties', array($this, 'handle_ajax_refresh_all_active_properties') );

			//add_action( 'wp_ajax_' . self::$plugin_name . '-flush-status-transients', array($this, 'flush_status_transients') );
			//add_action( 'wp_ajax_nopriv_' . self::$plugin_name . '-flush-status-transients', array($this, 'flush_status_transients') );

			//add_action( 'wp_ajax_' . self::$plugin_name . '-get-registered-properties', array($this, 'handle_ajax_rp') );
			//add_action( 'wp_ajax_nopriv_' . self::$plugin_name . '-get-registered-properties', array($this, 'handle_ajax_rp') );

			add_shortcode( self::$plugin_name, array( $this, 'shortcode' ) );

			add_action( self::$plugin_name . '-flush-status-transients', array( $this, 'flush_status_transients' ) );
			add_action( self::$plugin_name . '-refresh-all-feeds', array( $this, 'refresh_all_active_feeds' ) );

		}


		function shortcode( $args ) {

			$args = wp_parse_args( $args, array() );

			ob_start();
			?>
				<div class="wp-social-media-hub">
					<?php foreach( $this->registered_properties as $rp ) {
						if ( $rp->enabled ) {
							echo $this->build_property_latest_status_html( $rp );
						}
					} ?>

				</div><!-- .wp-social-media-hub -->

			<?php
			$content = ob_get_clean();

			return do_shortcode( $content );

		}


		function build_property_latest_status_html( $property ) {

			$status = apply_filters( 'wp-social-media-hub-latest-status-' . $property->settings_prefix, false );
			if ( false === $status) {
				return '';
			}

			ob_start();
			?>
				<div class="wp-social-media-hub-property wp-social-media-hub-property-<?php echo $property->settings_prefix; ?>">
					<div class="icon">
						<img src="<?php echo plugin_dir_url( __FILE__ ) ?>images/<?php echo $property->settings_prefix ?>.png" alt="<?php echo esc_attr( $property->property_name ); ?>" />
					</div>
					<div class="status-update">
						<?php
							$status_message_html = apply_filters( self::$plugin_name . '-status-message-html-' . $property->settings_prefix, '' );
							echo $status_message_html;
						?>
					</div>
				</div>

			<?php
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}


		function flush_status_transients() {

			foreach ( $this->registered_properties as $rp ) {
				delete_transient( 'wp-smhub-fetch-' . $rp->settings_prefix );
			}

		}


		function refresh_all_active_feeds() {

			$results = array();
			foreach ( $this->registered_properties as $rp ) {
				if ( $rp->enabled ) {
					$results[] = apply_filters( 'wp-social-media-hub-latest-status-' . $rp->settings_prefix, false );
				}
			}

			return $results;
		}



		function load_feeds() {

			$requires = array(
				'Status',
				'Feed-Base',
				'Feed-Facebook',
				'Feed-Twitter',
				'Feed-YouTube',
				'Feed-Instagram',
				'Feed-Flickr',
			);

			$wp_sm_hub = null;

			foreach ($requires as $require) {
				require_once 'class-WP-Social-Media-' . $require . '.php';

				$class_name = 'WP_Social_Media_' . str_replace( '-', '_', $require );

				if ( class_exists( $class_name ) &&  'Feed-Base' !== $require ) {

					$wp_smhub_class = new $class_name;

					if ( method_exists( $wp_smhub_class, 'init' ) ) {
						add_action( 'init', array( $wp_smhub_class, 'init' ) );
					}

					if ( is_subclass_of( $wp_smhub_class, 'WP_Social_Media_Feed_Base' ) ) {

						// create a filter to hook into the class' latest_status method
						add_filter( 'wp-social-media-hub-latest-status-' . $wp_smhub_class->settings_prefix, array( $wp_smhub_class, 'latest_status' ) );

						// filter to generate html for the shortcode
						add_filter( self::$plugin_name . '-status-message-html-' . $wp_smhub_class->settings_prefix , array( $wp_smhub_class, 'status_message_html' ) );


						// register this property
						$this->registered_properties[] = $wp_smhub_class;


					}

				}
			}

		}


		function populate_feed_details() {
			for ($i=0; $i < count( $this->registered_properties ); $i++) {
				$this->registered_properties[$i]->enabled = apply_filters( 'wp-smhub-setting-is-enabled', false, 'wp-smhub-settings-properties', $this->registered_properties[$i]->settings_prefix . '-enabled' );
				$this->registered_properties[$i]->status_url = admin_url( 'admin-ajax.php?action=wp-social-media-hub-latest-status&property=' . urlencode( $this->registered_properties[$i]->settings_prefix ) );
			}
		}


		function handle_ajax() {
			// function for handling AJAX calls if-necessary
			$results = apply_filters( 'wp-social-media-hub-latest-status-' . $_REQUEST['property'], false );
			wp_send_json( $results );
		}


		function handle_ajax_rp() {
			// function for handling AJAX calls if-necessary
			wp_send_json( $this->registered_properties );
		}

	}

}
