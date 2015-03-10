<?php

class MetaFields {
	private $fields = array(
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

	public function restore($post) {
		foreach ($this->fields as $key => $field) {
			$meta_key = $field["meta_key"];
			$meta_value = get_post_meta($post->ID, $meta_key, true);
			$this->fields[$key]["input_value"] = esc_attr($meta_value);
		}
	}

	public function save($post) {
		foreach($this->fields as $field) {
			$meta_key = $field["meta_key"];
			$meta_value = get_post_meta($post->ID, $meta_key, true );
			$new_meta_value = isset($_POST[$meta_key]) ? sanitize_text_field($_POST[$meta_key]) : '';

			/* If a new meta value was added and there was no previous value, add it. */
			if ($new_meta_value && '' == $meta_value) {
				add_post_meta($post->ID, $meta_key, $new_meta_value, true);
			}

			/* If the new meta value does not match the old value, update it. */
			elseif ($new_meta_value && $new_meta_value != $meta_value) {
				update_post_meta($post->ID, $meta_key, $new_meta_value);
			}

			/* If there is no new meta value but an old value exists, delete it. */
			elseif ('' == $new_meta_value && $meta_value) {
				delete_post_meta($post->ID, $meta_key, $meta_value);
			}
		}
	}

	public function fields() {
		return $this->fields;
	}

	public function value($key) {
		return $this->fields[$key]["input_value"];
	}

	public function meta_key($key) {
		return $this->fields[$key]["meta_key"];	
	}
}

?>