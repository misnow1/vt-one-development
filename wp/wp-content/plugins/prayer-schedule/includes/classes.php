<?php

class WPPS_PrayerSchedule {

	var $initial = false;

	var $id;
	var $title;

	var $start;
	var $end;
	var $period_len;
	var $location;
	var $description;
	
	var $mailfrom;
	var $mailfromname;
	var $mailto;
	var $mailsubject;
	
	var $unit_tag;

	var $responses_count = 0;
	var $scanned_form_tags;

	var $posted_data;
	var $uploaded_files;

	var $skip_mail = false;

	// Return true if this form is the same one as currently POSTed.
	function is_posted() {
		if ( ! isset( $_POST['_wpps_unit_tag'] ) || empty( $_POST['_wpps_unit_tag'] ) )
			return false;

		if ( $this->unit_tag == $_POST['_wpps_unit_tag'] )
			return true;

		return false;
	}

	function clear_post() {
		return true;	// bypass this for now
		$fes = $this->form_scan_shortcode();

		foreach ( $fes as $fe ) {
			$name = $fe['name'];

			if ( empty( $name ) )
				continue;

			if ( isset( $_POST[$name] ) )
				unset( $_POST[$name] );
		}
	}

	/* Generating Form HTML */

	function form_html() {
		global $wpdb, $wpps;
		
		/*
		 * Get the number of users signed up for each time period
		 */
		$sql = "SELECT start, COUNT(start) AS c, GROUP_CONCAT(name,'|',email SEPARATOR '||') AS n " . 
			"FROM $wpps->prayerscheduleentries WHERE schedule_id=%d GROUP BY start";
		$query = $wpdb->prepare( $sql, $this->id );

		$rows = $wpdb->get_results($query, ARRAY_A);
		$usersSignedUp = array();
		foreach ($rows as $row) {
			// copy the rows in a useful associative array
			$usersSignedUp[$row['start']] = array('count' => $row['c'], 'names' => $row['n']);
		}

		$form = '<div class="wpps" id="' . $this->unit_tag . '">';

		$url = wpps_get_request_uri();

		if ( $frag = strstr( $url, '#' ) )
			$url = substr( $url, 0, -strlen( $frag ) );

		$url .= '#' . $this->unit_tag;

		$url = apply_filters( 'wpps_form_action_url', $url );
		$enctype = apply_filters( 'wpps_form_enctype', '' );
		$class = apply_filters( 'wpps_form_class_attr', 'wpps-form' );

		$form .= '<form action="' . esc_url_raw( $url ) . '" method="post"'
			. ' class="' . esc_attr( $class ) . '"' . $enctype . '>' . "\n";
		$form .= '<div style="display: none;">' . "\n";
		$form .= '<input type="hidden" name="_wpps" value="'
			. esc_attr( $this->id ) . '" />' . "\n";
		$form .= '<input type="hidden" name="_wpps_version" value="'
			. esc_attr( WPPS_VERSION ) . '" />' . "\n";
		$form .= '<input type="hidden" name="_wpps_unit_tag" value="'
			. esc_attr( $this->unit_tag ) . '" />' . "\n";
		$form .= '</div>' . "\n";
		
		/* 
		 * Render some useful information about the event
		 */
		$form .= "<p>Your Name: <input type=\"text\" len=\"40\" name=\"wpps-name\"/></p>";
		$form .= "<p>Your E-mail Address: <input type=\"text\" len=\"40\" name=\"wpps-mail\"/></p>";
		
		/* 
		 * Render the prayer schedule here
		 */
		$form .= "<table>\n";
		
		$tStart = strtotime($this->start);
		$tEnd = strtotime($this->end);
		$tPeriodLen = $this->period_len * 60;
		
		if ($tStart <= $tEnd) {
			// start and end times make sense, so render the list of times
			$curDay = '';
			$prevDay = '';
			for ($curTS = $tStart; $curTS < $tEnd; $curTS += $tPeriodLen) {
				$curDay = date('D', $curTS);
				if ($curDay != $prevDay) {
					$prevDay = $curDay;
					$form .= "<tr><th colspan=2><strong>" . date('l, F j, Y', $curTS) . "</strong></th></tr>\n";
				}
				
				if ($curTS + $tPeriodLen <= $tEnd) {
					// the current time plus period length is within the end time so use cur+period
					$tActualEnd = $curTS + $tPeriodLen;
				}
				else {
					// this is the last period, ending within the cur+period so use the actual end time
					$tActualEnd = $tEnd;
				}
				
				$pdStart = date('g:i A', $curTS);
				$pdEnd = date('g:i A', $tActualEnd);
				
				$cbName = 'wpps-period[' . date('Y-m-d_H:i', $curTS) . '_' . date('Y-m-d_H:i', $tActualEnd) . ']';
				$form .= "<tr><td width=\"30%\"><div align=\"right\">$pdStart - $pdEnd</div></td><td><input type=\"checkbox\" name=\"$cbName\"/>";
				
				$sqlDate = date('Y-m-d H:i:s', $curTS);
				if (isset($usersSignedUp[$sqlDate])) {
					$numUsers = $usersSignedUp[$sqlDate]['count'];
					if (is_user_logged_in()) {
						// user is logged in, display names/e-mail addresses of people in that slot
						// user names are in format name|mail||name|mail||name|mail, so explode these things
						$users = $usersSignedUp[$sqlDate]['names'];
						
						$userArr = explode('||', $users);
						$userStrArr = array();
						foreach ($userArr as $userWithAddr) {
							$user2 = explode('|', $userWithAddr);
							$userStr = $user2[0] . ' &lt;<a href="mailto:' . $user2[1] . '">' . $user2[1] . '</a>&gt;';
							$userStrArr[] = $userStr;
						}
						
						$form .= ' Signed up: ' . implode(', ', $userStrArr);
					}
					else {
						$form .= ' ' . $numUsers . ' ' . ($numUsers == 1 ? "person has" : "people have") . " signed up for this time.";
					}
				}
				$form .= "</td></tr>\n";
			}
		}
		
		$form .= "</table>";

		if ( ! $this->responses_count )
			$form .= $this->form_response_output();

		$html = '<input type="submit" value="Send" class="wpps-submit"/>';

		if ( wpcf7_script_is() ) {
			$src = apply_filters( 'wpcf7_ajax_loader', wpcf7_plugin_url( 'images/ajax-loader.gif' ) );
			$html .= '<img class="ajax-loader" style="visibility: hidden;" alt="' . esc_attr( __( 'Sending ...', 'wpcf7' ) ) . '" src="' . esc_url_raw( $src ) . '" />';
		}
			
		$form .= "\n<p>$html</p>\n";
		$form .= "</form>\n";

		$form .= "</div>\n";

		return $form;
	}

