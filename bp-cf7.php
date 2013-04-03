<?php
/*
Plugin Name: Bp-cf7
Version: 0.1-alpha
Description: BuddyPress Group integration for Contact Form 7
Author: Boone B Gorges
Author URI: http://boone.gorg.es
Text Domain: bp-cf7
Domain Path: /languages
*/

function bpcf7_init() {
	// Make sure that CF7 exists
	if ( ! function_exists( 'wpcf7' ) ) {
		return;
	}

	include( dirname(__FILE__) . '/includes/class-bpcf7.php' );
}
add_action( 'bp_include', 'bpcf7_init' );

