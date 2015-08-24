<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if ( ! class_exists( 'WP_Social_Media_Feed_Twitter' ) ) {

	class WP_Social_Media_Feed_Twitter extends WP_Social_Media_Feed_Base {

		var $status;

		public function __construct() {
			$this->property_name = 'Twitter';
			$this->settings_prefix = 'twitter';
		}


		public function requires_authentication() {
			return true;
		}


		public function status_message_html() {
			$status = $this->status;
			$content = $status->content;

			$images = '';

			if ( !empty( $status->thumbnails ))
				$images .= $this->image_tag( $status->thumbnails[0]['url'] ) . ' ';

			$content = $images . $this->published_time_html( $status->published ) . $this->replace_urls_with_anchors( $content );

			$content .= $this->read_more_html( $status->url, 'View on Twitter' );

			return $content;
		}


		public function latest_status() {

			$this->status = new WP_Social_Media_Status();
			$this->status->property_name = $this->property_name;
			$this->status->fetch_results = $this->fetch_feed();
			if ( false !== $this->status->fetch_results) {

				$this->status->fetch_results = json_decode( $this->status->fetch_results );
				if ( !empty( $this->status->fetch_results ) )
					$this->status = $this->parse_fetch_results( $this->status );

			}

			return $this->status;

		}


		function bearer_token() {

			$transient_key = 'wp-smhub-twitter-bt';
			$bearer = get_transient( $transient_key );

			if ( false === $bearer) {
				$bearer = $this->authenticate();
				if ( !empty( $bearer ) && !empty( $bearer->access_token )) {
					set_transient( $transient_key, $bearer->access_token, DAY_IN_SECONDS * 30 );
					$bearer = $bearer->access_token;
				}
			}

			return $bearer;

		}


		function authenticate() {

			$consumer_key = trim( $this->get_setting( $this->settings_prefix . '-consumer-key' ) );
			$consumer_secret = trim( $this->get_setting( $this->settings_prefix . '-consumer-secret' ) );

			$basic_auth = base64_encode($consumer_key . ':' . $consumer_secret);


			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, 'https://api.twitter.com/oauth2/token' );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 30);
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Basic ' . $basic_auth,
				'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
			) );
			curl_setopt( $ch, CURLOPT_POST, true );
			$fields = http_build_query( array('grant_type' => 'client_credentials') );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields );

			$result = curl_exec( $ch );

			if ( curl_errno( $ch ) > 0 )
				$result = false;
			else
				$result = json_decode($result);

			return $result;

		}


		function fetch_feed() {

			$fetch = $this->get_fetch_transient( );
			if ( false === $fetch) {

				$bearer = $this->bearer_token();
				if ( false === $bearer )
					return false;

				$screen_name = trim( $this->get_setting( $this->settings_prefix . '-screen-name' ) );
				$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=' . $screen_name . '&count=1&exclude_replies=true&include_rts=false';


				$ch = curl_init();

				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_TIMEOUT, 30);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
					'Authorization: Bearer ' . $bearer
				) );

				//curl_setopt( $ch, CURLOPT_VERBOSE, true);

				//$verbose = fopen('php://temp', 'rw+');
				//curl_setopt($ch, CURLOPT_STDERR, $verbose);


				$result = curl_exec( $ch );

				if ( curl_errno( $ch ) > 0 )
					$fetch = false;
				else {
					$fetch = $result;
					$this->set_fetch_transient( $fetch );
				}

				curl_close( $ch );

			}
			else
				$this->status->transient_hit = true;

			return $fetch;
		}


		function parse_fetch_results( $status ) {

			$data = $status->fetch_results;

			if ( !empty( $data )  && is_array( $data ) && count( $data) > 0 ) {

				$entry = $data[0];

				$fields = array(
					'id_str' => 'id',
					'created_at' => 'published',
					'text' => 'content',
				);


				foreach ($fields as $json_field => $status_field) {
					if ( !empty( $entry->$json_field ))
						$status->$status_field = $entry->$json_field;
				}


				$screen_name = trim( $this->get_setting( $this->settings_prefix . '-screen-name' ) );
				if ( !empty( $status->id ))
					$status->url = 'https://twitter.com/' . $screen_name . '/status/' . $status->id;

				if ( !empty( $status->published ))
					$status->published = strtotime($status->published);

				if ( !empty( $entry->entities ) && !empty( $entry->entities->media ) && is_array( $entry->entities->media )  && count( $entry->entities->media ) > 0 ) {
					$media = $entry->entities->media[0];
					if ( !empty( $media->media_url_https ) && !empty( $media->sizes ) && !empty( $media->sizes->thumb ) )
						$status->thumbnails[] = array(
							'url' => $media->media_url_https . ':thumb',
							'size' => 'thumb',
							'width' => $media->sizes->thumb->w,
							'height' => $media->sizes->thumb->h,
						);
				}

			}


			return $status;
		}



	}

}