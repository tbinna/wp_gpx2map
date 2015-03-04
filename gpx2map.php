<?php
/**
 * Plugin Name: Adventure Tracks GPX 2 Map
 * Plugin URI: http://www.adventure-tracks.com
 * Description: Visualze gpx files as maps on posts.
 * Version: 0.2
 * Author: Tobi Binna
 * Author URI: http://www.adventure-tracks.com
 * License: GPL2
 */

/* Initialize blade template engine */
require_once 'vendor/autoload.php';

function twig() {
	$views = __DIR__ . '/views';
	$cache = __DIR__ . '/cache';

	$loader = new Twig_Loader_Filesystem($views);
	return new Twig_Environment($loader, array(
	    'cache' => false,
	    'strict_variables' => true
	));
}

$meta_fields = array(
		"trail_gpx"		=> array(
				"meta_key" 		=> 'gpx2map-gpx-file',
				"input_label"	=> 'GPX file',
				"input_type"	=> 'file',
				"input_value"	=> '',
				"input_attrs"	=> ''
			),
		"trail_title"	=> array(
				"meta_key" 		=> 'gpx2map-trail-title',
				"input_label"	=> 'Title',
				"input_type"	=> 'text',
				"input_value"	=> '',
				"input_attrs"	=> 'size=30'
			),
		"trail_aka"	=> array(
				"meta_key" 		=> 'gpx2map-trail-aka',
				"input_label"	=> 'Also know as',
				"input_type"	=> 'text',
				"input_value"	=> '',
				"input_attrs"	=> 'size=30'
			),
		"trail_length"	=> array(
				"meta_key" 		=> 'gpx2map-trail-length',
				"input_label"	=> 'Length [km]',
				"input_type"	=> 'number',
				"input_value"	=> '',
				"input_attrs"	=> 'size=5 step=any'
			),
		"trail_total_up"	=> array(
				"meta_key" 		=> 'gpx2map-trail-total-up',
				"input_label"	=> 'Total elevation gain [m]',
				"input_type"	=> 'number',
				"input_value"	=> '',
				"input_attrs"	=> 'size=5 min=0'
			),
		"trail_total_down"	=> array(
				"meta_key" 		=> 'gpx2map-trail-total-down',
				"input_label"	=> 'Total elevation loss [m]',
				"input_type"	=> 'number',
				"input_value"	=> '',
				"input_attrs"	=> 'size=5 min=0'
			),
		"trail_trailhead"	=> array(
				"meta_key" 		=> 'gpx2map-trail-trailhead',
				"input_label"	=> 'Trailhead',
				"input_type"	=> 'text',
				"input_value"	=> '',
				"input_attrs"	=> 'size=30'
			),
		"trail_howtofind"	=> array(
				"meta_key" 		=> 'gpx2map-trail-howtofind',
				"input_label"	=> 'How to find the trail',
				"input_type"	=> 'textarea',
				"input_value"	=> '',
				"input_attrs"	=> ''
			),
		"trail_desc"		=> array(
				"meta_key" 		=> 'gpx2map-trail-desc',
				"input_label"	=> 'Trail description',
				"input_type"	=> 'textarea',
				"input_value"	=> '',
				"input_attrs"	=> ''
			)

	);

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}	

