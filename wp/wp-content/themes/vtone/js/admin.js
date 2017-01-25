
var vtONESSImageIndex = 0;

function addImageToTable (img) {
	// verify that the image is appropriately sized before adding
	imgurl = img.url;
	if (img.height != 425 || img.width != 1000) {
		alert("Cannot add image " + img.url + ". Images must be 1000x425.");
		return false;
	}

	// set up some variables for table parts
	vtONESSImageIndex++;

	var tOrder = "<td><input type=\"text\" name=\"vtone-ml-theme-opts-ss[" + img.id + "][order]\" value=\"0\" maxlen=\"2\" size=\"3\" /></td>";
	var tImgURL = "<td><input type=\"hidden\" name=\"vtone-ml-theme-opts-ss[" + img.id + "][imgurl]\" value=\"" + imgurl + "\" />" + imgurl + "</td>";
	var tLink = "<td><input type=\"text\" name=\"vtone-ml-theme-opts-ss[" + img.id + "][href]\" value=\"\" size=\"50\" /></td>";
	var tEnabled = "<td><input type=\"checkbox\" name=\"vtone-ml-theme-opts-ss[" + img.id + "][enabled]\" checked/></td>";

	jQuery('#vtone-ml-theme-ss-image-table tbody').append('<tr>' + tOrder + tImgURL + tLink + tEnabled + '</tr>');
}

function removeImageFromTable (id) {
	jQuery('#vtone-ml-theme-ss-image-table #vtone-ml-theme-ss-image-' + id).fadeOut(function () {
		this.remove();
	});
}

//Uploading files
var file_frame;

jQuery(document).ready(function() {
    jQuery('.upload_image_button').on('click', function( event ){

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Slideshow Images',
            button: { text: 'Add Images', },
            library: { type: 'image' },
            multiple: true // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            selection = file_frame.state().get('selection');

            // do something with each attachment
            selection.map( function( attachment ) {
                attachment = attachment.toJSON();

                // Do something with attachment.id and/or attachment.url here
                addImageToTable(attachment);
            });

        });

        // Finally, open the modal
        file_frame.open();
    });
});
