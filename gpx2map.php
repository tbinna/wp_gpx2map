<?php
/**
 * Plugin Name: Adventure Tracks GPX 2 Map
 * Plugin URI: http://www.adventure-tracks.com
 * Description: Visualze gpx files as maps on posts.
 * Version: 1.1
 * Author: Tobi Binna
 * Author URI: http://www.adventure-tracks.com
 * License: GPL2
 */

$mygpGeotagsGeoMetatags_key = "adventureTracksGpx2map";

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}	

/** Define script location */
define( 'AT_GPX2MAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AT_GPX2MAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Enqueue scripts and styles
 */
function blog_post_map_scripts() {

	global $wp_styles;

	wp_enqueue_style('blog_post_map style', AT_BLOGPOSTMAP_PLUGIN_URL . 'blog_post_map.css');
	wp_enqueue_style('blog_post_map marker cluster', AT_BLOGPOSTMAP_PLUGIN_URL . 'marker_cluster.css');
	wp_enqueue_script('blog_post_map script', AT_BLOGPOSTMAP_PLUGIN_URL . 'blog_post_map.js', array(), false, false );

	// Mapbox style and script
	wp_enqueue_style('mapbox', 'https://api.tiles.mapbox.com/mapbox.js/v2.0.1/mapbox.css', array(), '2.0.1');
	wp_enqueue_script('mapbox', 'https://api.tiles.mapbox.com/mapbox.js/v2.0.1/mapbox.js', array(), '2.0.1', false );

	// Mapbox fullscreen plugin style and script
	wp_enqueue_style('mapbox fullscreen', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v0.0.3/leaflet.fullscreen.css', array(), '0.0.3');
	wp_enqueue_script('mapbox fullscreen', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v0.0.3/Leaflet.fullscreen.min.js', array('mapbox'), '0.0.3', false );

	// Mapbox locate plugin style and script
	wp_enqueue_style('mapbox locate', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.24.0/L.Control.Locate.css', array(), '0.24.0');
	wp_enqueue_style('mapbox locate ie', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.21.0/L.Control.Locate.ie.css', array('mapbox locate'), '0.21.0');
	$wp_styles->add_data( 'mapbox locate ie', 'conditional', 'IE 9' );
	wp_enqueue_script('mapbox locate', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-locatecontrol/v0.24.0/L.Control.Locate.js', array('mapbox'), '0.24.0', false );

	// Mapbox cluster plugin style and script
	wp_enqueue_style('mapbox marker cluster', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.css', array(), '0.4.0');
	wp_enqueue_script('mapbox marker cluster', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/leaflet.markercluster.js', array('mapbox'), '0.4.0', false );
}

add_action('wp_enqueue_scripts', 'blog_post_map_scripts');


class Marker {

	var $title;
	var $permalink;
	var $latLon;
	var $postThumbnailUrl;

	function Marker($title, $permalink, $latLon, $postThumbnailUrl) {
       $this->title = $title;
       $this->permalink = $permalink;
       $this->latLon = $latLon;
       $this->postThumbnailUrl = $postThumbnailUrl;
   }
}


/** Create and return map */
function getMap() {

	global $wp_query, $mygpGeotagsGeoMetatags_key;

	$markers = array();
	$posts = get_posts(array('numberposts' => 1000, 'meta_key' => $mygpGeotagsGeoMetatags_key, 'post_status' => 'publish'));

	foreach($posts as $post) {
		$positionData = get_post_meta($post->ID, $mygpGeotagsGeoMetatags_key, true);

		$dataSplitted = "";
		if($positionData['position'] != "") {
			$dataSplitted = array_map('doubleval', explode(";", $positionData[ 'position' ]));
		} else {
			continue; // post seems not to be georeferenced
		}

		$postThumbnailUrl = wp_get_attachment_url(get_post_thumbnail_id($post->ID));

		array_push($markers, new Marker(get_the_title($post->ID), get_permalink($post->ID), $dataSplitted, $postThumbnailUrl));
	}

	$mapId = 'map' . $wp_query->post->ID;

	$html = '<div id="' . $mapId . '" class="blog-post-map"></div>';
	$html .= '<script type="text/javascript">initMap(' . $mapId . ', ' . json_encode($markers) .');</script>';

	return $html;
	
}

/** Add map to post */
function addMap($content) {

	global $wp_query;
	
	$postId = $wp_query->post->ID;
	$shortcode = '[at_gpx2map]';

	$html = getMap();
	$content = str_replace($shortcode, $html, $content);

    return $content;
}
add_filter('the_content', 'addMap');

?>