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
function gpx2map_scripts() {

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

add_action('wp_enqueue_scripts', 'gpx2map_scripts');

/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'gpx2map_meta_boxes_setup' );
add_action( 'load-post-new.php', 'gpx2map_meta_boxes_setup' );

function gpx2map_meta_boxes_setup() {
	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'gpx2map_add_post_meta_boxes' );

	/* Save post meta on the 'save_post' hook. */
	add_action( 'save_post', 'gpx2map_save_post_class_meta', 10, 2 );
}

function gpx2map_add_post_meta_boxes() {
	add_meta_box('gpx2map-trail', esc_html__('GPX2Map', 'gpx2map'), 'gpx2map_post_class_meta_box', 'post', 'normal', 'default');
}

/* Display the post meta box. */
function gpx2map_post_class_meta_box($object, $box) { ?>

  <?php wp_nonce_field( basename( __FILE__ ), 'gpx2map_trail_nonce' ); ?>

  <p>
    <label for="gpx2map-trail-title"><?php _e( "Trail title", 'gpx2map' ); ?></label>
    <br />
    <input class="widefat" type="text" name="gpx2map-trail-title" id="gpx2map-trail-title" size="30" />
  </p>
<?php }

/* Save the meta box's post metadata. */
function gpx2map_save_post_class_meta( $post_id, $post ) {

  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['gpx2map_trail_nonce'] ) || !wp_verify_nonce( $_POST['gpx2map_trail_nonce'], basename( __FILE__ ) ) )
    return $post_id;

  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;

  /* Get the posted data and sanitize it for use as an HTML class. */
  $new_meta_value = ( isset( $_POST['gpx2map-trail-title'] ) ? sanitize_html_class( $_POST['gpx2map-trail-title'] ) : '' );

  /* Get the meta key. */
  $meta_key = 'gpx2map-trail';

  /* Get the meta value of the custom field key. */
  $meta_value = get_post_meta( $post_id, $meta_key, true );

  /* If a new meta value was added and there was no previous value, add it. */
  if ( $new_meta_value && '' == $meta_value )
    add_post_meta( $post_id, $meta_key, $new_meta_value, true );

  /* If the new meta value does not match the old value, update it. */
  elseif ( $new_meta_value && $new_meta_value != $meta_value )
    update_post_meta( $post_id, $meta_key, $new_meta_value );

  /* If there is no new meta value but an old value exists, delete it. */
  elseif ( '' == $new_meta_value && $meta_value )
    delete_post_meta( $post_id, $meta_key, $meta_value );
}

function gpx2map_trail_info_html() {
	/* Get the custom post class. */
    $trail_title = get_post_meta( $post_id, 'gpx2map-trail-title', true );

	$html = '<div id="gpx2map-trail-title">' . $trail_title . '</div>';

	retrun $html;
}

/* Filter the post class hook with our custom post class function. */
add_filter( 'the_content', 'gpx2map_add_trail_info' )

function gpx2map_add_trail_info($content) {
	* Get the current post ID. */
  	$post_id = get_the_ID();

  	if ( !empty( $post_id ) ) {
  		$shortcode = '[at_gpx2map]';

		$html = gpx2map_trail_info_html();
		$content = str_replace($shortcode, $html, $content);
	}

	return $content;
}

?>