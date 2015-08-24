<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if (!class_exists('WP_Social_Media_Feed_Instagram')) {

	class WP_Social_Media_Feed_Instagram extends WP_Social_Media_Feed_Base {

		var $status;

		public function __construct() {
			$this->property_name = 'Instagram';
			$this->settings_prefix = 'instagram';
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

			$content .= $this->read_more_html( $status->url, 'View on Instagram' );

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


		function fetch_feed() {

			$fetch = $this->get_fetch_transient( );

			if ( false === $fetch) {
				$user_id = trim( $this->get_setting( $this->settings_prefix . '-user-id' ) );
				$client_id = trim( $this->get_setting( $this->settings_prefix . '-client-id' ) );
				$fetch = wp_remote_get( 'https://api.instagram.com/v1/users/' . $user_id .'/media/recent/?client_id=' . $client_id );
				var_dump($fetch);
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

			$data = $status->fetch_results;

			if ( !empty( $data->data )  && is_array( $data->data ) && count( $data->data ) > 0 ) {
				$entry = $data->data[0];

				$fields = array(
					'created_time' => 'published',
					'id' => 'id',
					'link' => 'url',
				);

				foreach ($fields as $json_field => $status_field) {
					if ( !empty( $entry->$json_field ))
						$status->$status_field = $entry->$json_field;
				}


				$status->published = intval($status->published);


				if ( !empty( $entry->caption ) && !empty( $entry->caption->text ) )
					$status->content = $entry->caption->text;


				if ( !empty( $entry->images ) && !empty( $entry->images->thumbnail ) )
					$status->thumbnails[] = array(
						'url' => $entry->images->thumbnail->url,
						'width' => $entry->images->thumbnail->width,
						'height' => $entry->images->thumbnail->height,
					);

			}


			return $status;
		}

	}

}