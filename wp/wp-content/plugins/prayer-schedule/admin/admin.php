<?php

function wpps_admin_has_edit_cap() {
	return current_user_can( WPPS_ADMIN_READ_WRITE_CAPABILITY );
}

add_action( 'admin_menu', 'wpps_admin_add_pages', 9 );

function wpps_admin_add_pages() {

	if ( isset( $_POST['wpps-save'] ) && wpps_admin_has_edit_cap() ) {
		/*
		 * Save the submitted form
		 */
		$id = $_POST['wpps-id'];
		check_admin_referer( 'wpps-save_' . $id );

		if ( ! $prayer_schedule = wpps_prayer_schedule( $id ) ) {
			$prayer_schedule = new WPPS_PrayerSchedule();
			$prayer_schedule->initial = true;
		}

		$title = trim( $_POST['wpps-title'] );
		$start = trim( $_POST['wpps-start'] );
		$end = trim( $_POST['wpps-end'] );
		$period_len = trim( $_POST['wpps-period_len'] );
		$location = trim( $_POST['wpps-location'] );
		$description = trim( $_POST['wpps-description'] );
		
		$mailto = trim($_POST['wpps-mail-recipient']);
		$mailfrom = trim($_POST['wpps-mail-sender']);
		$mailfromname = trim($_POST['wpps-mail-sender-name']);
		$mailsubject = trim($_POST['wpps-mail-subject']);
		
		$messages = $prayer_schedule->messages;
		foreach ( wpps_messages() as $key => $arr ) {
			$field_name = 'wpps-message-' . strtr( $key, '_', '-' );
			if ( isset( $_POST[$field_name] ) )
				$messages[$key] = trim( $_POST[$field_name] );
		}

		$query = array();
		$query['message'] = ( $prayer_schedule->initial ) ? 'created' : 'saved';

		$prayer_schedule->title = $title;
		$prayer_schedule->start = $start;
		$prayer_schedule->end = $end;
		$prayer_schedule->period_len = $period_len;
		$prayer_schedule->location = $location;
		$prayer_schedule->description = $description;
		$prayer_schedule->mailto = $mailto;
		$prayer_schedule->mailfrom = $mailfrom;
		$prayer_schedule->mailfromname = $mailfromname;
		$prayer_schedule->mailsubject = $mailsubject;
		//$prayer_schedule->messages = $messages;

		$prayer_schedule->save();

		$query['prayerschedule'] = $prayer_schedule->id;
		$redirect_to = wpps_admin_url( $query );
		wp_redirect( $redirect_to );
		exit();
	} elseif ( isset( $_POST['wpps-copy'] ) && wpps_admin_has_edit_cap() ) {
		$id = $_POST['wpps-id'];
		check_admin_referer( 'wpps-copy_' . $id );

		$query = array();

		if ( $prayer_schedule = wpps_prayer_schedule( $id ) ) {
			$new_prayer_schedule = $prayer_schedule->copy();
			$new_prayer_schedule->save();

			$query['contactform'] = $new_prayer_schedule->id;
			$query['message'] = 'created';
		} else {
			$query['contactform'] = $prayer_schedule->id;
		}

		$redirect_to = wpps_admin_url( $query );
		wp_redirect( $redirect_to );
		exit();
	} elseif ( isset( $_POST['wpps-delete'] ) && wpps_admin_has_edit_cap() ) {
		$id = $_POST['wpps-id'];
		check_admin_referer( 'wpps-delete_' . $id );

		if ( $prayer_schedule = wpps_prayer_schedule( $id ) )
			$prayer_schedule->delete();

		$redirect_to = wpps_admin_url( array( 'message' => 'deleted' ) );
		wp_redirect( $redirect_to );
		exit();
	} elseif ( isset( $_GET['wpps-create-table'] ) ) {
		check_admin_referer( 'wpps-create-table' );

		$query = array();

		if ( ! wpps_table_exists() && current_user_can( 'activate_plugins' ) ) {
			wpps_install();
			if ( wpps_table_exists() ) {
				$query['message'] = 'table_created';
			} else {
				$query['message'] = 'table_not_created';
			}
		}

		wp_redirect( wpps_admin_url( $query ) );
		exit();
	}

	add_menu_page( __( 'Prayer Schedule', 'wpps' ), __( 'Prayer Schedules', 'wpps' ),
		WPPS_ADMIN_READ_CAPABILITY, 'wpps', 'wpps_admin_management_page' );
}

add_action( 'admin_footer', 'wpps_admin_footer' );

