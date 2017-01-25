<?php

require_once WPUS_PLUGIN_DIR . '/includes/bcrypt.php';
$bcrypt = new Bcrypt(15);

class WPUS_User {
	var $id;
	var $email;
	var $password;
	var $realname;

	var $projects;
}

class WPUS_User_Project {
	var $id;
	var $user_id;
	var $project_id;
}

function wpus_user($id) {
	global $wpdb, $wpus;

	$query = $wpdb->prepare( "SELECT * FROM $wpus->users WHERE id = %d", $id );

	if ( ! $row = $wpdb->get_row( $query ) )
		return false; // No data

	$unique_downloader_project_user = new WPUS_User();
	$unique_downloader_project_user->id = $row->id;
	$unique_downloader_project_user->email = $row->email;
	$unique_downloader_project_user->password = $row->password;
	$unique_downloader_project_user->realname = $row->realname;

	return $unique_downloader_project_user;
}

function wpus_user_by_email($email) {
	global $wpdb, $wpus;

	//echo "<p>In wpus_user_by_email: searching for $email.</p>\n";

	$query = $wpdb->prepare( "SELECT * FROM $wpus->users WHERE email = %s", $email );

	if ( ! $row = $wpdb->get_row( $query ) ) {
		//echo "<p>In wpus_user_by_email: no user for $email.</p>\n";
		return false; // No data
	}

	$unique_downloader_project_user = new WPUS_User();
	$unique_downloader_project_user->id = $row->id;
	$unique_downloader_project_user->email = $row->email;
	$unique_downloader_project_user->password = $row->password;
	$unique_downloader_project_user->realname = $row->realname;

	return $unique_downloader_project_user;
}

function wpus_check_session () {
	global $wpdb, $wpus;

	// start the session, really
	if (!session_id()) {
		session_start();
	}

	//echo "<p>Working in session " . session_id() . "</p>\n";

	if (!isset($_SESSION['wpus_email'])) {
		return;	// session variable for user e-mail isn't set, so just return
	}

	if (!empty($_SESSION['wpus_email'])) {
		// load the user indicated by the e-mail address in the session
		$wpus->user = wpus_user_by_email($_SESSION['wpus_email']);

		if ($wpus->user === false) {
			// unable to load the user, so clean up the session and redirect
			unset($_SESSION['wpus_email']);

			wp_redirect(wpus_redirect_url());
			exit();
		}
	}
}

function wpus_destroy_session () {
	global $wpdb, $wpus;

	// kill the username in the session
	unset($_SESSION['wpus-user']);
	$wpus->user = false;
}

function wpus_verify_user ($email, $password) {
	global $bcrypt, $wpus;

	//echo "<p>In wpus_verify_user() with $email and $password</p>\n";

	$user = wpus_user_by_email($email);

	// no record exists for this e-mail address
	if (!$user) {
		$wpus->LogEvent("wpus_verify_user: No record for $email.", WPUS_LOG_BAD_LOGIN);
		return false;
	}

	// check the posted password against the salt and hash
	if ($bcrypt->verify($password, $user->password)) {
		$wpus->LogEvent("wpus_verify_user: Verified password for $email.", WPUS_LOG_USER);

		return $user;
	}
	else {
		$wpus->LogEvent("wpus_verify_user: Bad password for $email.", WPUS_LOG_BAD_LOGIN);
		return false;
	}
}

function wpus_create_user ($realname, $email, $password) {
	global $bcrypt, $wpus, $wpdb;

	//echo "<p>In wpus_create_user: creating account for $email.</p>\n";

	if (empty($email) || empty($password)) {
		return WPUS_USER_BAD_FORMAT;	// e-mail address or password was blank, return failedure
	}

	// try loading a user by the e-mail address.  if this returns something,
	// bail out
	$user = wpus_user_by_email($email);
	if ($user) return WPUS_USER_ALREADY_EXISTS;

	$user = new WPUS_User();

	$user->email = $email;
	$user->password = $bcrypt->hash($password);
	$user->realname = $realname;

	$fields = array(
		'email' => $user->email,
		'password' => $user->password,
		'realname' => $user->realname,
	);

	//echo "About to insert into $wpus->users:\n";
	//echo "<pre>\n";
	//print_r($fields);
	//echo "</pre>\n";

	if ($wpdb->insert($wpus->users, $fields) !== false) {
		// Successfully created user.  Send an e-mail and return success.
		$fullName = $realname . ' ' . $lastname;
		wpus_compose_and_send_client($fullName, $email);

		return $wpdb->insert_id;
	}

	error_log("A database error occurred while adding a user: " . $wpdb->last_error);
	return WPUS_USER_CREATE_ERROR;

}

/**
 * wpus_reset_password
 * Enter description here ...
 * @param unknown_type $email
 * @param unknown_type $password
 */
function wpus_reset_password ($email, $password) {
	global $bcrypt, $wpdb, $wpus;

	$updates = $wpdb->update($wpus->users,
		array (
			'password' => $bcrypt->hash($password),
		),
		array (
			'email' => $email,
		));

	return ($updates === 1);

}

/*
 * Function to send mail to the person who signed up
 */
function wpus_compose_and_send_client($name, $mail) {
	$regex = '/\[\s*([a-zA-Z_][0-9a-zA-Z:._-]*)\s*\]/';

	$callback = array( &$this, 'mail_callback' );

	if (empty($name) || empty($mail)) return false;	// this function requires a recipient

	$subject = "vtONE Digital Downloads account created";
	$sender = "vtONE <info@vt-one.org>";
	$recipient = $name . ' <' . $mail . '>';

	$body = "$name,\r\n\r\n";
	$body .= "Thanks for creating a vtONE Digital Downloads account.  This account will ";
	$body .= "allow you to download vtONE content at any time from digitaldownloads.vt-one.org using your ";
	$body .= "e-mail address and the password you created.\r\n\r\n";

	$body .= "If you have any questions or have trouble downloading content, please contact us by replying to this e-mail.\r\n";
	$body .= "----\r\n";
	$body .= "vtONE\r\n";
	$body .= "uniting His campus for His glory.\r\n";

	$headers = "From: $sender\r\n";

	return @wp_mail( $recipient, $subject, $body, $headers );
}

/**
 * wpus_send_reset_mail
 * Creates a pw reset request and sends it to the user
 * @param WPUS_User $user
 */
function wpus_send_reset_mail ($user) {
	global $wpus;

	$name = $user->realname;
	$mail = $user->email;

	if (($code = wpus_create_pwreset_request($user->email)) !== false) {
		$subject = "vtONE Digital Downloads password reset";
		$sender = "vtONE <info@vt-one.org>";
		$recipient = $name . ' <' . $mail . '>';

		$body = "$name,\r\n\r\n";
		$body .= "Please click the link below or copy and paste it into your favorite we browser to reset";
		$body .= " your password. If you have not requested a password reset, please disregard this e-mail.\r\n\r\n";
		$body .= wpus_redirect_url(array(
			'email' => $mail,
			'code' => $code,
			)) . "\r\n\r\n";
		$body .= "----\r\n";
		$body .= "vtONE\r\n";
		$body .= "uniting His campus for His glory.\r\n";

		$headers = "From: $sender\r\n";

		return @wp_mail( $recipient, $subject, $body, $headers );
	}
	else {
		$wpus->AppendMessage("Unable to generate password reset request due to a database error. Please contact the administrator.");
	}

	return false;
}
