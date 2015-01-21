<?php

/* No table warning */
if ( ! wpps_table_exists() ) {
	if ( current_user_can( 'activate_plugins' ) ) {
		$create_table_link_url = wpps_admin_url( array( 'wpps-create-table' => 1 ) );
		$create_table_link_url = wp_nonce_url( $create_table_link_url, 'wpps-create-table' );
		$message = sprintf(
			__( '<strong>The database table for Prayer Schedule does not exist.</strong> You must <a href="%s">create the table</a> for it to work.', 'wpps' ),
			$create_table_link_url );
	} else {
		$message = __( "<strong>The database table for Prayer Schedule does not exist.</strong>", 'wpps' );
	}
?>
	<div class="wrap">
	<?php screen_icon( 'edit-pages' ); ?>
	<h2><?php echo esc_html( __( 'Prayer Schedule', 'wpps' ) ); ?></h2>
	<div id="message" class="updated fade">
	<p><?php echo $message; ?></p>
	</div>
	</div>
<?php
	return;
}

?><div class="wrap wpps">

<?php screen_icon( 'edit-pages' ); ?>

<h2><?php echo esc_html( __( 'Prayer Schedule', 'wpps' ) ); ?></h2>

<?php do_action_ref_array( 'wpps_admin_before_subsubsub', array( &$cf ) ); ?>

<ul class="subsubsub">
<?php
$first = array_shift( $prayer_schedules );
if ( ! is_null( $first ) ) : ?>
<li><a href="<?php echo wpps_admin_url( array( 'prayerschedule' => $first->id ) ); ?>"<?php if ( $first->id == $current ) echo ' class="current"'; ?>><?php echo esc_html( $first->title ); ?></a></li>
<?php endif;
foreach ( $prayer_schedules as $v ) : ?>
<li>| <a href="<?php echo wpps_admin_url( array( 'prayerschedule' => $v->id ) ); ?>"<?php if ( $v->id == $current ) echo ' class="current"'; ?>><?php echo esc_html( $v->title ); ?></a></li>
<?php endforeach; ?>

<?php if ( wpps_admin_has_edit_cap() ) : ?>
<li class="addnew"><a class="thickbox<?php if ( $unsaved ) echo ' current'; ?>" href="#TB_inline?height=300&width=400&inlineId=wpps-lang-select-modal"><?php echo esc_html( __( 'Add new', 'wpps' ) ); ?></a></li>
<?php endif; ?>
</ul>

<br class="clear" />
<?php if ( $cf ) : ?>
<?php $disabled = ( wpps_admin_has_edit_cap() ) ? '' : ' disabled="disabled"'; ?>

<form method="post" action="<?php echo wpps_admin_url( array( 'prayerschedule' => $current ) ); ?>" id="wpps-admin-form-element">
	<?php if ( wpps_admin_has_edit_cap() ) wp_nonce_field( 'wpps-save_' . $current ); ?>
	<input type="hidden" id="wpps-id" name="wpps-id" value="<?php echo $current; ?>" />

	<table class="widefat">
	<tbody>
	<tr>
	<td scope="col">
	<div style="position: relative;">
		<input type="text" id="wpps-title" name="wpps-title" size="40" value="<?php echo esc_attr( $cf->title ); ?>"<?php echo $disabled; ?> />

		<?php if ( ! $unsaved ) : ?>
		<p class="tagcode">
			<?php echo esc_html( __( "Copy this code and paste it into your post, page or text widget content.", 'wpps' ) ); ?><br />

			<input type="text" size="50" id="prayer-schedule-anchor-text" onfocus="this.select();" readonly="readonly" />
		</p>
		<?php endif; ?>

		<?php if ( wpps_admin_has_edit_cap() ) : ?>
		<div class="save-prayer-schedule">
			<input type="submit" class="button button-highlighted" name="wpps-save" value="<?php echo esc_attr( __( 'Save', 'wpps' ) ); ?>" />
		</div>
		<?php endif; ?>

		<?php if ( wpps_admin_has_edit_cap() && ! $unsaved ) : ?>
		<div class="actions-link">
			<?php $copy_nonce = wp_create_nonce( 'wpps-copy_' . $current ); ?>
			<input type="submit" name="wpps-copy" class="copy" value="<?php echo esc_attr( __( 'Copy', 'wpps' ) ); ?>"
			<?php echo "onclick=\"this.form._wpnonce.value = '$copy_nonce'; return true;\""; ?> />
			|

			<?php $delete_nonce = wp_create_nonce( 'wpps-delete_' . $current ); ?>
			<input type="submit" name="wpps-delete" class="delete" value="<?php echo esc_attr( __( 'Delete', 'wpps' ) ); ?>"
			<?php echo "onclick=\"if (confirm('" .
				esc_js( __( "You are about to delete this contact form.\n  'Cancel' to stop, 'OK' to delete.", 'wpps' ) ) .
				"')) {this.form._wpnonce.value = '$delete_nonce'; return true;} return false;\""; ?> />
		</div>
		<?php endif; ?>
	</div>
	</td>
	</tr>
	</tbody>
	</table>

<?php do_action_ref_array( 'wpps_admin_after_general_settings', array( &$cf ) ); ?>

