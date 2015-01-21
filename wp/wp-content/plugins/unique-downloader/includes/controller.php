<?php

add_action('wp_enqueue_scripts','wpus_enqueue_scripts');

function wpus_enqueue_scripts () {
	// scripts
	
	// and styles
	wp_register_style( 'wp-unique-dl', plugins_url( 'unique-downloader/css/wp-unique-dl.css' ), array( 'dashicons' ), '20140125.01');
	wp_enqueue_style( 'wp-unique-dl' );
}

/*
 * Once wordpress is loaded, check to see if we need to redirect to handle a POST
 */
add_action('wp_loaded', 'wpus_check_post');

function wpus_check_post() {
	global $wpdb, $wpus;
	
	/*
	 * Check GET variables
	 */
	if (isset($_GET['logout'])) {
		/*
		 * Log the user off
		 */
		$wpus->LogEvent("User " . $wpus->user->email . " logged out.", WPUS_LOG_USER);
		
		$_SESSION['wpus_email'] = '';
		unset($_SESSION['wpus_email']);
		
		wp_redirect(wpus_redirect_url(array(), array('logout')));
		exit();
	}
	
	/*
	 * Check for the various types of POST actions that are done without a user set
	 */
	if (!$wpus->user) {
		if (isset($_POST['wpus-login'])) {
			// user has requested a login action, check username and password
			if (!isset($_POST['wpus-email']) || !isset($_POST['wpus-password'])) {
				$wpus->AppendMessage("POST missing required field.");
				return;
			}
		
			// Remove the whitespace from what was posted and check again
			$email = trim($_POST['wpus-email']);
			$password = trim($_POST['wpus-password']);
		
			if ($user = wpus_verify_user($email, $password)) {
				$wpus->user = $user;
				$_SESSION['wpus_email'] = $email;
					
				$wpus->LogEvent("User $email logged in.", WPUS_LOG_USER);
			}
			else {
				$wpus->AppendMessage("<i>Invalid user name or password.  Please try again.</i>");
					
				if ($wpus->GetPendingCode() !== false) {
					$wpus->LogEvent("User $email failed login with pending project code.", WPUS_LOG_BAD_LOGIN);
				}
				else {
					$wpus->LogEvent("User $email failed login.", WPUS_LOG_BAD_LOGIN);
				}
			}
		
		}
		elseif (isset($_POST['wpus-create-account'])) {
			// verify that username and password are entered
			if (!isset($_POST['wpus-email'])) {
				$wpus->AppendMessage("POST missing required field: e-mail");
			}
			if (!(isset($_POST['wpus-password']) && isset($_POST['wpus-password-confirm']))) {
				$wpus->AppendMessage("POST missing required field: password or password-confirm");
			}
			if (!isset($_POST['wpus-firstname'])) {
				$wpus->AppendMessage("POST missing required field: first name");
			}
			if (!isset($_POST['wpus-lastname'])) {
				$wpus->AppendMessage("POST missing required field: last name");
			}
		
			if ($wpus->HasMessages()) {
				return;	// there are messages to display so bail out
			}
		
			// Remove the whitespace from what was posted and check again
			$email = trim($_POST['wpus-email']);
			$password = trim($_POST['wpus-password']);
			$passwordConfirm = trim($_POST['wpus-password-confirm']);
			$firstname = trim($_POST['wpus-firstname']);
			$lastname = trim($_POST['wpus-lastname']);
		
			if (empty($firstname) || empty($lastname)) {
				$wpus->AppendMessage("Your first name and last name are required to create an account.");
			}
			if (empty($email)) {
				$wpus->AppendMessage("Your e-mail address is required to create an account.");
			}
			if (empty($password) || empty($passwordConfirm)) {
				$wpus->AppendMessage("The password is empty.  Please enter a password.");
			}
			if ($password != $passwordConfirm) {
				$wpus->AppendMessage("The passwords do not match.  Please enter the same password in the \"Password\" and \"Confirm Password\" fields.");
			}
		
			if ($wpus->HasMessages()) {
				return;	// there are messages to display so bail out
			}
		
			$userResult = wpus_create_user($firstname, $lastname, $email, $password);
		
			if ($userResult > 0) {
				// the result was a valid ID. Do the needful.
				$wpus->LogEvent("Created account for $email.", WPUS_LOG_NEW_USER);
				
				// make the new user the current active user
				$user = new WPUS_User();
				$user->id = $userResult;
				$user->email = $email;
				$user->firstname = $firstname;
				$user->lastname = $lastname;
			
				$wpus->user = $user;
				$_SESSION['wpus_email'] = $email;
			}
			else {
				switch ($userResult) {
					case WPUS_USER_BAD_FORMAT:
						$wpus->LogEvent("Failed to create account for $email: a required field was missing.", WPUS_LOG_NEW_USER);
						$wpus->AppendMessage("The account could not be created: a required field was missing.");
						break;
					case WPUS_USER_ALREADY_EXISTS:
						$wpus->LogEvent("Failed to create account for $email: the user already exists.", WPUS_LOG_NEW_USER);
						$wpus->AppendMessage("An account already exists for $email.  Please log in to that account below.  If you have forgotten your password, please click \"Log in to an existing account\" and then click the \"forgot password\" link below.");
						break;
					default:
						$wpus->LogEvent("Failed to create account for $email: unknown error code $userResult was returned.", WPUS_LOG_NEW_USER);
						$wpus->AppendMessage("An unknown error occurred while trying to create your account.  Please contact info@vt-one.org.");
				}
			}
		}
		elseif (isset($_POST['wpus-request-pwreset'])) {
			// verify that an e-mail address was provided
			$email = wpus_get_request_var('wpus-email', '');
		
			if (empty($email)) {
				$wpus->AppendMessage("An e-mail address is required.");
			}
			else {
				// verify that the user exists
				$user = wpus_user_by_email($email);
				if (!$user) {
					$wpus->LogEvent("User $email requested password reset, but is not registered.", WPUS_LOG_PWRESET_FAIL);
		
					$wpus->AppendMessage("The e-mail address provided is not registered.");
				}
				else {
					$wpus->LogEvent("User $email requested password reset.", WPUS_LOG_PWRESET_REQUEST);
		
					if (wpus_send_reset_mail($user)) {
						$wpus->AppendMessage("A password reset e-mail has been sent to $email. Please check your spam or junk mail folder if you have not received it in a few minutes.");
					}
					else {
						$wpus->AppendMessage("Unable to send password reset e-mail. Please contact the administrators.");
					}
				}
			}
		}
		elseif (isset($_POST['wpus-reset-password'])) {
			$email = wpus_get_request_var('wpus-reset-email', '');
			$code = wpus_get_request_var('wpus-reset-code', '');
			$password = wpus_get_request_var('wpus-password');
			$passwordConfirm = wpus_get_request_var('wpus-password-confirm');
		
			if (wpus_verify_pwreset_request($email, $code)) {
				if (empty($password) || empty($passwordConfirm)) {
					$wpus->LogEvent("Password reset for $email failed because a password was not provided.", WPUS_LOG_PWRESET_FAIL);
		
					$wpus->AppendMessage("A new password is required.");
					$_SESSION['wpus-pwreset'] = true;
					$_SESSION['wpus-pwreset-email'] = $email;
					$_SESSION['wpus-pwreset-code'] = $code;
				}
				elseif ($password != $passwordConfirm) {
					$wpus->LogEvent("Password reset for $email failed because passwords did not match.", WPUS_LOG_PWRESET_FAIL);
		
					$wpus->AppendMessage("The passwords do not match.");
					$_SESSION['wpus-pwreset'] = true;
					$_SESSION['wpus-pwreset-email'] = $email;
					$_SESSION['wpus-pwreset-code'] = $code;
				}
				else {
					unset($_SESSION['wpus-pwreset']);
					unset($_SESSION['wpus-pwreset-email']);
					unset($_SESSION['wpus-pwreset-code']);
		
					if (wpus_reset_password($email, $password)) {
						$wpus->LogEvent("Password reset for $email was successful.", WPUS_LOG_PWRESET_SUCCESS);
		
						$_SESSION['pwresetsuccess'] = true;
						wp_redirect(wpus_redirect_url());
						exit();
					}
					else {
						$wpus->AppendMessage("There was an error changing your password. Please contact the administrators.");
					}
				}
			}
			else {
				$wpus->AppendMessage("The password reset request is not valid or has expired. Please request a new one below.");
			}
		}
		
	}
	
	
	/*
	 * Check for a code in the POSTed values
	 */
	if (isset($_POST['wpus-access-code'])) {
		// user posted a code, see if it's valid
		$code = $_POST['wpus-access-code'];
		
		$status = wpus_validate_code($code);
		
		if ($status >= 0) {
			// The code is valid, add the project to the user or to the session (if no user) and redirect
			if ($wpus->user) {
				$wpus->LogEvent("User " . $wpus->user->email . " registered code $code.", WPUS_LOG_USER_ADD_CODE);
				
				// handle the pending project code
				$wpus->SetPendingCode($status, $code);
				if ($wpus->AddPendingProjectToActiveUser()) {
					wp_redirect(wpus_redirect_url());
					exit();
				}
				else {
					$wpus->LogEvent("Error while adding user to project", WPUS_LOG_ACTIVATE_CODE);
					$wpus->AppendMessage("An error occurred while attempting to add the project to your account. Please contact info@vt-one.org for more information.", true);
				}
				
			}
			else {
				$wpus->LogEvent("Successfully activated code $code for project $status, waiting for user.", WPUS_LOG_ACTIVATE_CODE);
				$wpus->SetPendingCode($status, $code);
			}
		}
		else {
			$wpus->LogEvent("Invalid code $code was entered.", WPUS_LOG_BAD_CODE);
			
			$wpus->AppendMessage(wpus_human_readable_error($status));
		}
	}
	
	/*
	 * Check the session to see if there's any work to be done (like a code to be added to a user)
	 */
	if (isset($_SESSION['wpus_project'])) {
		// there is a code hanging out in the session, blow it away (this is legacy stuff)
		unset($_SESSION['wpus_project']);
		unset($_SESSION['wpus_code']);
	}
	
	//echo "<p>Checking POST variables</p><pre>\n";
	//print_r($_REQUEST);
	//echo "</pre>\n";
	//echo "<p>Session variables:</p><pre>\n";
	//print_r($_SESSION);
	//echo "</pre>\n";
	//echo "<p>WPUS Object</p><pre>\n";
	//print_r($wpus);
	//echo "</pre>\n";
	
	
}

