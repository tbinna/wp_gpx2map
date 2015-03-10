<?php
/**
 * Plugin Name: Adventure Tracks GPX2Map
 * Plugin URI: http://www.adventure-tracks.com
 * Description: Visualze gpx files as maps on posts.
 * Version: 0.3
 * Author: Tobi Binna
 * Author URI: http://www.adventure-tracks.com
 * License: GPL2
 */
require_once 'Gpx2Map.class.php';

$gpx2map = new Gpx2Map(plugin_dir_url(__FILE__));

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

?>