<?php if ( wpps_admin_has_edit_cap() ) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<thead><tr><th scope="col" colspan="2">Schedule Information</th></tr></thead>

	<tbody>
	<tr>
	
	<td scope="col" style="width: 75%;">
	<div>Start Time: <input type="text" id="wpps-form" name="wpps-start" value="<?php echo esc_html( $cf->start ); ?>" />(YYYY-MM-DD HH:MM)</div>
	<div>End Time: <input type="text" id="wpps-form" name="wpps-end" value="<?php echo esc_html( $cf->end ); ?>" />(YYYY-MM-DD HH:MM)</div>
	<div>Period Length: <input type="text" id="wpps-form" name="wpps-period_len" value="<?php echo esc_html( $cf->period_len ); ?>" />(minutes)</div>
	<div>Location: <textarea id="wpps-form" name="wpps-location" cols="100" rows="5"><?php echo esc_html( $cf->location ); ?></textarea></div>
	<div>Description: <textarea id="wpps-form" name="wpps-description" cols="100" rows="10"><?php echo esc_html( $cf->description ); ?></textarea></div>
	</td>

	<td scope="col" style="width: 25%;">
	<div id="taggenerator"></div>
	</td>
	


	</tr>
	</tbody>
	</table>

<?php endif; ?>

<?php do_action_ref_array( 'wpps_admin_after_form', array( &$cf ) ); ?>

<?php if ( wpps_admin_has_edit_cap() ) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<thead><tr><th scope="col" colspan="2"><?php echo esc_html( __( 'Mail', 'wpps' ) ); ?></th></tr></thead>

	<tbody>
	<tr>
	<td scope="col" style="width: 50%;">
	<div>When a user signs up for a time period, the system will send him/her an e-mail.  A copy of the message sent to the user will always be carbon-copied to the address below.</div>
	<br/>
	<div class="mail-field">
	<label for="wpps-mail-sender"><?php echo esc_html( __( 'From Address:', 'wpps' ) ); ?></label><br />
	<input type="text" id="wpps-mail-sender" name="wpps-mail-sender" class="wide" size="70" value="<?php echo esc_attr( $cf->mailfrom ); ?>" />
	</div>

	<div class="mail-field">
	<label for="wpps-mail-sender-name"><?php echo esc_html( __( 'From Name:', 'wpps' ) ); ?></label><br />
	<input type="text" id="wpps-mail-sender-name" name="wpps-mail-sender-name" class="wide" size="70" value="<?php echo esc_attr( $cf->mailfromname ); ?>" />
	</div>

	<div class="mail-field">
	<label for="wpps-mail-subject"><?php echo esc_html( __( 'Subject:', 'wpps' ) ); ?></label><br />
	<input type="text" id="wpps-mail-subject" name="wpps-mail-subject" class="wide" size="70" value="<?php echo esc_attr( $cf->mailsubject ); ?>" />
	</div>

	</td>
	<td scope="col" style="width: 50%;">

	</td>
	</tr>
	</tbody>
	</table>

<?php endif; ?>

<?php do_action_ref_array( 'wpps_admin_after_mail', array( &$cf ) ); ?>

<?php if ( wpps_admin_has_edit_cap() ) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<thead><tr><th scope="col"><?php echo esc_html( __( 'Messages', 'wpps' ) ); ?> <span id="message-fields-toggle-switch"></span></th></tr></thead>

	<tbody>
	<tr>
	<td scope="col">
	<div id="message-fields">

<?php foreach ( wpps_messages() as $key => $arr ) :
	$field_name = 'wpps-message-' . strtr( $key, '_', '-' );
?>
	<div class="message-field">
	<label for="<?php echo $field_name; ?>"><em># <?php echo esc_html( $arr['description'] ); ?></em></label><br />
	<input type="text" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" class="wide" size="70" value="<?php echo esc_attr( $cf->messages[$key] ); ?>" />
	</div>

<?php endforeach; ?>

	</div>
	</td>
	</tr>
	</tbody>
	</table>

<?php endif; ?>

<?php do_action_ref_array( 'wpps_admin_after_messages', array( &$cf ) ); ?>

<?php if ( wpps_admin_has_edit_cap() ) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<tbody>
	<tr>
	<td scope="col">
	<div class="save-prayer-schedule">
	<input type="submit" class="button button-highlighted" name="wpps-save" value="<?php echo esc_attr( __( 'Save', 'wpps' ) ); ?>" />
	</div>
	</td>
	</tr>
	</tbody>
	</table>

<?php endif; ?>

</form>

<?php endif; ?>

</div>

<div id="wpps-lang-select-modal" class="hidden">
<?php
	$available_locales = wpps_l10n();
	$default_locale = get_locale();

	if ( ! isset( $available_locales[$default_locale] ) )
		$default_locale = 'en_US';

?>
<h4><?php echo esc_html( sprintf( __( 'Use the default language (%s)', 'wpps' ), $available_locales[$default_locale] ) ); ?></h4>
<p><a href="<?php echo wpps_admin_url( array( 'prayerschedule' => 'new' ) ); ?>" class="button" /><?php echo esc_html( __( 'Add New', 'wpps' ) ); ?></a></p>

<?php unset( $available_locales[$default_locale] ); ?>
<h4><?php echo esc_html( __( 'Or', 'wpps' ) ); ?></h4>
<form action="" method="get">
<input type="hidden" name="page" value="wpps" />
<input type="hidden" name="prayerschedule" value="new" />
<select name="locale">
<option value="" selected="selected"><?php echo esc_html( __( '(select language)', 'wpps' ) ); ?></option>
<?php foreach ( $available_locales as $code => $locale ) : ?>
<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $locale ); ?></option>
<?php endforeach; ?>
</select>
<input type="submit" class="button" value="<?php echo esc_attr( __( 'Add New', 'wpps' ) ); ?>" />
</form>
</div>

<?php do_action_ref_array( 'wpps_admin_footer', array( &$cf ) ); ?>