	function form_response_output() {
		$class = 'wpps-response-output';
		$content = '';

		if ( $this->is_posted() ) { // Post response output for non-AJAX
			if ( isset( $_POST['_wpps_mail_sent'] ) && $_POST['_wpps_mail_sent']['id'] == $this->id ) {
				if ( $_POST['_wpps_mail_sent']['ok'] ) {
					$class .= ' wpps-mail-sent-ok';
					$content = $_POST['_wpps_mail_sent']['message'];
				} else {
					$class .= ' wpps-mail-sent-ng';
					if ( $_POST['_wpps_mail_sent']['spam'] )
						$class .= ' wpps-spam-blocked';
					$content = $_POST['_wpps_mail_sent']['message'];
				}
			} elseif ( isset( $_POST['_wpps_validation_errors'] ) && $_POST['_wpps_validation_errors']['id'] == $this->id ) {
				$class .= ' wpps-validation-errors';
				$content = $this->message( 'validation_error' );
			}
		} else {
			$class .= ' wpps-display-none';
		}

		$class = ' class="' . $class . '"';

		return '<div' . $class . '>' . $content . '</div>';
	}

	function validation_error( $name ) {
		return '';	// bypass this
		
		if ( ! $this->is_posted() )
			return '';

		if ( ! isset( $_POST['_wpps_validation_errors'] ) )
			return '';

		if ( $ve = trim( $_POST['_wpps_validation_errors']['messages'][$name] ) ) {
			$ve = '<span class="wpps-not-valid-tip-no-ajax">' . esc_html( $ve ) . '</span>';
			return apply_filters( 'wpps_validation_error', $ve, $name, $this );
		}

		return '';
	}

