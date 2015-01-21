<?php

add_action( 'init', 'wpps_init_switch', 11 );

function wpps_init_switch() {
	if ( 'GET' == $_SERVER['REQUEST_METHOD'] && isset( $_GET['_wpps_is_ajax_call'] ) ) {
		wpps_ajax_onload();
		exit();
	} elseif ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['_wpps_is_ajax_call'] ) ) {
		wpps_ajax_json_echo();
		exit();
	} elseif ( isset( $_POST['_wpps'] ) ) {
		wpps_process_nonajax_submitting();
	}
}

function wpps_ajax_onload() {
	global $wpps_prayer_schedule;

	$echo = '';

	if ( isset( $_GET['_wpps'] ) ) {
		$id = (int) $_GET['_wpps'];

		if ( $wpps_prayer_schedule = wpps_prayer_schedule( $id ) ) {
			$items = apply_filters( 'wpps_ajax_onload', array() );
			$wpps_prayer_schedule = null;
		}
	}

	$echo = json_encode( $items );

	if ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo $echo;
	}
}

function wpps_ajax_json_echo() {
	global $wpps_prayer_schedule;

	$echo = '';

	/*
	 * In general, this is where submissions are handled
	 */
	
	if ( isset( $_POST['_wpps'] ) ) {
		$id = (int) $_POST['_wpps'];
		$unit_tag = $_POST['_wpps_unit_tag'];

		if ( $wpps_prayer_schedule = wpps_prayer_schedule( $id ) ) {
			$validation = $wpps_prayer_schedule->validate();

			$items = array(
				'mailSent' => false,
				'into' => '#' . $unit_tag,
				'captcha' => null );

			$items = apply_filters( 'wpps_ajax_json_echo', $items );

			if ( ! $validation['valid'] ) { // Validation error occured
				$invalids = array();
				foreach ( $validation['reason'] as $name => $reason ) {
					$invalids[] = array(
						'into' => 'span.wpps-form-control-wrap.' . $name,
						'message' => $reason );
				}

				$items['message'] = wpps_get_message( 'validation_error' );
				$items['invalids'] = $invalids;

			} 
			else {
				// well, we passed validation.  
				
				/*
				 * Snow - add the entry to the database
				 */
				$name = trim($_POST['wpps-name']);
				$mail = trim($_POST['wpps-mail']);
				if (isset($_POST['wpps-period'])) {
					$periods = $_POST['wpps-period'];
				}
				else {
					$periods = array();
				}
				$wpps_prayer_schedule->save_entry($name, $mail, $periods);

				/*
				 * Send the notification messages
				 */
				if ( $wpps_prayer_schedule->mail($name, $mail, $periods) ) {
					$items['mailSent'] = true;
					$items['message'] = wpps_get_message( 'mail_sent_ok' );
	
					do_action_ref_array( 'wpps_mail_sent', array( &$wpps_prayer_schedule ) );
				} 
				else {
					$items['message'] = wpps_get_message( 'mail_sent_ng' );
				}
			}
			
			$wpps_prayer_schedule = null;
		}
	}

	$echo = json_encode( $items );

	if ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo $echo;
	} else {
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		echo '<textarea>' . $echo . '</textarea>';
	}
}

function wpps_process_nonajax_submitting() {
	global $wpps_prayer_schedule, $wpdb, $wpps;

	/*
	 * In general, this is where submissions are handled
	 */
	
	if ( ! isset($_POST['_wpps'] ) )
		return;

	$id = (int) $_POST['_wpps'];

	if ( $wpps_prayer_schedule = wpps_prayer_schedule( $id ) ) {
		$validation = $wpps_prayer_schedule->validate();

		if ( ! $validation['valid'] ) {
			$_POST['_wpps_validation_errors'] = array( 'id' => $id, 'messages' => $validation['reason'] );
		} 
		elseif ( ! $wpps_prayer_schedule->accepted() ) { // Not accepted terms
			$_POST['_wpps_mail_sent'] = array( 'id' => $id, 'ok' => false, 'message' => wpps_get_message( 'accept_terms' ) );
		} 
		elseif ( $wpps_prayer_schedule->mail() ) {
			$_POST['_wpps_mail_sent'] = array( 'id' => $id, 'ok' => true, 'message' => wpps_get_message( 'mail_sent_ok' ) );

			do_action_ref_array( 'wpps_mail_sent', array( &$wpps_prayer_schedule ) );

			$wpps_prayer_schedule->clear_post();
		} 
		else {
			$_POST['_wpps_mail_sent'] = array( 'id' => $id, 'ok' => false, 'message' => wpps_get_message( 'mail_sent_ng' ) );
		}

		// remove upload files
		foreach ( (array) $wpps_prayer_schedule->uploaded_files as $name => $path ) {
			@unlink( $path );
		}

		// place this into the database
		$entries = array();
		$name = trim($_POST['wpps-name']);
		$mail = trim($_POST['wpps-mail']);
		$periods = $_POST['wpps-period'];
		if (is_array($periods)) {
			foreach ($periods as $period => $val) {
				if ($val != 'on') continue;	// this should never happen
				$matches = array();
				if (preg_match('/([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]_[0-9][0-9]:[0-9][0-9])_([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]_[0-9][0-9]:[0-9][0-9])/', $period, $matches)) {
					$periodStart = preg_replace('/_/', ' ', $matches[1]);
					$periodEnd = preg_replace('/_/', ' ', $matches[2]);
				}
			
				$entry = array('start' => $periodStart,
					'end' => $periodEnd,
					'name' => $name,
					'email' => $mail);
				
				$wpdb->insert($wpps->prayerscheduleentries, $entry);
			}
		}
		
		$wpps_prayer_schedule = null;
	}
}

