<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if ( ! class_exists( 'WP_Social_Media_Feed_Base' ) ) {

	abstract class WP_Social_Media_Feed_Base {

		public $property_name;
		public $settings_prefix;

		abstract public function requires_authentication();
		//abstract public function Authenticate();
		abstract public function latest_status();
		abstract public function status_message_html();


		function get_urls( $content ) {

			preg_match_all("/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", $content, $output_array);
			if ( !empty( $output_array ) && is_array( $output_array ) && count( $output_array ) > 0 ) {
				$urls = $output_array[0];
				if ( !empty( $urls ) )
					return $urls;
			}

			return false;
		}


		function published_time_html( $published_timestamp ) {
			return '<div class="published">' . human_time_diff( $published_timestamp, current_time('timestamp') ) . ' ago</div>';
		}


		function replace_urls_with_anchors( $content ) {
			$urls = $this->get_urls( $content );
			if ( !empty( $urls ) && is_array( $urls ) ) {
				foreach ($urls as $url)
					$content = str_replace( $url, '<a href="' . $url . '" target="_blank">' . $url . '</a>', $content );
			}
			return $content;
		}


		public function image_tag( $image_url, $atts = null ) {
			$html = '<img width="100" class="thumbnail" src="' . $image_url . '"';

			$html .= ' />';
			return $html;
		}


		public function read_more_html( $url, $read_more = 'Read more...' ) {
			return '<div class="read-more"><a href="' . $url . '" target="_blank">' . $read_more . '</a></div>';
		}


		function get_fetch_transient() {
			return get_transient( $this->get_fetch_transient_name() );
		}

		function set_fetch_transient( $value ) {
			return set_transient( $this->get_fetch_transient_name(), $value, MINUTE_IN_SECONDS * 15 );
		}

		function get_fetch_transient_name() {
			return 'wp-smhub-fetch-' . $this->property_name;
		}

		function get_setting( $key, $default = '' ) {
			return apply_filters( 'wp-smhub-setting-get', $default, 'wp-smhub-settings-properties', $key );
		}

	}

}