	/* Validate */

	function validate() {
		//$fes = $this->form_scan_shortcode();

		$result = array( 'valid' => true, 'reason' => array() );
		return $result;	// bypass
		
		foreach ( $fes as $fe ) {
			$result = apply_filters( 'wpps_validate_' . $fe['type'], $result, $fe );
		}

		return $result;
	}

	/* Acceptance */

	function accepted() {
		$accepted = true;

		return apply_filters( 'wpps_acceptance', $accepted );
	}

	/* Mail */

	function mail($name, $mail, $periods) {

		do_action_ref_array( 'wpps_before_send_mail', array( &$this ) );

		if ($this->compose_and_send_client($name, $mail, $periods)) {
			return true;
		}

		return false;
	}

	/*
	 * Function to send mail to the person who signed up
	 */
	function compose_and_send_client($name, $mail, $periods = '') {
		$regex = '/\[\s*([a-zA-Z_][0-9a-zA-Z:._-]*)\s*\]/';

		$callback = array( &$this, 'mail_callback' );
		
		if (empty($name) || empty($mail)) return false;	// this function requires a recipient
		
		$subject = preg_replace_callback( $regex, $callback, $this->mailsubject );
		$sender = preg_replace_callback( $regex, $callback, $this->mailfromname . ' <' . $this->mailfrom . '>');
		$recipient = preg_replace_callback( $regex, $callback, $name . ' <' . $mail . '>' );
		$recipientcc = preg_replace_callback( $regex, $callback, $this->mailfromname . ' <' . $this->mailfrom . '>');
		
		$body = "$name,\r\n\r\n";
		$body .= "Thanks for signing up for " . $this->title . "!\r\n\r\n";
		$body .= "You're signed up for the following times:\r\n";
		foreach ($periods as $period => $val) {
			// this is just the HTML posted data
			if ($val != 'on') continue;	// this should never happen
			$matches = array();
			if (preg_match('/([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9])_([0-9][0-9]:[0-9][0-9])_([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9])_([0-9][0-9]:[0-9][0-9])/', $period, $matches)) {
				$dayStart = $matches[1];
				$timeStart = $matches[2];
				$dayEnd = $matches[3];
				$timeEnd = $matches[4];
				
				// convert the dates to something a normal human can read
				$dayStartHR = date('l F n, Y', strtotime($dayStart));
				$timeStartHR = date('h:i A', strtotime($timeStart));
				$timeEndHR = date('h:i A', strtotime($timeEnd));
				
				$body .= "$dayStartHR: $timeStartHR - $timeEndHR\r\n";
			}
		}
		$body .= "\r\n";
		if ($this->description) {
			$body .= "Here's a brief description of this event:\r\n";
			$body .= $this->description;
			$body .= "\r\n\r\n";
		}
		if ($this->location) {
			$body .= "This is where you should go:\r\n";
			$body .= $this->location;
			$body .= "\r\n\r\n";
		}
		
		$body .= "If you have any questions, please contact us by replying to this e-mail.\r\n";
		$body .= "----\r\n" . $this->mailfromname;
		
		$body = preg_replace_callback( $regex, $callback, $body );

		extract( apply_filters( 'wpps_mail_components',
			compact( 'subject', 'sender', 'body', 'recipient', 'additional_headers' ) ) );

		$headers = "From: $sender\r\n";
		$headers .= "CC: $recipientcc\r\n";

		return @wp_mail( $recipient, $subject, $body, $headers );
	}

	function mail_callback_html( $matches ) {
		return $this->mail_callback( $matches, true );
	}

