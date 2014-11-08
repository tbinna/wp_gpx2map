wp_gpx2map
==========

To make the media uploader to accept GPX files add the following lines to the functions.php file of your theme:

add_filter('upload_mimes', 'custom_upload_mimes');
function custom_upload_mimes ( $existing_mimes=array() ) {
	// add your extension to the array
	$existing_mimes['gpx'] = 'application/gpx+xml';
	return $existing_mimes;
}