/** Define script location */
define( 'AT_GPX2MAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AT_GPX2MAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

function meta_field_value($key) {
	global $meta_fields;
	return get_post_meta(get_the_ID(), $meta_fields[$key]["meta_key"], true);
}

/** Enqueue page scripts and styles */
function gpx2map_page_scripts() {
	global $meta_fields;

	wp_enqueue_style('gpx2map-boostrap-style', "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css", array(), '3.3.0');
	wp_enqueue_style('gpx2map-boostrap-theme-style', "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap-theme.min.css", array(), '3.3.0');
	wp_enqueue_script('gpx2map-boostrap-script', "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js", array(), '3.3.0', false);

	wp_enqueue_style('gpx2map-leaflet-style', "http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css", array(), '0.7.3');
	wp_enqueue_script('gpx2map-leaflet-script', "http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js", array(), '0.7.3', false);

	wp_enqueue_script('gpx2map-leaflet-gpx-script', AT_GPX2MAP_PLUGIN_URL . 'js/gpx.js', array('gpx2map-leaflet-script'));
	// wp_enqueue_script('gpx2map-leaflet-gpx-script-test', 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.2.0/leaflet-omnivore.min.js', array('gpx2map-leaflet-script'));

	wp_enqueue_style('gpx2map-page-style', AT_GPX2MAP_PLUGIN_URL . 'gpx2map-page.css');

	
	wp_register_script( 'gpx2map-trail-map', AT_GPX2MAP_PLUGIN_URL . 'js/trail-map.js', array('jquery'), false, true);

	$data_array = array( 'gpx_file' => meta_field_value("trail_gpx"));
	wp_localize_script( 'gpx2map-trail-map', 'data', $data_array );

	wp_enqueue_script( 'gpx2map-trail-map');
}

add_action('wp_enqueue_scripts', 'gpx2map_page_scripts');

/** Enqueue admin scripts and styles */
function gpx2map_admin_scripts() {
	global $typenow;

	if( $typenow == 'post' ) {
		wp_enqueue_media();
		wp_enqueue_script('gpx2map-gpx-upload', AT_GPX2MAP_PLUGIN_URL . 'js/gpx-upload.js', array('jquery'));
	}

	wp_enqueue_style('gpx2map-admin-style', AT_GPX2MAP_PLUGIN_URL . 'gpx2map-admin.css');
}

add_action('admin_enqueue_scripts', 'gpx2map_admin_scripts');

/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'gpx2map_meta_boxes_setup' );
add_action( 'load-post-new.php', 'gpx2map_meta_boxes_setup' );

function gpx2map_meta_boxes_setup() {
	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'gpx2map_add_post_meta_boxes' );

	/* Save post meta on the 'save_post' hook. */
	add_action( 'save_post', 'gpx2map_save_meta', 10, 2 );
}

function gpx2map_add_post_meta_boxes() {
	add_meta_box('gpx2map', esc_html__('GPX2Map Trail Info', 'gpx2map'), 'gpx2map_meta_box', 'post', 'normal', 'default');
}

/* Display the post meta box. */
function gpx2map_meta_box($post) {

	global $meta_fields;
	gpx2map_restore_meta($post->ID);

	wp_nonce_field( basename( __FILE__ ), 'gpx2map_nonce' );
	echo twig()->render('gpx2map_meta_box.twig.html', array('meta_fields' => $meta_fields));
}

/* Save the meta box's post metadata. */
function gpx2map_save_meta( $post_id, $post ) {
	global $meta_fields;

	/* Verify the nonce before proceeding. */
	if ( !isset( $_POST['gpx2map_nonce'] ) || !wp_verify_nonce( $_POST['gpx2map_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	$post_type = get_post_type_object( $post->post_type );

	/* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ){
		return $post_id;  	
	}

	foreach($meta_fields as $field) {
		$meta_key = $field["meta_key"];
		$meta_value = get_post_meta( $post_id, $meta_key, true );
		$new_meta_value = isset( $_POST[$meta_key] ) ? sanitize_text_field( $_POST[$meta_key] ) : '';

		/* If a new meta value was added and there was no previous value, add it. */
		if ( $new_meta_value && '' == $meta_value ) {
			add_post_meta($post_id, $meta_key, $new_meta_value, true);
		}

		/* If the new meta value does not match the old value, update it. */
		elseif ( $new_meta_value && $new_meta_value != $meta_value ) {
			update_post_meta($post_id, $meta_key, $new_meta_value);
		}

		/* If there is no new meta value but an old value exists, delete it. */
		elseif ( '' == $new_meta_value && $meta_value) {
			delete_post_meta($post_id, $meta_key, $meta_value);
		}
	}
}

function gpx2map_restore_meta($post_id) {
	global $meta_fields;

	foreach ($meta_fields as $key => $field) {
		$meta_fields[$key]["input_value"] = esc_attr(get_post_meta($post_id, $field["meta_key"], true));
	}
}

/* Filter the post class hook with our custom post class function. */
add_filter('the_content', 'gpx2map_add_trail_info');

function gpx2map_add_trail_info($content) {
	global $meta_fields;
	$post_id = get_the_ID();

	if ( !empty( $post_id ) ) {
		$shortcode = '[at_gpx2map]';
		gpx2map_restore_meta($post_id);

		$html = twig()->render('gpx2map_trail_info.twig.html', array('meta_fields' => $meta_fields));
		$content = str_replace($shortcode, $html, $content);
	}

	return $content;
}

?>