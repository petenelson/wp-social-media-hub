<?php
/*
Plugin Name: Social Media Hub
Description: Feeds from your social media properties
Author: Pete Nelson
Version: 1.0
*/

if (!defined( 'ABSPATH' )) exit('restricted access');

require_once plugin_dir_path( __FILE__ ) . 'lib/class-WP-Social-Media-Hub.php';

if ( class_exists( 'WP_Social_Media_Hub' ) ) {
	$hub = new WP_Social_Media_Hub();
	add_action( 'plugins_loaded', array( $hub, 'plugins_loaded' ) );
}


require_once plugin_dir_path( __FILE__ ) . 'lib/class-WP-Social-Media-Hub-Settings.php';

if ( class_exists( 'WP_Social_Media_Hub_Settings' ) ) {
	$settings = new WP_Social_Media_Hub_Settings();
	add_action( 'plugins_loaded', array( $settings, 'plugins_loaded' ) );
}
