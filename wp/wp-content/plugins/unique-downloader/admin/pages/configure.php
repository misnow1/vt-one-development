<script type="text/javascript">
function addCDNServer() {
	var cdn_server = jQuery('#wpus-cdn-server-new').val().trim();

	if (cdn_server != "") {
		jQuery('#wpus-cdn-server-table tbody').append('<tr><td><input type="hidden" name="wpus_cdn_server[' + cdn_server + ']" value="' + cdn_server + '"/>' + cdn_server + '</td></tr>');
		jQuery('#wpus-cdn-server-new').val('');

	}

	return true;
}

function deleteCDNServer(rowNum) {
	jQuery('#wpus-cdn-server-table-row-' + rowNum).remove();
}

</script>

<div class="wrap wpgcp">

<?php echo wpus_get_messages() ?>

<h2>Unique Downloader Configuration</h2>

<?php if (wpus_admin_has_edit_cap()) { ?>

<form method="post" id="wpgcp-admin-form-element">
	<?php wp_nonce_field( 'wpus-configuration' ); ?>

	<h3>CDN Servers</h3>
	
	<table class="widefat" id="wpus-cdn-server-table">
		<thead>
			<tr>
				<th>CDN Server</th>
				<th>Delete</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			$row = 0;
foreach ($cdn_servers as $k => $url) {
	$row++;
	?>
	<tr id="wpus-cdn-server-table-row-<?php echo $row?>">
		<td><input type="hidden" name="wpus_cdn_server[<?php echo $url ?>]" value="<?php echo $url ?>"/><?php echo $url ?></td>
		<td><button type="button" class="button" name="wpus-cdn-servers-delete" onClick="deleteCDNServer(<?php echo $row ?>);">Delete CDN Server</button></td>
	</tr>	
	<?php 
}
			?>
		</tbody>
	</table>
<br/>
<input type="text" name="wpus-cdn-server-new" id="wpus-cdn-server-new" />
<button class="button" type="button" name="wpus-cdn-servers-add-btn" id="wpus-cdn-servers-add" onClick="addCDNServer();">Add CDN Server</button>
<br/>
<br/>
	<table class="widefat" id="wpus-cdn-server-table">
		<tbody>
			<tr>
				<td>Square API Token:</td>
				<td><input type="text" name="wpus_square_api_token" value="<?php echo $wpus->GetSquareAPIToken() ?>" size="50" /></td>
			</tr>
		</tbody>
	</table>


<br/>
<input type="submit" name="wpus-configure-save" id="wpus-configure-save" class="button" value="Save All" />
</form>

<?php } // end of user can edit theme options ?>


</div>
