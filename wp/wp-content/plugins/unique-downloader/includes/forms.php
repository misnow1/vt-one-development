<?php

function wpus_user_welcome () {
	global $wpdb, $wpus;

	$url = wpus_redirect_url();

	$form = '';
	$userID = $wpus->user->id;
    $project_id = wpus_get_request_var('item', -1);
    $asset_id = wpus_get_request_var('asset', -1);

	if ($asset_id != -1) {
        /*
         * Render an asset link
         */
    }
    elseif ($project_id != -1) {
		/*
		 * Render an individual item that the user has requested
		 */
		$query = $wpdb->prepare( "SELECT * FROM $wpus->user_projects WHERE user_id = %d AND project_id = %d", $userID, $project_id );

		if ($row = $wpdb->get_row($query)) {
			$projectID = $row->project_id;
			$project = wpus_project($projectID);

            $form .= "<div class=\"wpus-registered-user wpus-project-wrapper row\">\n";
            $form .= "<div class=\"col-md-6\">\n";
			$form .= $project->GetAssetsPanel();
            $form .= "</div> <!-- end col-md-6 -->\n";
            $form .= "</div> <!-- end registered-user -->\n";
		}
		else {
			/*
			 * The Project ID is not valid or the user does not have access to it (yet?)
			 */
			$form .= "<p>You do not have access to the requested item.</p>\n";
		}
	}
	else {
		/*
		 * Render the list of items that the user has purchased
		 */
        $form = "<div class=\"wpus-registered-user wpus-download-links row\">\n";
		//$form .= "<p>Click a link below to download an item you have purchased.</p>\n";

		$query = $wpdb->prepare( "SELECT * FROM $wpus->user_projects WHERE user_id = %d ORDER BY project_id", $userID );

		$rows = $wpdb->get_results($query);

		foreach ($rows as $row) {
			$projectID = $row->project_id;
			$project = wpus_project($projectID);

			if ($project instanceof WPUS_Project) {
				if ($project->enabled == '1') {
                    $form .= "<div class=\"col-md-4\">\n";
					$form .= $project->GetWelcomePanel();
                    $form .= "</div> <!-- end col-md-4 -->\n";
				}
			}
		}

		$form .= "</div> <!-- end registered-user -->\n";
		$form .= "<div class=\"row\">\n";
        $form .= wpus_code_form_form($url);
        $form .= "</div>\n";
	}

	$form .= "<div class=\"pull-right\"><a href=\"" . wpus_redirect_url(array('logout' => 1), array('item')) . "\">log out</a></div><div class=\"clear\"></div>\n";

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

function wpus_wrap_form($title, $content) {

    $form .= "<div class=\"col-md-6\">\n";
    $form .= "<div class=\"panel panel-default\">\n";
    $form .= "<div class=\"panel-heading\"><h2 class=\"panel-title\">$title</h2></div>\n";
    $form .= "<div class=\"panel-body\">\n";
    $form .= $content;
    $form .= "</div> <!-- end panel-body -->\n";
    $form .= "</div> <!-- end panel -->\n";
    $form .= "</div> <!-- end col -->\n";
    return $form;

}

function wpus_control_group($label, $control_name, $control) {
    $form = "<div class=\"form-group\">\n";
    $form .= "<label for=\"control_name\">$label</label>\n";
    $form .= "$control\n";
    $form .= "</div>\n";

    return $form;
}

function wpus_submit_button() {
    $form = "<div class=\"pull-right\">\n";
    $form .= '<button type="submit" class="btn btn-default">Submit</button>' ."\n";
    $form .= "</div> <!-- end submit wrapper -->\n";
    $form .= "</form>\n";

    return $form;
}

function wpus_code_form_form($url) {

	$form .= "<p>To redeem your download code, enter it in the box below.</p>\n";

	$form .= "<form action=\"$url\" method=\"post\" >\n";

    $form .= wpus_control_group('Download Code', 'wpus-access-code', "<input type=\"text\" class=\"form-control\" size=\"40\" maxlength=\"29\" name=\"wpus-access-code\"/>");

	$form .= "<div class=\"small\">" . WPUS_CODE_FORMAT_HUMAN . "<br/>\n";
	$form .= WPUS_CODE_FORMAT_HUMAN2 . "</div><br/>\n";

    $form .= wpus_submit_button();

	return wpus_wrap_form("Redeem Code", $form);
}

function wpus_create_account_form_form($url) {
	global $wpus;

	$form .= "<p>Create an account by entering your e-mail address and a password below.</p>\n";

	$form .= "<form action=\"$url\" method=\"post\" >\n";
	$form .= "<input type=\"hidden\" name=\"wpus-create-account\" value=\"wpus-create-account\">\n";

	$code = $wpus->GetPendingCode();
	if ($code !== false) {
		$form .= "<input type=\"hidden\" name=\"wpus-access-code\" value=\"$code\">\n";
	}

    $form .= wpus_control_group('Name', 'wpus-realname', "<input type=\"text\" class=\"form-control\" size=\"20\" maxlength=\"100\" name=\"wpus-realname\"/>");
	$form .= wpus_control_group('E-mail Address', 'wpus-email', "<input type=\"text\" class=\"form-control\" size=\"20\" maxlength=\"100\" name=\"wpus-email\"/>");
    $form .= wpus_control_group('Password', 'wpus-password', "<input type=\"password\" class=\"form-control\" size=\"20\" maxlength=\"100\" name=\"wpus-password\"/>");
    $form .= wpus_control_group('Confirm Password', 'wpus-password-confirm', "<input type=\"password\" class=\"form-control\" size=\"20\" maxlength=\"100\" name=\"wpus-password-confirm\"/>");

    $form .= wpus_submit_button();

    return wpus_wrap_form("Create Account", $form);
}

function wpus_login_form_form ($url) {
	global $wpus;

	$form = "<p>To access content, log in using the account you created when redeeming your code.</p>\n";

	$form .= "<form action=\"$url\" method=\"post\" >\n";
	$form .= "<input type=\"hidden\" name=\"wpus-login\" value=\"wpus-login\">\n";

	$code = $wpus->GetPendingCode();
	if ($code !== false) {
		$form .= "<input type=\"hidden\" name=\"wpus-access-code\" value=\"$code\">\n";
	}

    $form .= wpus_control_group('E-mail address', 'wpus-email', "<input type=\"text\" class=\"form-control\" size=\"20\" maxlength=\"100\" name=\"wpus-email\"/>");
    $form .= wpus_control_group('Password', 'wpus-password', "<input type=\"password\" class=\"form-control\" size=\"20\" maxlength=\"100\" name=\"wpus-password\"/>");

    $form .= wpus_submit_button();
	$form .= "<a href=\"" . wpus_redirect_url(array('resetpw' => 1)) . "\" class=\"small\">forgot password</a>\n";

    return wpus_wrap_form("Log In", $form);
}

function wpus_request_pwreset_form ($url) {
	$form = "<p>To reset your password, enter the e-mail address you used to register in the form below.</p>\n";

	$form .= "<form action=\"$url\" method=\"post\" >\n";
	$form .= "<input type=\"hidden\" name=\"wpus-request-pwreset\" value=\"wpus-request-pwreset\">\n";

    $form .= wpus_control_group('E-mail address', 'wpus-email', "<input type=\"text\" class=\"form-control\" size=\"20\" maxlength=\"100\" name=\"wpus-email\"/>");

    $form .= wpus_submit_button();

    return wpus_wrap_form("Reset Password", $form);
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
		$form .= "<p>Reset your password by entering a new one below and clicking Submit</p>\n";

		$form .= "<form action=\"$url\" method=\"post\" >\n";
		$form .= "<input type=\"hidden\" name=\"wpus-reset-password\" value=\"wpus-reset-password\">\n";
		$form .= "<input type=\"hidden\" name=\"wpus-reset-email\" value=\"$email\">\n";
		$form .= "<input type=\"hidden\" name=\"wpus-reset-code\" value=\"$code\">\n";


        $form .= wpus_control_group('Password', 'wpus-password', "<input type=\"password\" class=\"form-control\" size=\"20\" maxlength=\"100\" name=\"wpus-password\"/>");
        $form .= wpus_control_group('Confirm Password', 'wpus-password-confirm', "<input type=\"password\" class=\"form-control\" size=\"20\" maxlength=\"100\" name=\"wpus-password-confirm\"/>");

	    $form .= wpus_submit_button();

        $form = wpus_wrap_form("Reset Password", $form);
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