add_action( 'the_post', 'wpps_the_post' );

function wpps_the_post() {
	global $wpps;

	$wpps->processing_within = 'p' . get_the_ID();
	$wpps->unit_count = 0;
}

add_action( 'loop_end', 'wpps_loop_end' );

function wpps_loop_end() {
	global $wpps;

	$wpps->processing_within = '';
}

add_filter( 'widget_text', 'wpps_widget_text_filter', 9 );

function wpps_widget_text_filter( $content ) {
	global $wpps;

	$wpps->widget_count += 1;
	$wpps->processing_within = 'w' . $wpps->widget_count;
	$wpps->unit_count = 0;

	$regex = '/\[\s*prayer-schedule\s+(\d+(?:\s+.*)?)\]/';
	$content = preg_replace_callback( $regex, 'wpps_widget_text_filter_callback', $content );

	$wpps->processing_within = '';
	return $content;
}

function wpps_widget_text_filter_callback( $matches ) {
	return do_shortcode( $matches[0] );
}

add_shortcode( 'prayer-schedule', 'wpps_prayer_schedule_tag_func' );

function wpps_prayer_schedule_tag_func( $atts ) {
	/*
	 * Render the prayer schedule
	 */
	global $wpps, $wpps_prayer_schedule;

	if ( is_feed() )
		return '[prayer-schedule]';

	if ( is_string( $atts ) )
		$atts = explode( ' ', $atts, 2 );

	$atts = (array) $atts;

	$id = (int) array_shift( $atts );

	if ( ! ( $wpps_prayer_schedule = wpps_prayer_schedule( $id ) ) )
		return '[prayer-schedule 404 "Not Found"]';

	if ( $wpps->processing_within ) { // Inside post content or text widget
		$wpps->unit_count += 1;
		$unit_count = $wpps->unit_count;
		$processing_within = $wpps->processing_within;

	} else { // Inside template

		if ( ! isset( $wpps->global_unit_count ) )
			$wpps->global_unit_count = 0;

		$wpps->global_unit_count += 1;
		$unit_count = 1;
		$processing_within = 't' . $wpps->global_unit_count;
	}

	$unit_tag = 'wpps-f' . $id . '-' . $processing_within . '-o' . $unit_count;
	$wpps_prayer_schedule->unit_tag = $unit_tag;

	if (!isset($_POST['_wpps_mail_sent'])) {
		$form = $wpps_prayer_schedule->form_html();
	}
	else {
		$form = "<p>Thanks for signing up!</p>";
	}

	$wpps_prayer_schedule = null;

	return $form;
}

add_action( 'wp_head', 'wpps_head' );

function wpps_head() {
	// Cached?
	if ( wpps_script_is() && defined( 'WP_CACHE' ) && WP_CACHE ) :
?>
<script type="text/javascript">
//<![CDATA[
var _wpps = { cached: 1 };
//]]>
</script>
<?php
	endif;
}

if ( WPPS_LOAD_JS )
	add_action( 'wp_print_scripts', 'wpps_enqueue_scripts' );

function wpps_enqueue_scripts() {
	// jquery.form.js originally bundled with WordPress is out of date and deprecated
	// so we need to deregister it and re-register the latest one
	wp_deregister_script( 'jquery-form' );
	wp_register_script( 'jquery-form', wpps_plugin_url( 'jquery.form.js' ),
		array( 'jquery' ), '2.47', true );

	$in_footer = true;
	if ( 'header' === WPPS_LOAD_JS )
		$in_footer = false;

	wp_enqueue_script( 'prayer-schedule', wpps_plugin_url( 'scripts.js' ),
		array( 'jquery', 'jquery-form' ), WPPS_VERSION, $in_footer );

	do_action( 'wpps_enqueue_scripts' );
}

function wpps_script_is() {
	return wp_script_is( 'prayer-schedule' );
}

function wpps_style_is() {
	return wp_style_is( 'prayer-schedule' );
}

?>