	function mail_callback( $matches, $html = false ) {
		if ( isset( $this->posted_data[$matches[1]] ) ) {
			$submitted = $this->posted_data[$matches[1]];

			if ( is_array( $submitted ) )
				$replaced = join( ', ', $submitted );
			else
				$replaced = $submitted;

			if ( $html ) {
				$replaced = strip_tags( $replaced );
				$replaced = wptexturize( $replaced );
			}

			$replaced = apply_filters( 'wpps_mail_tag_replaced', $replaced, $submitted );

			return stripslashes( $replaced );
		}

		if ( $special = apply_filters( 'wpps_special_mail_tags', '', $matches[1] ) )
			return $special;

		return $matches[0];
	}

	/* Message */

	function message( $status ) {
		$messages = $this->messages;
		$message = $messages[$status];

		return apply_filters( 'wpps_display_message', $message );
	}

	/* Upgrade */

	function upgrade() {
		if ( ! isset( $this->mail['recipient'] ) )
			$this->mail['recipient'] = get_option( 'admin_email' );


		if ( ! is_array( $this->messages ) )
			$this->messages = array();


		foreach ( wpps_messages() as $key => $arr ) {
			if ( ! isset( $this->messages[$key] ) )
				$this->messages[$key] = $arr['default'];
		}
	}

	/* Save */

	function save() {
		global $wpdb, $wpps;

		$fields = array(
			'title' => maybe_serialize( stripslashes_deep( $this->title ) ),
			'start' => maybe_serialize( stripslashes_deep( $this->start ) ),
			'end' => maybe_serialize( stripslashes_deep( $this->end ) ),
			'period_len' => maybe_serialize( stripslashes_deep( $this->period_len ) ),
			'location' => maybe_serialize( stripslashes_deep( $this->location ) ),
			'description' => maybe_serialize( stripslashes_deep( $this->description ) ),
			'mailfrom' => maybe_serialize( stripslashes_deep( $this->mailfrom ) ),
			'mailfromname' => maybe_serialize( stripslashes_deep( $this->mailfromname ) ),
			'mailsubject' => maybe_serialize( stripslashes_deep( $this->mailsubject ) )
		);

		if ( $this->initial ) {
			$result = $wpdb->insert( $wpps->prayerschedules, $fields );
			
			if ( $result ) {
				$this->initial = false;
				$this->id = $wpdb->insert_id;

				do_action_ref_array( 'wpps_after_create', array( &$this ) );
			} else {
				return false; // Failed to save
			}

		} else { // Update
			if ( ! (int) $this->id )
				return false; // Missing ID

			$result = $wpdb->update( $wpps->prayerschedules, $fields,
				array( 'id' => absint( $this->id ) ) );

			if ( false !== $result ) {
				do_action_ref_array( 'wpps_after_update', array( &$this ) );
			} else {
				return false; // Failed to save
			}
		}

		do_action_ref_array( 'wpps_after_save', array( &$this ) );
		return true; // Succeeded to save
	}

	function copy() {
		$new = new WPPS_PrayerSchedule();
		$new->initial = true;

		$new->title = $this->title . '_copy';
		$new->form = $this->form;
		$new->mail = $this->mail;
		$new->mail_2 = $this->mail_2;
		$new->messages = $this->messages;
		$new->additional_settings = $this->additional_settings;

		return $new;
	}

	function delete() {
		global $wpdb, $wpps;

		if ( $this->initial )
			return;

		$query = $wpdb->prepare(
			"DELETE FROM $wpps->prayerschedules WHERE cf7_unit_id = %d LIMIT 1",
			absint( $this->id ) );

		$wpdb->query( $query );

		$this->initial = true;
		$this->id = null;
	}
	
