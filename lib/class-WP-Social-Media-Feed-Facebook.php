<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if (!class_exists('WP_Social_Media_Feed_Facebook')) {

	class WP_Social_Media_Feed_Facebook extends WP_Social_Media_Feed_Base {

		var $status;

		public function __construct() {
			$this->property_name = 'Facebook';
			$this->settings_prefix = 'facebook';
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

			$content .= $this->read_more_html( $status->url, 'View on Facebook' );


			return $content;

		}


		public function latest_status() {

			$this->status = new WP_Social_Media_Status();
			$this->status->property_name = $this->property_name;
			$this->status->fetch_results = $this->fetch_feed( );
			if ( false !== $this->status->fetch_results) {

				$this->status->fetch_results = json_decode( $this->status->fetch_results );
				if ( !empty( $this->status->fetch_results ) )
					$this->status = $this->parse_fetch_results( $this->status );

			}

			return $this->status;

		}


		function fetch_feed( ) {

			$fetch = $this->get_fetch_transient( );
			if ( false === $fetch) {
				$app_id = trim( $this->get_setting( $this->settings_prefix . '-app-id' ) );
				$app_secret = trim( $this->get_setting( $this->settings_prefix . '-app-secret' ) );
				$page_id = trim( $this->get_setting( $this->settings_prefix . '-page-id' ) );
				$fetch = wp_remote_get( 'https://graph.facebook.com/' . $page_id . '/feed?access_token=' . $app_id . '|' . $app_secret );
				if ( !is_wp_error( $fetch )) {
					$this->set_fetch_transient( $fetch['body'] );
					$fetch = $fetch['body'];
				}
			}
			else
				$this->status->transient_hit = true;

			return $fetch;
		}


		function parse_fetch_results( $status ) {

			$page_id = trim( $this->get_setting( $this->settings_prefix . '-page-id' ) );
			$data = $status->fetch_results;
			$entry = null;

			if ( !empty( $data->data )  && is_array( $data->data ) && count( $data->data ) > 0 ) {

				// find the first post
				for ($i=0; $i < count($data->data); $i++) {
					if ( !empty( $data->data[$i]->from ) && !empty( $data->data[$i]->status_type ) && !empty( $data->data[$i]->from->id ) && $page_id === $data->data[$i]->from->id ) {

						$entry = $data->data[$i];
						break;
					}
				}


				if (!empty( $entry )) {

					$status->fetch_results = $entry;

					$fields = array(
						'id' => 'id',
						'created_time' => 'published',
						'title' => 'title',
						'message' => 'content',
						'link' => 'url',
					);

					foreach ($fields as $json_field => $status_field) {
						if ( !empty( $entry->$json_field ))
							$status->$status_field = $entry->$json_field;
					}

					$status->published = strtotime($status->published);

					if ( !empty( $entry->picture )) {
						$status->thumbnails[] = array( 'url' => $entry->picture );
					}

				}

			}


			return $status;
		}



	}

}