<?php

include 'MetaFields.class.php';
require_once 'vendor/autoload.php';

class Gpx2Map {
	private $twig;
	private $plugin_dir_url;
	private $meta_fields;

	public function __construct($plugin_dir_url) {
		$this->twig = self::init_twig();
		$this->plugin_dir_url = $plugin_dir_url;
		$this->meta_fields = new MetaFields();

		/* Setup meta boxes on load of post editor screen. */
		add_action('load-post.php', array($this, 'meta_boxes_setup'));
		add_action('load-post-new.php', array($this, 'meta_boxes_setup'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'register_page_scripts'));

		add_shortcode('at_gpx2map', array($this, 'render_shortcode'));
	}

	// Callback
	public function enqueue_admin_scripts() {
		global $typenow;
		if($typenow == 'post') {
			wp_enqueue_media();
			wp_enqueue_script('gpx2map-gpx-upload', $this->plugin_dir_url . 'js/gpx-upload.js', array('jquery'));
		}

		wp_enqueue_style('gpx2map-admin-style', $this->plugin_dir_url . 'gpx2map-admin.css');
	}

	// Callback
	public function register_page_scripts() {
		wp_register_style('gpx2map-boostrap-style', $this->plugin_dir_url . "/css/bootstrap.min.css");
		wp_register_style('gpx2map-boostrap-theme-style', $this->plugin_dir_url . "/css/bootstrap-theme.min.css");

		wp_register_style('gpx2map-leaflet-style', "http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css", array(), '0.7.3');
		wp_register_script('gpx2map-leaflet-script', "http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js", array(), '0.7.3', false);
		wp_register_script('gpx2map-leaflet-gpx-script', $this->plugin_dir_url . 'js/gpx.js', array('gpx2map-leaflet-script'));

		wp_register_style('gpx2map-page-style', $this->plugin_dir_url . 'gpx2map-page.css');

		wp_register_script( 'gpx2map-trail-map', $this->plugin_dir_url . 'js/trail-map.js', array('jquery'), false, true);
	}

	// Callback
	public function meta_boxes_setup() {
		add_action( 'add_meta_boxes', array($this, 'add_post_meta_boxes'));
		add_action( 'save_post', array($this, 'save'), 10, 2 );
	}

	// Callback meta_boxes_setup
	public function add_post_meta_boxes() {
		add_meta_box('gpx2map', esc_html__('GPX2Map Trail Info', 'gpx2map'), array($this, 'meta_box'), 'post', 'normal', 'default');
	}

	// Callback add_meta_box
	public function meta_box($post) {
		$this->meta_fields->restore($post);

		wp_nonce_field( basename( __FILE__ ), 'gpx2map_nonce' );
		echo $this->twig->render('gpx2map_meta_box.twig.html', array('meta_fields' => $this->meta_fields->fields()));
	}

	// Callback meta_boxes_setup
	public function save( $post_id, $post ) {

		$post_type = get_post_type_object($post->post_type);

		if(!$this->isNonceValid() || !$this->userHasEditPermission($post_type, $post_id)) {
			return $post_id;
		}

		$this->meta_fields->save($post);
	}

	// Callback render short code
	public function render_shortcode() {
		$this->meta_fields->restore(get_post(get_the_ID()));
		$this->enqueue_page_scripts();
		return $this->twig->render('gpx2map_trail_info.twig.html', array('meta_fields' => $this->meta_fields->fields()));
	}

	private function isNonceValid() {
		return isset( $_POST['gpx2map_nonce'] ) && wp_verify_nonce( $_POST['gpx2map_nonce'], basename( __FILE__ ));
	}

	private function userHasEditPermission($post_type, $post_id) {
		return current_user_can( $post_type->cap->edit_post, $post_id);
	}

	private static function init_twig() {
		$views = __DIR__ . '/views';
		$cache = __DIR__ . '/cache';

		$loader = new Twig_Loader_Filesystem($views);
		return new Twig_Environment($loader, array(
		    'cache' => false,
		    'strict_variables' => true
		));
	}

	private function enqueue_page_scripts() {
		wp_enqueue_style('gpx2map-boostrap-style');
		wp_enqueue_style('gpx2map-boostrap-theme-style');
		wp_enqueue_script('gpx2map-boostrap-script');

		wp_enqueue_style('gpx2map-leaflet-style');
		wp_enqueue_script('gpx2map-leaflet-script');
		wp_enqueue_script('gpx2map-leaflet-gpx-script');

		wp_enqueue_style('gpx2map-page-style');

		$data_array = array('gpx_file' => $this->meta_fields->value("trail_gpx"));
		wp_localize_script( 'gpx2map-trail-map', 'data', $data_array );

		wp_enqueue_script( 'gpx2map-trail-map');
	}
}

?>