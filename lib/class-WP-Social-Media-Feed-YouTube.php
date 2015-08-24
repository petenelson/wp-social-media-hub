<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if (!class_exists('WP_Social_Media_Feed_YouTube')) {

	class WP_Social_Media_Feed_YouTube extends WP_Social_Media_Feed_Base {

		var $status;

		public function __construct() {
			$this->property_name = 'YouTube';
			$this->settings_prefix = 'youtube';
		}


		public function requires_authentication() {
			return false;
		}


		public function status_message_html() {
			$status = $this->status;
			$content = $status->content;

			$images = '';

			if ( !empty( $status->thumbnails ))
				$images .= $this->image_tag( $status->thumbnails[0]['url'] ) . ' ';

			$content = $images . $this->published_time_html( $status->published ) . $this->replace_urls_with_anchors( $content );

			$content .= $this->read_more_html( $status->url, 'View on YouTube' );

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


		function parse_fetch_results( $status ) {

			$data = $status->fetch_results;

			if ( !empty( $data->feed ) && !empty( $data->feed->entry ) && is_array( $data->feed->entry ) && count( $data->feed->entry ) > 0 ) {
				$entry = $data->feed->entry[0];

				// find the URL
				if ( is_array( $entry->link ) ) {
					for ($i=0; $i < count( $entry->link ); $i++) {
						if ( !empty( $entry->link[$i]->rel ) && 'alternate' === $entry->link[$i]->rel &&!empty( $entry->link[$i]->href ) )
							$status->url = $entry->link[$i]->href;
					}
				}

				$property_name = '$t';
				if ( !empty( $entry->title ) && !empty( $entry->title->$property_name ) )
					$status->title = $entry->title->$property_name;

				if ( !empty( $entry->content ) && !empty( $entry->content->$property_name ) )
					$status->content = $entry->content->$property_name;

				if ( !empty( $entry->published ) && !empty( $entry->published->$property_name ) ) {
					$status->published = $entry->published->$property_name;
					$status->published = strtotime($status->published);
				}

				if ( !empty( $entry->id ) && !empty( $entry->id->$property_name ) )
					$status->id = $entry->id->$property_name;

				// find the thumbnails
				$property_name = 'media$group';
				if ( !empty( $entry->$property_name ) ) {
					$media_group = $entry->$property_name;

					$property_name = 'media$thumbnail';
					if ( !empty( $media_group->$property_name ) && is_array( $media_group->$property_name ) ) {
						foreach ($media_group->$property_name as $thumbnail) {
							$status->thumbnails[] = array(
								'url' => $thumbnail->url,
								'width' => $thumbnail->width,
								'height' => $thumbnail->height,
							);
						}
					}
				}

			}

			return $status;

		}


		function fetch_feed() {

			$fetch = $this->get_fetch_transient( );
			if ( false === $fetch) {

				$channel_name = trim( $this->get_setting( $this->settings_prefix . '-channel-name' ) );

				$fetch = wp_remote_get( 'http://gdata.youtube.com/feeds/api/users/' . $channel_name . '/uploads?max-results=1&alt=json&orderby=published' );
				if ( !is_wp_error( $fetch )) {
					$this->set_fetch_transient( $fetch['body'] );
					$fetch = $fetch['body'];
				}
			}
			else
				$this->status->transient_hit = true;

			return $fetch;

		}

	}

}