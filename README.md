# GPX2Map

This small wordpress plugin allows the blog post writer to attach a trail description to any blog post and visualize the trail through a GPX file on a map. 


## Installation

To make the media uploader to accept GPX files add the following lines to the functions.php file of your theme:

```php
add_filter('upload_mimes', 'custom_upload_mimes');
function custom_upload_mimes ( $existing_mimes=array() ) {
	// add your extension to the array
	$existing_mimes['gpx'] = 'application/gpx+xml';
	return $existing_mimes;
}
```