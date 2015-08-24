<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if ( ! class_exists( 'WP_Social_Media_Feed_Flickr' ) ) {

	class WP_Social_Media_Feed_Flickr extends WP_Social_Media_Feed_Base {

		var $status;

		public function __construct() {
			$this->property_name = 'Flickr';
			$this->settings_prefix = 'flickr';
		}


		public function requires_authentication() {
			return true;
		}


		public function status_message_html() {
			$status = $this->status;
			$html = $status->title;

			$images = '';

			if ( !empty( $status->thumbnails ))
				$images .= $this->image_tag( $status->thumbnails[0]['url'] ) . ' ';

			$html = $images . $this->published_time_html( $status->published ) . $this->replace_urls_with_anchors( $html );


			if ( !empty( $status->content ))
				$html .= ' - ' . $status->content;

			$html .= $this->read_more_html( $status->url, 'View on Flickr' );


			return $html;
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
				$api_key = trim( $this->get_setting( $this->settings_prefix . '-api-key' ) );
				$fetch = wp_remote_get( 'https://api.flickr.com/services/rest?format=json&nojsoncallback=1&method=flickr.people.getPublicPhotos&api_key=' . $api_key . '&user_id=' . $user_id . '&per_page=1&extras=description,date_upload,path_alias,url_t,owner_name,icon_server,o_dims' );
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

			if ( !empty( $data->photos )  && is_array( $data->photos->photo ) && count( $data->photos->photo ) > 0 ) {
				$entry = $data->photos->photo[0];

				$fields = array(
					'id' => 'id',
					'dateupload' => 'published',
					'title' => 'title',
				);

				foreach ($fields as $json_field => $status_field) {
					if ( !empty( $entry->$json_field ))
						$status->$status_field = $entry->$json_field;
				}

				$property_name = '_content';
				if ( !empty( $entry->description ) && !empty( $entry->description->$property_name ))
					$status->content = $entry->description->$property_name;

				if ( !empty( $entry->url_t ) && !empty( $entry->height_t ) && !empty( $entry->width_t )) {
					$status->thumbnails[] = array(
						'url' => $entry->url_t,
						'height' => $entry->height_t,
						'width' => $entry->width_t,
					);
				}

				if ( !empty( $entry->pathalias ) )
					$status->url = 'https://www.flickr.com/photos/' . $entry->pathalias . '/' . $status->id;


			}


			return $status;
		}



	}

}