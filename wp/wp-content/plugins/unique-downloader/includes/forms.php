<?php

function wpus_user_welcome () {
	global $wpdb, $wpus;
	
	$url = wpus_redirect_url();
	
	$form = '';
	$userID = $wpus->user->id;
			
	if (isset($_GET['item'])) {
		/*
		 * Render an individual item that the user has requested
		 */
		$projectID = $_GET['item'];
		if (is_numeric($projectID)) {
			$query = $wpdb->prepare( "SELECT * FROM $wpus->user_projects WHERE user_id = %d AND project_id = %d", $userID, $projectID );
			
			if ($row = $wpdb->get_row($query)) {
				$projectID = $row->project_id;
				$project = wpus_project($projectID);
		
				//echo "<pre>\n";
				//print_r($project);
				//echo "</pre>\n";
				
				if ($project instanceof WPUS_Project) {
					if ($project->enabled == '1') {
						$form .= $project->GetDownloadLink(true);
					}
				}
			}
			else {
				/*
				 * The Project ID is not valid or the user does not have access to it (yet?)
				 */
				$form .= "<p>You do not have access to the requested item.</p>\n";
			}
		}
		else {
			$form .= "<p>The item requested is not valid.</p>\n";
		}
	}
	else {
		/*
		 * Render the list of items that the user has purchased
		 */
		$form .= "<div class=\"wpus-all-downloads-wrapper\">\n";
		$form .= "<h3>Download</h3>\n";
		//$form .= "<p>Click a link below to download an item you have purchased.</p>\n";
		
		$query = $wpdb->prepare( "SELECT * FROM $wpus->user_projects WHERE user_id = %d ORDER BY project_id", $userID );
	
		$rows = $wpdb->get_results($query);
		
		foreach ($rows as $row) {
			$projectID = $row->project_id;
			$project = wpus_project($projectID);
	
			//echo "<pre>\n";
			//print_r($project);
			//echo "</pre>\n";
			
			if ($project instanceof WPUS_Project) {
				if ($project->enabled == '1') {
					$form .= $project->GetDownloadLink(false);
				}
			}
		}
		
		$form .= "</div>\n";
		//$form .= "<p>To redeem another code, enter it below.</p>\n";
		$form .= wpus_code_form_form($url);
	}
	
	$form .= "<div class=\"wpus-logout-wrapper\"><a href=\"" . wpus_redirect_url(array('logout' => 1)) . "\">log out</a></div><div class=\"clear\"></div>\n";
	
	return $form;
}

function wpus_welcome_form () {
	global $wpus;
	$url = wpus_redirect_url();
	
	$form .= wpus_code_form_form($url);	
	$form .= wpus_login_form_form($url);
	
	return $form;
}

function wpus_create_account_form () {
	global $wpdb,$wpus;

	$url = wpus_redirect_url();
	
	$form .= "<script type=\"text/javascript\">
	function showCreateAccount() {
		jQuery('#wpus-dl-create-account').show();
		jQuery('#wpus-dl-existing-account').hide();
	}
	function showExistingAccount() {
		jQuery('#wpus-dl-create-account').hide();
		jQuery('#wpus-dl-existing-account').show();
	}
	</script>";	

	$form .= "In order to register your code, you'll need an account.  Please select an option below:<br/>\n";
	$form .= "<ul>\n";
	$form .= "<li><a onclick=\"showCreateAccount();\">Create an account</a></li>\n";
	$form .= "<li><a onclick=\"showExistingAccount();\">Log in to an existing account</a></li>\n";
	$form .= "</ul>\n";	
	
	$form .= '<div id="wpus-dl-create-account" style="display:none;">';
	$form .= wpus_create_account_form_form($url);
	$form .= '</div>';
		
	$form .= '<div id="wpus-dl-existing-account" style="display:none;">';
	$form .= wpus_login_form_form($url);
	$form .= '</div>';
	
	return $form;
}

