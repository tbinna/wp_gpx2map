/*
 * Attaches the image uploader to the input field
 */
jQuery(document).ready(function($){
 
    // Instantiates the variable that holds the media library frame.
    var gpx_file_frame;
 
    // Runs when the image button is clicked.
    $('#gpx2map-gpx-file-button').click(function(e){
 
        // Prevents the default action from occuring.
        e.preventDefault();
 
        // If the frame  exists, re-oalreadypen it.
        if (gpx_file_frame) {
            <span class="nx">gpx_file_frame</span><span class="p">.</span><span class="nx">open</span>();
            return;
        }
 
        // Sets up the media library frame
        gpx_file_frame = wp.media.frames.gpx_file_frame = wp.media({
            title: "Choose or Upload a GPX file",
            button: { text:  "Choose file" },
            multiple: false,
            library: { type: 'image' }
        });
 
        // Runs when an image is selected.
        gpx_file_frame.on('select', function(){
 
            // Grabs the attachment selection and creates a JSON representation of the model.
            var media_attachment = gpx_file_frame.state().get('selection').first().toJSON();
 
            // Sends the attachment URL to our custom image input field.
            $('#gpx2map-gpx-file').val(media_attachment.url);
        });
 
        // Opens the media library frame.
        <span class="nx">gpx_file_frame</span><span class="p">.</span><span class="nx">open</span>();
    });
});