add_shortcode( 'unique-downloader', 'wpus_unique_downloader_tag_func' );

function wpus_unique_downloader_tag_func( $atts ) {
	/*
	 * Render the downloader here
	 */
	global $wpus;

	if ( is_feed() )
		return '[unique-downloader]';

	if ( $wpus->processing_within ) { // Inside post content or text widget
		$wpus->unit_count += 1;
		$unit_count = $wpus->unit_count;
		$processing_within = $wpus->processing_within;

	} else { // Inside template

		if ( ! isset( $wpus->global_unit_count ) )
			$wpus->global_unit_count = 0;

		$wpus->global_unit_count += 1;
		$unit_count = 1;
		$processing_within = 't' . $wpus->global_unit_count;
	}

	//$form .= "<pre>\n";
	//$form .= print_r($_SESSION, true);
	//$form .= "</pre>\n";
	
	if (isset($_SESSION['pwresetsuccess'])) {
		$wpus->AppendMessage("Your password was changed successfully. Please log in below.");
		unset($_SESSION['pwresetsuccess']);
	}
	
	/*
	 * Core logic here.  Determine what state we're in.
	 */
	$form = '';
	if ($wpus->user) {
		// a user is logged in
		$form .= wpus_user_welcome();
	}
	elseif ((isset($_GET['resetpw']) && isset($_GET['code']) && isset($_GET['email'])) || 
		(isset($_SESSION['wpus-pwreset']) && isset($_SESSION['wpus-pwreset-code']) && isset($_SESSION['wpus-pwreset-email']))) {
		/*
		 * Password reset code is present in the URL or session. Handle it.
		 */
		$form .= wpus_handle_pwreset_form(wpus_redirect_url(array(), array('resetpw', 'email', 'code')));
	}
	elseif (isset($_GET['resetpw'])) {
		/*
		 * user has requested a password reset
		 */
		$form .= wpus_request_pwreset_form(wpus_redirect_url());
	}
	elseif ($wpus->GetPendingCode() !== false) {
		/*
		 * User has requested a unique download but is not logged in
		 */
		$form .= wpus_create_account_form();
	}
	else {
		// render the form
		$form .= wpus_welcome_form();
	}

	/*
	 * Process the outputz
	 */
	$output = "<div class=\"wpus\">\n";
	if ($wpus->HasMessages()) {
		$output .= wpus_get_messages();
	}
	
	$output .= $form;
	$output .= "</div>";
	return $output;
}

