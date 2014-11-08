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

/* Meta Keys */
$meta_keys = array(
	"trail_gpx"			=> 'gpx2map-gpx-file',
	"trail_title" 		=> 'gpx2map-trail-title',
	"trail_aka" 		=> 'gpx2map-trail-aka',
	"trail_length"		=> 'gpx2map-trail-length',
	"trail_total_up"	=> 'gpx2map-trail-total-up',
	"trail_total_down"	=> 'gpx2map-trail-total-down',
	"trail_trailhead"	=> 'gpx2map-trail-trailhead',
	"trail_howtofind"	=> 'gpx2map-trail-howtofind',
	"trail_desc"		=> 'gpx2map-trail-desc'
);

// $meta_keys = array($trail_title, $trail_aka, $trail_length, $trail_total_up, $trail_total_down, $trail_trailhead, $trail_howtofind, $trail_desc);



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

// 	wp_enqueue_style('blog_post_map style', AT_BLOGPOSTMAP_PLUGIN_URL . 'blog_post_map.css');
// 	wp_enqueue_style('blog_post_map marker cluster', AT_BLOGPOSTMAP_PLUGIN_URL . 'marker_cluster.css');
// 	wp_enqueue_script('blog_post_map script', AT_BLOGPOSTMAP_PLUGIN_URL . 'blog_post_map.js', array(), false, false );
}

add_action('wp_enqueue_scripts', 'gpx2map_scripts');

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

	global $meta_keys;
	wp_nonce_field( basename( __FILE__ ), 'gpx2map_nonce' );

	?>

	<p>
		<label for="gpx2map-gpx-file"><?php _e('GPX file', 'gpx2map')?></label>
		<br />
		<input type="text" name="gpx2map-gpx-file" id="gpx2map-gpx-file" value="<?php if( isset($prfx_stored_meta['meta-image'])) echo $prfx_stored_meta['meta-image'][0]; ?>" />
		<input type="button" id="gpx2map-gpx-file-button" class="button" value="<?php _e('Choose or Upload a GPX file', 'gpx2map')?>" />
	</p>

	<p>
		<label for="gpx2map-trail-title"><?php _e( "Title", 'gpx2map' ); ?></label>
		<br />
		<input class="widefat" type="text" name="gpx2map-trail-title" id="gpx2map-trail-title" value="<?php echo esc_attr(get_post_meta($post->ID, $meta_keys["trail_title"], true)); ?>" size="30" />
	</p>

	<p>
		<label for="gpx2map-trail-aka"><?php _e( "Also know as", 'gpx2map' ); ?></label>
		<br />
		<input class="widefat" type="text" name="gpx2map-trail-aka" id="gpx2map-trail-aka" value="<?php echo esc_attr(get_post_meta($post->ID, $meta_keys["trail_aka"], true)); ?>" size="30" />
	</p>

	<p>
		<label for="gpx2map-trail-length"><?php _e( "Length [km]", 'gpx2map' ); ?></label>
		<br />
		<input class="widefat" type="number" name="gpx2map-trail-length" id="gpx2map-trail-length" value="<?php echo esc_attr(get_post_meta( $post->ID, $meta_keys["trail_length"], true)); ?>" size="5" />
	</p>

	<p>
		<label for="gpx2map-trail-total-up"><?php _e( "Total elevation gain [m]", 'gpx2map' ); ?></label>
		<br />
		<input class="widefat" type="number" name="gpx2map-trail-total-up" id="gpx2map-trail-total-up" value="<?php echo esc_attr(get_post_meta( $post->ID, $meta_keys["trail_total_up"], true)); ?>" size="5" />
	</p>

	<p>
		<label for="gpx2map-trail-total-down"><?php _e( "Total elevation loss [m]", 'gpx2map' ); ?></label>
		<br />
		<input class="widefat" type="number" name="gpx2map-trail-total-down" id="gpx2map-trail-total-down" value="<?php echo esc_attr(get_post_meta( $post->ID, $meta_keys["trail_total_down"], true)); ?>" size="5" />
	</p>

	<p>
		<label for="gpx2map-trail-trailhead"><?php _e( "Trailhead", 'gpx2map' ); ?></label>
		<br />
		<input class="widefat" type="text" name="gpx2map-trail-trailhead" id="gpx2map-trail-trailhead" value="<?php echo esc_attr(get_post_meta( $post->ID, $meta_keys["trail_trailhead"], true)); ?>" size="30" />
	</p>

	<p>
		<label for="gpx2map-trail-howtofind"><?php _e( 'How to find the trail', 'gpx2map')?></label>
		<br />
		<textarea name="gpx2map-trail-howtofind" id="gpx2map-trail-howtofind"><?php echo esc_attr(get_post_meta( $post->ID, $meta_keys["trail_howtofind"], true)); ?></textarea>
	</p>

	<p>
		<label for="gpx2map-trail-desc"><?php _e( 'Trail Description', 'gpx2map')?></label>
		<br />
		<textarea name="gpx2map-trail-desc" id="gpx2map-trail-desc"><?php echo esc_attr(get_post_meta( $post->ID, $meta_keys["trail_desc"], true )); ?></textarea>
	</p>
<?php }

/* Save the meta box's post metadata. */
function gpx2map_save_meta( $post_id, $post ) {

	global $meta_keys;

	/* Verify the nonce before proceeding. */
	if ( !isset( $_POST['gpx2map_nonce'] ) || !wp_verify_nonce( $_POST['gpx2map_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	$post_type = get_post_type_object( $post->post_type );

	/* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ){
		return $post_id;  	
	}

	foreach($meta_keys as $key) {
		$meta_value = get_post_meta( $post_id, $key, true );
		$new_meta_value = isset( $_POST[$key] ) ? sanitize_text_field( $_POST[$key] ) : '';

		/* If a new meta value was added and there was no previous value, add it. */
		if ( $new_meta_value && '' == $meta_value ) {
			add_post_meta($post_id, $key, $new_meta_value, true);
		}

		/* If the new meta value does not match the old value, update it. */
		elseif ( $new_meta_value && $new_meta_value != $meta_value ) {
			update_post_meta($post_id, $key, $new_meta_value);
		}

		/* If there is no new meta value but an old value exists, delete it. */
		elseif ( '' == $new_meta_value && $meta_value) {
			delete_post_meta($post_id, $key, $meta_value);
		}
	}
}

function gpx2map_trail_info_html($post_id) {

	global $meta_keys;

    $title = get_post_meta( $post_id, $meta_keys["trail_title"], true );

	$html = '<div id="gpx2map-trail-title">' . $title . '</div>';

	return $html;
}

/* Filter the post class hook with our custom post class function. */
add_filter('the_content', 'gpx2map_add_trail_info');

function gpx2map_add_trail_info($content) {
	/* Get the current post ID. */
  	$post_id = get_the_ID();

  	if ( !empty( $post_id ) ) {
  		$shortcode = '[at_gpx2map]';

		$html = gpx2map_trail_info_html($post_id);
		$content = str_replace($shortcode, $html, $content);
	}

	return $content;
}

?>