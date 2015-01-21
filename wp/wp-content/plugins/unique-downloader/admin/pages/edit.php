<div class="wrap wpus">

<?php echo wpus_get_messages() ?>

<?php 
//echo "<pre>\n";
//print_r($cf);
//echo "</pre>\n";
?>
<script type="text/javascript">

jQuery(document).ready(function() {
	jQuery( "#wpus-date" ).datepicker( { dateFormat: "yy-mm-dd" } );
	jQuery( "#wpus-date-new" ).datepicker( { dateFormat: "yy-mm-dd" } );
});

var file_frame;

function showMediaLibrary () {
 
	// If the media frame already exists, reopen it.
	if ( file_frame ) {
		file_frame.open();
		return;
	}
 
	// Create the media frame.
	file_frame = wp.media.frames.file_frame = wp.media({
		title: 'Album Image',
		button: { text: 'Select Image', },
		library: { type: 'image' },
		multiple: false // Set to true to allow multiple files to be selected
	});
 
	// When an image is selected, run a callback.
	file_frame.on( 'select', function() {
		// We set multiple to false so only get one image from the uploader
		selection = file_frame.state().get('selection').first().toJSON();
		
		jQuery('#wpus-image-url').val(selection.url);
	});
 
	// Finally, open the modal
	file_frame.open();
}

</script>

<h2><?php echo esc_html( __( 'Projects', 'wpus' ) ); ?></h2>

<?php do_action_ref_array( 'wpus_admin_before_subsubsub', array( &$cf ) ); ?>

<ul class="subsubsub">
<?php
$first = array_shift( $projects );
if ( ! is_null( $first ) ) : ?>
<li><a href="<?php echo wpus_admin_url( array( 'project' => $first->id ) ); ?>"<?php if ( $first->id == $current ) echo ' class="current"'; ?>><?php echo esc_html( $first->name ); ?></a></li>
<?php endif;
foreach ( $projects as $v ) : ?>
<li>| <a href="<?php echo wpus_admin_url( array( 'project' => $v->id ) ); ?>"<?php if ( $v->id == $current ) echo ' class="current"'; ?>><?php echo esc_html( $v->name ); ?></a></li>
<?php endforeach; ?>

<?php if ( wpus_admin_has_edit_cap()  && !$unsaved ) : ?>
<li class="addnew"><a class="thickbox" href="#TB_inline?width=600&height=550&inlineId=wpus-new-project">Add New</a></li>
<?php endif; ?>
</ul>

<br class="clear" />
<?php if ( $cf ) : ?>
<?php $disabled = ( wpus_admin_has_edit_cap() ) ? '' : ' disabled="disabled"'; ?>

<form method="post" action="<?php echo wpus_admin_url( array( 'project' => $current ) ); ?>" id="wpus-admin-form-element">
	<?php if ( wpus_admin_has_edit_cap() ) wp_nonce_field( 'wpus-save_' . $current ); ?>
	<input type="hidden" id="wpus-id" name="wpus-id" value="<?php echo $current; ?>" />

	<table class="widefat">
	<tbody>
	<tr>
	<td scope="col">
	<div style="position: relative;">
		<table class="novpad">
		<tr>
			<td>Name:</td>
			<td><input type="text" id="wpus-name" name="wpus-name" size="40" value="<?php echo esc_attr( $cf->name ); ?>"<?php echo $disabled; ?> /></td>
		</tr>	
		<tr>
			<td>Key:</td>
			<td><input type="text" id="wpus-key" name="wpus-key" size="10" maxlength="10" value="<?php echo $cf->key; ?>"/> (format: [a-z][0-9]{2})</td>
		</tr>
		<tr>
			<td>SKU:</td>
			<td><input type="text" id="wpus-sku" name="wpus-sku" size="10" maxlength="20" value="<?php echo $cf->sku; ?>"/></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="checkbox" id="wpus-enabled" name="wpus-enabled" <?php echo $cf->enabled == 1 ? "checked" : "" ?>/>Enabled
		</tr>
		<tr>
			<td>Date Available:</td>
			<td><input type="text" id="wpus-date" name="wpus-date" size="30" value="<?php echo $cf->date_available; ?>"/></td>
		</tr>
		<tr>
			<td>Image URL:</td>
			<td><input type="text" id="wpus-image-url" name="wpus-image-url" size="60" value="<?php echo $cf->image_url; ?>"/> <button type="button" class="button" onClick="showMediaLibrary()">Select</button></td>
		</tr>
		<tr>
			<td>CDN URL:</td>
			<td><input type="text" id="wpus-cdn-url" name="wpus-cdn-url" size="60" value="<?php echo $cf->cdn_url; ?>"/></td>
		</tr>
		<tr>
			<td>CDN URL 2:</td>
			<td><input type="text" id="wpus-cdn-url2" name="wpus-cdn-url2" size="60" value="<?php echo $cf->cdn_url2; ?>"/></td>
		</tr>
		<tr>
			<td>CDN Server:</td>
			<td><select id="wpus-cdn-server" name="wpus-cdn-server" >
			<?php foreach ($wpus->GetCDNServers(true) as $url) {
				$selected = ($cf->cdn_server == $url); ?>
				<option value="<?php echo $url ?>" <?php echo ($selected ? "selected" : "") ?>><?php echo $url ?></option>
			<?php } // end foreach ?>
			</select>
			</td>
		</tr>
		</table>
		
		<div class="actions-link" style="float: right">
		<input type="submit" class="button button-highlighted" name="wpus-save" value="<?php echo esc_attr( __( 'Save', 'wpus' ) ); ?>" />

		<?php if ( wpus_admin_has_edit_cap() && ! $unsaved ) : ?>
			<?php $delete_nonce = wp_create_nonce( 'wpus-delete_' . $current ); ?>
			<input type="submit" name="wpus-delete" class="button" value="<?php echo esc_attr( __( 'Delete', 'wpus' ) ); ?>"
			<?php echo "onclick=\"if (confirm('" .
				esc_js( __( "You are about to delete this project.\n  'Cancel' to stop, 'OK' to delete.", 'wpus' ) ) .
				"')) {this.form._wpnonce.value = '$delete_nonce'; return true;} return false;\""; ?> />
		</div>
		<?php endif; ?>
	</div>
	</td>
	</tr>
	</tbody>
	</table>