/*
 * Function to verify that an entered code is valid.  Returns either a project id or 
 */
function wpus_validate_code($code) {
	global $wpus, $wpdb;
	
	$status = WPUS_CODE_INVALID;
	
	if (preg_match(WPUS_CODE_REGEX, $code) || preg_match(WPUS_CODE_REGEX2, $code)) {
		$codeo = wpus_code_by_code($code);
		
		if ($codeo !== false) {
			if ($codeo->enabled == '0') {
				$status = WPUS_CODE_DISABLED;
			}
			elseif ($codeo->remaining_uses <= 0) {
				$status = WPUS_CODE_NO_USES_REMAINING;
			}
			else {
				// valid code!
				$status = $codeo->project_id;
			}
		}
	}
	else {
		$status = WPUS_CODE_BAD_FORMAT;
	}
	
	/*
	 * Log the attempt
	 */
	$fields = array(
		'code' => $code,
		'when' => date('Y-m-d H:i:s', time()),
		'ipaddress' => $_SERVER['REMOTE_ADDR'],
		'status' => $status,
	);
	if (!$wpdb->insert($wpus->code_attempts, $fields)) {
		// couldn't insert the log into the table, so that's weird
		return WPUS_CODE_DATABASE_ERROR;
	}
	
	return $status;
}

function wpus_register_code($code) {
	global $wpdb, $wpus;
	
	// update the count of remaining uses
	if ($codeo = wpus_code_by_code($code)) {
		$codeo->remaining_uses--;
		$remaining_uses = $codeo->remaining_uses;
		$wpdb->update($wpus->codes, array('remaining_uses' => $remaining_uses), array('id' => $codeo->id));
	}
}


//echo "<p>" . date_default_timezone_get() . "</p><p>" . strftime('%Y-%m-%d %H:%M', time()) . "</p>";
?>