function wpus_code_form_form($url) {
	
	$form .= "<div class=\"wpus-form-wrapper\">\n";
	$form .= "<h3>Redeem</h3>\n";
	$form .= "<p>To redeem your download code, enter it in the box below.</p>\n";
	
	$form .= "<form action=\"$url\" method=\"post\" >\n";
	
	$form .= "<table>\n";
	$form .= "<tr><td>Download Code:</td><td colspan=2><input type=\"text\" size=\"40\" maxlength=\"29\" name=\"wpus-access-code\"/></td></tr>\n";
	$form .= "</table><br/>\n";
	
	$form .= "<div class=\"small\">" . WPUS_CODE_FORMAT_HUMAN . "<br/>\n";
	$form .= WPUS_CODE_FORMAT_HUMAN2 . "</div><br/>\n";
	
	$form .= "<div class=\"wpus-submit-wrapper\">\n";
	$form .= '<input type="submit" value="Submit" class="wpus-submit"/>' ."\n";
	$form .= "</div>\n";
	$form .= "</form>\n";
	$form .= "</div>\n";
	
	return $form;
}

function wpus_create_account_form_form($url) {
	global $wpus;
	
	$form = "<div class=\"wpus-form-wrapper\">\n";
	$form .= "<h3>Create Account</h3>\n";	
	$form .= "<p>Create an account by entering your e-mail address and a password below.</p>\n";

	$form .= "<form action=\"$url\" method=\"post\" >\n";
	$form .= "<input type=\"hidden\" name=\"wpus-create-account\" value=\"wpus-create-account\">\n";
	
	$code = $wpus->GetPendingCode();
	if ($code !== false) {
		$form .= "<input type=\"hidden\" name=\"wpus-access-code\" value=\"$code\">\n";
	}
	
	$form .= "<table>\n";
	$form .= "<tr><td>First Name:</td><td><input type=\"text\" size=\"20\" maxlength=\"100\" name=\"wpus-firstname\"/>" . 
		"</td></tr>\n";
	$form .= "<tr><td>Last Name:</td><td><input type=\"text\" size=\"20\" maxlength=\"100\" name=\"wpus-lastname\"/>" . 
		"</td></tr>\n";
	$form .= "<tr><td>E-mail address:</td><td><input type=\"text\" size=\"20\" maxlength=\"100\" name=\"wpus-email\"/>" . 
		"</td></tr>\n";
	$form .= "<tr><td>Pasword:</td><td><input type=\"password\" size=\"20\" maxlength=\"100\" name=\"wpus-password\"/>" . 
		"</td></tr>\n";
	$form .= "<tr><td>Confirm pasword:</td><td><input type=\"password\" size=\"20\" maxlength=\"100\" name=\"wpus-password-confirm\"/>" . 
		"</td></tr>\n";
	$form .= "</table>\n";
	$form .= "<div class=\"wpus-submit-wrapper\">\n";
	$form .= '<input type="submit" value="Submit" class="wpus-submit"/>' ."\n";
	$form .= "</div>\n";
	$form .= "</form>\n";
	$form .= "</div>\n";
	
	return $form;
	
}

function wpus_login_form_form ($url) {
	global $wpus;
	
	$form = "<div class=\"wpus-form-wrapper\">\n";
	$form .= "<h3>Log In</h3>\n";	
	$form .= "<p>To access content, log in using the account you created when redeeming your code.</p>\n";

	$form .= "<form action=\"$url\" method=\"post\" >\n";
	$form .= "<input type=\"hidden\" name=\"wpus-login\" value=\"wpus-login\">\n";
	
	$code = $wpus->GetPendingCode();
	if ($code !== false) {
		$form .= "<input type=\"hidden\" name=\"wpus-access-code\" value=\"$code\">\n";
	}
	
	$form .= "<table>\n";
	$form .= "<tr><td>E-mail address:</td><td><input type=\"text\" size=\"20\" maxlength=\"100\" name=\"wpus-email\"/>" . 
		"</td></tr>\n";
	$form .= "<tr><td>Pasword:</td><td><input type=\"password\" size=\"20\" maxlength=\"100\" name=\"wpus-password\"/>" . 
		"</td></tr>\n";
	$form .= "</table>\n";
	$form .= "<div class=\"wpus-submit-wrapper\">\n";
	$form .= '<input type="submit" value="Submit" class="wpus-submit"/>' ."\n";
	$form .= "</div>\n";
	$form .= "<a href=\"" . wpus_redirect_url(array('resetpw' => 1)) . "\" class=\"small\">forgot password</a>\n";
	$form .= "</form>\n";
	$form .= "</div>\n";
	
	return $form;
}

