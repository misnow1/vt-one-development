<?php

add_action( 'add_meta_boxes', 'vtone_modularity_lite_add_ss_meta');

add_action( 'edit_attachment', 'vtone_modularity_lite_save_ss_meta');

function vtone_modularity_lite_add_ss_meta () {
	add_meta_box(
		'wpml_meta_options',
		'Media List Options',
		'vtone_modularity_lite_show_ss_meta',
		'attachment'
	);
}

function vtone_modularity_lite_show_ss_meta ($post) {
	wp_nonce_field(plugin_basename(__FILE__), 'wpml_meta_nonce');
	
	$postID = $post->ID;
	
	$typeKey = get_post_meta($postID, '_wpml_type_key', true );
	$order = get_post_meta($postID, '_wpml_order', true );
	$eventImage = get_post_meta($postID, '_wpml_event_image', true );
	$eventName = get_post_meta($postID, '_wpml_event_name', true );
	$mediaDescription = get_post_meta($postID, '_wpml_media_description', true );
	
	?>
	<script language="JavaScript">
		function showMediaLibrary () {
			formfield = jQuery('#wpml_field_event_image').attr('name');
			tb_show('', 'media-upload.php?type=image&TB_iframe=true');
			return false;
		}

		jQuery(document).ready(function() {
			window.send_to_editor = function(html) {
				imgurl = jQuery('img',html).attr('src');
				jQuery('#wpml_field_event_image').val(imgurl);
				tb_remove();
				}
		});
	</script>
	<?php 
	
	echo '<table>';
	echo '<tr><td><label for="wpml_field_type_key">Type Key:</label></td>';
	echo '<td><input type="text" id="wpml_field_type_key" name="wpml_field_type_key" value="' . $typeKey . '" size="10" /></td></tr>';
	echo "\n";
	
	echo '<tr><td><label for="wpml_field_order">Order:</label></td>';
	echo '<td><input type="text" id="wpml_field_order" name="wpml_field_order" value="' . $order . '" size="3" /></td></tr>';
	echo "\n";
	
	echo '<tr><td><label for="wpml_field_event_image">Event Image:</label></td>';
	echo '<td><input type="text" id="wpml_field_event_image" name="wpml_field_event_image" value="' . $eventImage . '" size="50" /> <button type="button" name="wpml_image_select_button" onClick="showMediaLibrary();">Select</button></td></tr>';
	echo "\n";
	
	echo '<tr><td><label for="wpml_field_event_name">Event Name:</label></td>';
	echo '<td><input type="text" id="wpml_field_event_name" name="wpml_field_event_name" value="' . $eventName . '" size="25" /></td></tr>';
	echo "\n";
		
	echo '<tr><td><label for="wpml_field_media_description">Description:</label></td>';
	echo '<td><input type="text" id="wpml_field_media_description" name="wpml_field_media_description" value="' . $mediaDescription . '" size="50" /></td></tr>';
	echo "\n</table>\n";
	
}

function vtone_modularity_lite_save_ss_meta ($post_id) {
	/*
	 * Bail out if autosave is go
	 */
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	/*
	 * Also bail out if the nonce doesn't match
	 */
	if ( !wp_verify_nonce( $_POST['wpml_meta_nonce'], plugin_basename( __FILE__ ) ) ) {
		echo "<p>Nonce check failed.</p>\n";
		return;		
	}
	// Check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	}
	else {
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			echo "<p>Bailing on permissions.</p>\n";
			die();
			return;
		}
	}
	
	/*
	 * Do the needful
	 */
	$typeKey = sanitize_post_field('wpml_field_type_key', $_POST['wpml_field_type_key'], $post_id, 'edit');
	$order = sanitize_post_field('wpml_field_order', $_POST['wpml_field_order'], $post_id, 'edit');
	$eventImage = sanitize_post_field('wpml_field_event_image', $_POST['wpml_field_event_image'], $post_id, 'edit');
	$eventName = sanitize_post_field('wpml_field_event_name', $_POST['wpml_field_event_name'], $post_id, 'edit');
	$mediaDescription = sanitize_post_field('wpml_field_media_description', $_POST['wpml_field_media_description'], $post_id, 'edit');
	
	update_post_meta($post_id, '_wpml_type_key', $typeKey);
	update_post_meta($post_id, '_wpml_order', $order);
	update_post_meta($post_id, '_wpml_event_image', $eventImage);
	update_post_meta($post_id, '_wpml_event_name', $eventName);
	update_post_meta($post_id, '_wpml_media_description', $mediaDescription);
	
}

