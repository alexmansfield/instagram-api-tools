<?php
/*
Plugin Name: Instagram API Tools
Plugin URI: https://alexmansfield.com
Description: Handles API connection to a user's instagram feed.
Version: 0.1.0
Author: Alex Mansfield
Author URI: https://alexmansfield.com/
License: GPLv2 or later
Text Domain: instagram_api_tools_textdomain

Useful links:
http://usefulangle.com/post/25/instagram-login-api-with-php-curl

*/

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
	  if ( is_array( $log ) || is_object( $log ) ) {
		 error_log( print_r( $log, true ) );
	  } else {
		 error_log( $log );
	  }
   }
}

require_once( 'inc/class-ig-data.php' );
require_once( 'inc/class-ig-auth.php' );
require_once( 'inc/class-tools-settings.php' );

// Create the Settings > IG Tools.
new IG_API_Tools_Settings;