	/*
	 * Save an entry submitted online
	 */
	function save_entry ($name, $mail, $periods) {
		global $wpdb, $wpps;
		
		if (!empty($name) && !empty($mail) && is_array($periods)) {
			foreach ($periods as $period => $val) {
				if ($val != 'on') continue;	// this should never happen
				$matches = array();
				if (preg_match('/([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]_[0-9][0-9]:[0-9][0-9])_([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]_[0-9][0-9]:[0-9][0-9])/', $period, $matches)) {
					$periodStart = preg_replace('/_/', ' ', $matches[1]);
					$periodEnd = preg_replace('/_/', ' ', $matches[2]);
			
					$entry = array('schedule_id' => $this->id,
						'start' => $periodStart,
						'end' => $periodEnd,
						'name' => $name,
						'email' => $mail);
			
					$wpdb->insert($wpps->prayerscheduleentries, $entry);
				}
			}
		}
	}
}

function wpps_prayer_schedule( $id ) {
	global $wpdb, $wpps;

	//echo "Loading prayer schedule id $id<br/>";
	
	$query = $wpdb->prepare( "SELECT * FROM $wpps->prayerschedules WHERE id = %d", $id );

	if ( ! $row = $wpdb->get_row( $query ) )
		return false; // No data

	$prayer_schedule = new WPPS_PrayerSchedule();
	$prayer_schedule->id = $row->id;
	$prayer_schedule->title = maybe_unserialize( $row->title );
	$prayer_schedule->start = maybe_unserialize( $row->start );
	$prayer_schedule->end = maybe_unserialize( $row->end );
	$prayer_schedule->period_len = maybe_unserialize( $row->period_len );
	$prayer_schedule->location = maybe_unserialize( $row->location );
	$prayer_schedule->description = maybe_unserialize( $row->description );
	
	$prayer_schedule->mailfrom = $row->mailfrom;
	$prayer_schedule->mailfromname = $row->mailfromname;
	$prayer_schedule->mailto = $row->mailto;
	$prayer_schedule->mailsubject = $row->mailsubject;
	
	$prayer_schedule->upgrade();

	return $prayer_schedule;
}

function wpps_prayer_schedule_default_pack( $locale = null ) {
	global $l10n;

	if ( $locale && $locale != get_locale() ) {
		$mo_orig = $l10n['wpps'];
		unset( $l10n['wpps'] );

		if ( 'en_US' != $locale ) {
			$mofile = wpps_plugin_path( 'languages/wpps-' . $locale . '.mo' );
			if ( ! load_textdomain( 'wpps', $mofile ) ) {
				$l10n['wpps'] = $mo_orig;
				unset( $mo_orig );
			}
		}
	}

	$prayer_schedule = new WPPS_PrayerSchedule();
	$prayer_schedule->initial = true;

	$prayer_schedule->title = __( 'Untitled', 'wpps' );
	$prayer_schedule->start = 0;
	$prayer_schedule->end = 0;
	$prayer_schedule->period_len = 30;
	$prayer_schedule->location = __('Everywhere', 'wpps');
	$prayer_schedule->description = __('Prayer Event', 'wpps');

	if ( isset( $mo_orig ) )
		$l10n['wpps'] = $mo_orig;

	return $prayer_schedule;
}

function wpps_get_current_prayer_schedule() {
	global $wpps_prayer_schedule;

	if ( ! is_a( $wpps_prayer_schedule, 'WPPS_PrayerSchedule' ) )
		return null;

	return $wpps_prayer_schedule;
}

function wpps_is_posted() {
	if ( ! $prayer_schedule = wpps_get_current_prayer_schedule() )
		return false;

	return $prayer_schedule->is_posted();
}

function wpps_get_validation_error( $name ) {
	if ( ! $prayer_schedule = wpps_get_current_prayer_schedule() )
		return '';

	return $prayer_schedule->validation_error( $name );
}

function wpps_get_message( $status ) {
	if ( ! $prayer_schedule = wpps_get_current_prayer_schedule() )
		return '';

	return $prayer_schedule->message( $status );
}

function wpps_scan_shortcode( $cond = null ) {
	if ( ! $prayer_schedule = wpps_get_current_prayer_schedule() )
		return null;

	return $prayer_schedule->form_scan_shortcode( $cond );
}

?>