function wpus_request_pwreset_form ($url) {
	$form .= "<div class=\"wpus-form-wrapper\">\n";
	$form .= "<h3>Reset Password</h3>\n";
	$form .= "<p>To reset your password, enter the e-mail address you used to register in the form below.</p>\n";
	
	$form .= "<form action=\"$url\" method=\"post\" >\n";
	$form .= "<input type=\"hidden\" name=\"wpus-request-pwreset\" value=\"wpus-request-pwreset\">\n";

	$form .= "<table>\n";
	$form .= "<tr><td>E-mail Address:</td><td colspan=2><input type=\"text\" size=\"40\" maxlength=\"100\" name=\"wpus-email\"/></td></tr>\n";
	$form .= "</table>\n";
	
	$form .= "<div class=\"wpus-submit-wrapper\">\n";
	$form .= '<input type="submit" value="Submit" class="wpus-submit"/>' ."\n";
	$form .= "</div>\n";
	$form .= "</form>\n";
	$form .= "</div>\n";
	
	return $form;
}

function wpus_handle_pwreset_form($url) {
	if (isset($_SESSION['wpus-pwreset-email'])) {
		$email = $_SESSION['wpus-pwreset-email'];
	}
	else {
		$email = wpus_get_request_var('email', '');
	}
	
	if (isset($_SESSION['wpus-pwreset-code'])) {
		$code = $_SESSION['wpus-pwreset-code'];
	}
	else {
		$code = wpus_get_request_var('code', '');
	}
	
	if (wpus_verify_pwreset_request($email, $code)) {
		/*
		 * The password reset request was valid. Present the form for the needful
		 */
		$form = "<div class=\"wpus-form-wrapper\">\n";
		$form .= "<h3>Reset Password</h3>\n";	
		$form .= "<p>Reset your password by entering a new one below and clicking Submit</p>\n";
	
		$form .= "<form action=\"$url\" method=\"post\" >\n";
		$form .= "<input type=\"hidden\" name=\"wpus-reset-password\" value=\"wpus-reset-password\">\n";
		$form .= "<input type=\"hidden\" name=\"wpus-reset-email\" value=\"$email\">\n";
		$form .= "<input type=\"hidden\" name=\"wpus-reset-code\" value=\"$code\">\n";
		
		$form .= "<table>\n";
		$form .= "<tr><td>New Pasword:</td><td><input type=\"password\" size=\"20\" maxlength=\"100\" name=\"wpus-password\"/>" . 
			"</td></tr>\n";
		$form .= "<tr><td>Confirm pasword:</td><td><input type=\"password\" size=\"20\" maxlength=\"100\" name=\"wpus-password-confirm\"/>" . 
			"</td></tr>\n";
		$form .= "</table>\n";
		$form .= "<div class=\"wpus-submit-wrapper\">\n";
		$form .= '<input type="submit" value="Submit" class="wpus-submit"/>' ."\n";
		$form .= "</div>\n";
		$form .= "</form>\n";
		$form .= "</div>\n";
	}
	else {
		/*
		 * WHOA! The request was not valid!
		 */
		$form .= "The password request link provided is not valid. Please request another one from the <a href=\"" . wpus_redirect_url(array(), array('resetpw', 'email', 'code')) . "\">login</a> page</p>";
	}
	
	
	return $form;
	
}


?>