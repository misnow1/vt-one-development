<?php

// get the list of images from the database
$s = get_option('vtone-modularity-lite-slideshow-images', '');
if ($s == '') {
	// nothing was stored, return an empty array
	$s = array();
}
else {
	// something is available, unserialize it
	$s = unserialize($s);
}

//echo "<pre>";
//print_r($s);
//echo "</pre>";

?>
<div class="wrap wpgcp">

<h2>vtONE Modularity Lite Theme Options</h2>

<?php if (current_user_can('edit_theme_options')) { ?>

<form method="post" id="wpgcp-admin-form-element">
	<?php wp_nonce_field( 'vtone-modularity-list-theme-opts-save' ); ?>

	<h3>Slideshow Images</h3>

	<table class="widefat" id="vtone-ml-theme-ss-image-table">
		<thead>
			<tr>
				<th>Order</th>
				<th>URL</th>
				<th>Link</th>
				<th>Enabled</th>
				<th>Remove</th>
			</tr>
		</thead>
		<tbody>
			<?php
foreach ($s as $k => $img) {
	?>
	<tr id="vtone-ml-theme-ss-image-<?php echo $k ?>">
		<td><input type="text" name="vtone-ml-theme-opts-ss[<?php echo $k ?>][order]" value="<?php echo $img->order ?>" maxlen="2" size="3" /></td>
		<td><input type="hidden" name="vtone-ml-theme-opts-ss[<?php echo $k ?>][imgurl]" value="<?php echo $img->url ?>" /><?php echo $img->url ?></td>
		<td><input type="text" name="vtone-ml-theme-opts-ss[<?php echo $k ?>][href]" value="<?php echo $img->href ?>" size="50" /></td>
		<td><input type="checkbox" name="vtone-ml-theme-opts-ss[<?php echo $k ?>][enabled]" <?php echo ($img->enabled == 1) ? "checked" : "" ?>/></td>
		<td><button type="button" onClick="removeImageFromTable(<?php echo $k ?>)">Remove</button></td>
	</tr>
	<?php
}
			?>
		</tbody>
	</table>

<button type="button" class="button upload_image_button" name="wpml_image_select_button">Add New Image</button>
<br/>
<br/>
<input type="submit" class="button" name="vtone-ml-theme-opts-submit" id="vtone-ml-theme-opts-submit" value="Save All" />
</form>

<?php } // end of user can edit theme options ?>


</div>
