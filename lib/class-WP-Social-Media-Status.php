<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if (!class_exists('WP_Social_Media_Status')) {

	class WP_Social_Media_Status {

		public $property_name;
		public $id;
		public $title;
		public $content;
		public $url;
		public $thumbnails;
		public $published;
		public $transient_hit;
		public $fetch_results;

		public function __construct() {
			$this->thumbnails = array();
			$this->transient_hit = false;
		}

	}

}