<?php do_action_ref_array( 'wpus_admin_after_general_settings', array( &$cf ) ); ?>

<?php if ( wpus_admin_has_edit_cap() ) : ?>
<h2>Project Codes</h2>
<?php
	if (!is_array($codes)) {
		echo "<div>No codes exist for this project.</div>\n";
	}
	else {
		$numcodes = count($codes);
		?>
<div>Number of codes for this project: <?php echo $numcodes?>. <a href="<?php echo wpus_admin_url(array('project' => $cf->id, 'dl' => 1)) ?>">Download as CSV</a>.</div>
	<table class="widefat" style="margin-top: 1em;">
	<thead>
		<tr>
			<th scope="col">Code</th>
			<th scope="col">Remaining Uses</th>
			<th scope="col">Enabled</th>
			<th scope="col">Registered User</th>
		</tr>
	</thead>

	<tbody>
<?php 
	foreach ($codes as $code) {
		echo "<tr>\n";
		
		echo "<td><pre style=\"margin-bottom: 0px; margin-top: 0px;\">" . $code['href'] . "</pre></td>\n";
		echo "<td>" . $code['remaining_uses'] . "</td>\n";
		echo "<td>" . ($code['enabled'] == 1 ? 'Yes' : 'No') . "</td>\n";
		echo "<td>" . $code['user'] . "</td>\n";
		echo "</tr>";
	}
?>


	</tbody>
	</table>

<?php 
	}	// end else array($codes)

endif; ?>

<?php if (!$unsaved && wpus_admin_has_edit_cap() ) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<thead><tr><th scope="col" colspan="2">Generate Codes</th></tr></thead>

	<tbody>
	<tr>
	
	<td scope="col">
		<div>To generate more codes for this project, enter a number and click "Generate Codes"</div>
		<input type="text" name="wpus-num-codes" class="codes" value="100" />
		<input type="submit" name="wpus-gen-codes" class="codes button" value="Generate Codes"  />
	</td>

	</tr>
	</tbody>
	</table>

<?php endif; ?>

<?php do_action_ref_array( 'wpus_admin_after_form', array( &$cf ) ); ?>

</form>

<?php endif; ?>

</div>

<div id="wpus-new-project" style="display:none;">

<form method="post" action="<?php echo wpus_admin_url( array( 'project' => -1 ) ); ?>" id="wpus-form-new-project">
	<h2>Create New Project</h2>
	
	<?php if ( wpus_admin_has_edit_cap() ) wp_nonce_field( 'wpus-save-new' ); ?>
	<input type="hidden" id="wpus-id-new" name="wpus-id" value="-1" />

		<table class="novpad">
		<tr>
			<td>Name:</td>
			<td><input type="text" id="wpus-name-new" name="wpus-name" size="40" value=""<?php echo $disabled; ?> /></td>
		</tr>	
		<tr>
			<td>Key:</td>
			<td><input type="text" id="wpus-key-new" name="wpus-key" size="10" maxlength="10" value=""/> (format: [a-z][0-9]{2})</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="checkbox" id="wpus-enabled-new" name="wpus-enabled" checked />Enabled
		</tr>
		<tr>
			<td>Date Available:</td>
			<td><input type="text" id="wpus-date-new" name="wpus-date" size="30" value=""/></td>
		</tr>
		<tr>
			<td>Image URL:</td>
			<td><input type="text" id="wpus-image-url-new" name="wpus-image-url" size="60" value=""/> <button type="button" class="button" onClick="showMediaLibrary()">Select</button></td>
		</tr>
		<tr>
			<td>CDN URL:</td>
			<td><input type="text" id="wpus-cdn-url-new" name="wpus-cdn-url" size="60" value=""/></td>
		</tr>
		<tr>
			<td>CDN Server:</td>
			<td><input type="text" id="wpus-cdn-server-new" name="wpus-cdn-server" size="30" value=""/></td>
		</tr>
		</table>
		
		<div class="actions-link" style="float: right">
		<input type="submit" class="button button-highlighted" name="wpus-save" value="<?php echo esc_attr( __( 'Save', 'wpus' ) ); ?>" />
</form>
</div>