function wpps_admin_footer() {
	global $plugin_page;

	if ( ! isset( $plugin_page ) || 'wpps' != $plugin_page )
		return;

?>
<script type="text/javascript">
/* <![CDATA[ */
var _wpps = {
	pluginUrl: '<?php echo wpps_plugin_url(); ?>',
	tagGenerators: {
<?php wpps_print_tag_generators(); ?>
	}
};
/* ]]> */
</script>
<?php
}

function wpps_admin_management_page() {
	$prayer_schedules = wpps_prayer_schedules();

	$unsaved = false;

	if (!isset( $_GET['prayerschedule'])) {
		$_GET['prayerschedule'] = '';
	}
	
	if ( 'new' == $_GET['prayerschedule'] ) {
		$unsaved = true;
		$current = -1;
		$cf = wpps_prayer_schedule_default_pack( isset( $_GET['locale'] ) ? $_GET['locale'] : '' );
	} elseif ( $cf = wpps_prayer_schedule( $_GET['prayerschedule'] ) ) {
		$current = (int) $_GET['prayerschedule'];
	} else {
		$first = reset( $prayer_schedules ); // Returns first item
		$current = $first->id;
		$cf = wpps_prayer_schedule( $current );
	}
	
	
	require_once WPPS_PLUGIN_DIR . '/admin/edit.php';
}

/* Install and default settings */

add_action( 'activate_' . WPPS_PLUGIN_BASENAME, 'wpps_install' );

function wpps_install() {
	global $wpdb, $wpps;

	if ( wpps_table_exists() )
		return; // Exists already

	$charset_collate = '';
	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty( $wpdb->collate ) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}

	$wpdb->query( "CREATE TABLE IF NOT EXISTS $wpps->contactforms (
		cf7_unit_id bigint(20) unsigned NOT NULL auto_increment,
		title varchar(200) NOT NULL default '',
		form text NOT NULL,
		mail text NOT NULL,
		mail_2 text NOT NULL,
		messages text NOT NULL,
		additional_settings text NOT NULL,
		PRIMARY KEY (cf7_unit_id)) $charset_collate;" );

	if ( ! wpps_table_exists() )
		return false; // Failed to create

	$legacy_data = get_option( 'wpps' );
	if ( is_array( $legacy_data )
		&& is_array( $legacy_data['prayer_schedules'] ) && $legacy_data['prayer_schedules'] ) {
		foreach ( $legacy_data['prayer_schedules'] as $key => $value ) {
			$wpdb->insert( $wpps->contactforms, array(
				'cf7_unit_id' => $key,
				'title' => $value['title'],
				'form' => maybe_serialize( $value['form'] ),
				'mail' => maybe_serialize( $value['mail'] ),
				'mail_2' => maybe_serialize( $value['mail_2'] ),
				'messages' => maybe_serialize( $value['messages'] ),
				'additional_settings' => maybe_serialize( $value['additional_settings'] )
				), array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' ) );
		}
	} else {
		wpps_load_plugin_textdomain();

		$wpdb->insert( $wpps->contactforms, array(
			'title' => __( 'Prayer schedule', 'wpps' ) . ' 1',
			'form' => maybe_serialize( wpps_default_form_template() ),
			'mail' => maybe_serialize( wpps_default_mail_template() ),
			'mail_2' => maybe_serialize ( wpps_default_mail_2_template() ),
			'messages' => maybe_serialize( wpps_default_messages_template() ) ) );
	}
}

/* Misc */

add_filter( 'plugin_action_links', 'wpps_plugin_action_links', 10, 2 );

function wpps_plugin_action_links( $links, $file ) {
	if ( $file != WPPS_PLUGIN_BASENAME )
		return $links;

	$url = wpps_admin_url( array( 'page' => 'wpps' ) );

	$settings_link = '<a href="' . esc_attr( $url ) . '">'
		. esc_html( __( 'Settings', 'wpps' ) ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}

add_action( 'wpps_admin_before_subsubsub', 'wpps_updated_message' );

function wpps_updated_message( &$prayer_schedule ) {
	if ( ! isset( $_GET['message'] ) )
		return;

	switch ( $_GET['message'] ) {
		case 'created':
			$updated_message = __( "Prayer schedule created.", 'wpps' );
			break;
		case 'saved':
			$updated_message = __( "Prayer schedule saved.", 'wpps' );
			break;
		case 'deleted':
			$updated_message = __( "Prayer schedule deleted.", 'wpps' );
			break;
		case 'table_created':
			$updated_message = __( "Database table created.", 'wpps' );
			break;
		case 'table_not_created':
			$updated_message = __( "Failed to create database table.", 'wpps' );
			break;
	}

	if ( ! $updated_message )
		return;

?>
<div id="message" class="updated fade"><p><?php echo esc_html( $updated_message ); ?></p></div>
<?php
}

?>