add_action( 'add_meta_boxes', 'vtone_modularity_lite_add_ss_meta');

add_action( 'edit_attachment', 'vtone_modularity_lite_save_ss_meta');

function vtone_modularity_lite_add_ss_meta () {
	add_meta_box(
	'wpml_meta_options',
	'Media List Options',
	'vtone_modularity_lite_show_ss_meta',
	'attachment'
			);
}

function vtone_modularity_lite_show_ss_meta ($post) {
	wp_nonce_field(plugin_basename(__FILE__), 'wpml_meta_nonce');

	$postID = $post->ID;

	$typeKey = get_post_meta($postID, '_wpml_type_key', true );
	$order = get_post_meta($postID, '_wpml_order', true );
	$eventImage = get_post_meta($postID, '_wpml_event_image', true );
	$eventName = get_post_meta($postID, '_wpml_event_name', true );
	$mediaDescription = get_post_meta($postID, '_wpml_media_description', true );

	?>
	<script language="JavaScript">
		function showMediaLibrary () {
			formfield = jQuery('#wpml_field_event_image').attr('name');
			tb_show('', 'media-upload.php?type=image&TB_iframe=true');
			return false;
		}

		jQuery(document).ready(function() {
			window.send_to_editor = function(html) {
				imgurl = jQuery('img',html).attr('src');
				jQuery('#wpml_field_event_image').val(imgurl);
				tb_remove();
				}
		});
	</script>
	<?php 
	
	echo '<table>';
	echo '<tr><td><label for="wpml_field_type_key">Type Key:</label></td>';
	echo '<td><input type="text" id="wpml_field_type_key" name="wpml_field_type_key" value="' . $typeKey . '" size="10" /></td></tr>';
	echo "\n";
	
	echo '<tr><td><label for="wpml_field_order">Order:</label></td>';
	echo '<td><input type="text" id="wpml_field_order" name="wpml_field_order" value="' . $order . '" size="3" /></td></tr>';
	echo "\n";
	
	echo '<tr><td><label for="wpml_field_event_image">Event Image:</label></td>';
	echo '<td><input type="text" id="wpml_field_event_image" name="wpml_field_event_image" value="' . $eventImage . '" size="50" /> <button type="button" name="wpml_image_select_button" onClick="showMediaLibrary();">Select</button></td></tr>';
	echo "\n";
	
	echo '<tr><td><label for="wpml_field_event_name">Event Name:</label></td>';
	echo '<td><input type="text" id="wpml_field_event_name" name="wpml_field_event_name" value="' . $eventName . '" size="25" /></td></tr>';
	echo "\n";
		
	echo '<tr><td><label for="wpml_field_media_description">Description:</label></td>';
	echo '<td><input type="text" id="wpml_field_media_description" name="wpml_field_media_description" value="' . $mediaDescription . '" size="50" /></td></tr>';
	echo "\n</table>\n";
	
}

function vtone_modularity_lite_save_ss_meta ($post_id) {
	/*
	 * Bail out if autosave is go
	 */
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	/*
	 * Also bail out if the nonce doesn't match
	 */
	if ( !wp_verify_nonce( $_POST['wpml_meta_nonce'], plugin_basename( __FILE__ ) ) ) {
		echo "<p>Nonce check failed.</p>\n";
		return;		
	}
	// Check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	}
	else {
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			echo "<p>Bailing on permissions.</p>\n";
			die();
			return;
		}
	}
	
	/*
	 * Do the needful
	 */
	$typeKey = sanitize_post_field('wpml_field_type_key', $_POST['wpml_field_type_key'], $post_id, 'edit');
	$order = sanitize_post_field('wpml_field_order', $_POST['wpml_field_order'], $post_id, 'edit');
	$eventImage = sanitize_post_field('wpml_field_event_image', $_POST['wpml_field_event_image'], $post_id, 'edit');
	$eventName = sanitize_post_field('wpml_field_event_name', $_POST['wpml_field_event_name'], $post_id, 'edit');
	$mediaDescription = sanitize_post_field('wpml_field_media_description', $_POST['wpml_field_media_description'], $post_id, 'edit');
	
	update_post_meta($post_id, '_wpml_type_key', $typeKey);
	update_post_meta($post_id, '_wpml_order', $order);
	update_post_meta($post_id, '_wpml_event_image', $eventImage);
	update_post_meta($post_id, '_wpml_event_name', $eventName);
	update_post_meta($post_id, '_wpml_media_description', $mediaDescription);
	
}