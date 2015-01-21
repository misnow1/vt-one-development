<?php
require_once WPUS_PLUGIN_DIR . '/admin/pages.php';

add_action('admin_enqueue_scripts', 'wpus_manager_admin_scripts');
function wpus_manager_admin_scripts ($pageName) {
	if ($pageName == "toplevel_page_download_code_projects") {
		wp_enqueue_media();
	}
	
	// jQuery UI for the date picker
	wp_enqueue_script( 'jquery-ui', plugins_url('unique-downloader/js/jquery-ui-1.10.3.custom.min.js'), array('jquery'), '1.10.3.custom');
	
	wp_register_style('jquery-ui-smoothness', plugins_url('unique-downloader/css/smoothness/jquery-ui-1.10.3.custom.min.css'), array(), '1.10.3.custom');
	wp_enqueue_style('jquery-ui-smoothness');
	
	wp_register_style('wp-unique-dl-admin', plugins_url('unique-downloader/css/wp-unique-dl-admin.css'), array(), '20140125.01');
	wp_enqueue_style('wp-unique-dl-admin');

	// we need thickbox support
	add_thickbox();
}

function wpus_admin_has_edit_cap() {
	return current_user_can('manage_options');
}

add_filter( 'plugin_action_links', 'wpus_plugin_action_links', 10, 2 );

function wpus_plugin_action_links( $links, $file ) {
	if ( $file != WPUS_PLUGIN_BASENAME )
		return $links;

	$url = wpus_admin_url( array( 'page' => 'wpus' ) );

	$settings_link = '<a href="' . esc_attr( $url ) . '">Settings</a>';

	array_unshift( $links, $settings_link );

	return $links;
}

add_action( 'admin_menu', 'wpus_admin_add_pages', 9 );

function wpus_admin_add_pages() {
	add_menu_page('Download Code Projects', 'Projects', 'manage_options', WPUS_PAGES_PROJECTS_PAGE, 'wpus_admin_management_page' );

	add_submenu_page( WPUS_PAGES_PROJECTS_PAGE, 'History', 'Code History', 'manage_options', WPUS_PAGES_CODE_PAGE, 'wpus_admin_code_page' );
	
	add_submenu_page( WPUS_PAGES_PROJECTS_PAGE, 'Users', 'Users', 'manage_options', WPUS_PAGES_USERS_PAGE, 'wpus_admin_users_page' );
	
	add_submenu_page( WPUS_PAGES_PROJECTS_PAGE, 'Attempts', 'Attempts', 'manage_options', WPUS_PAGES_LOG_PAGE, 'wpus_admin_attempts_page' );
		
	add_submenu_page( WPUS_PAGES_PROJECTS_PAGE, 'Sales', 'Sales', 'manage_options', WPUS_PAGES_SALES_PAGE, 'wpus_admin_sales_page' );
	
	add_submenu_page( WPUS_PAGES_PROJECTS_PAGE, 'Configure', 'Configure', 'manage_options', WPUS_PAGES_CONFIGURE_PAGE, 'wpus_admin_configure_page' );
}


add_action('init', 'wpus_handle_post_actions');

function wpus_handle_post_actions() {
	global $wpdb, $wpus;
	
	/*
	 * See if the user would like a CSV version of the output
	 */
	if (isset($_GET['dl'])) {
		/*
		 * See if a project needs to be loaded
		 */
		if (!isset( $_GET['project'])) {
			$_GET['project'] = '';
		}
		
		if ( 'new' == $_GET['project'] ) {
			$unsaved = true;
			$current = -1;
			$cf = wpus_project_default_pack();
		} elseif ( $cf = wpus_project( $_GET['project'] ) ) {
			$current = (int) $_GET['project'];
		} else {
			$first = reset( $projects ); // Returns first item
			$current = $first->id;
			$cf = wpus_project( $current );
		}
		
		$codes = '';
		if (is_a($cf, 'WPUS_Project')) {
			$codes = wpus_codes_for_project($cf);
		}
		
		if (is_array($codes)) {
			$csv = '"id","code","remaining uses","enabled","user"' . "\r\n";
			
			foreach ($codes as $code) {
				$csv .= '"' . $code['id'] . '",';
				$csv .= '"' . $code['code'] . '",';
				$csv .= '"' . $code['remaining_uses'] . '",';
				$csv .= '"' . $code['enabled'] . '",';
				$csv .= '"' . $code['user'] . '"';
				$csv .= "\r\n";
			}
			
			header('Content-Description: File Transfer');
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="' . $cf->name . '.csv"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Content-Length: ' . strlen($csv));
			echo $csv;
			exit;
		}
		
	}
	
	/*
	 * Check for POST actions
	 */
	if (isset($_POST['wpus-gen-codes'])) {
		// user has requested to generate more codes.  yay.
		
		// verify some required things
		$num_codes = wpus_get_request_var('wpus-num-codes', 0);
		
		if (($num_codes > 0) && ($project = wpus_project(wpus_get_request_var('wpus-id', -1)))) {
			// generate some codes now
			
			if ($project->GenerateCodes($num_codes)) {
				// codes were generated, redirect so the request is a GET
				wp_redirect(wpus_admin_url(array('project' => $project->id)));
				exit();
			}
			else {
				$wpus->AppendMessage("Unable to generate the requested number of codes. Make sure a project key is set.");
			}
			
		}
		else {
			$wpus->AppendMessage("The requested number of codes or the project ID is not valid.");
		}
	}
	elseif (isset($_POST['wpus-save'])) {
		$pid = wpus_get_request_var('wpus-id', '');
		$name = wpus_get_request_var('wpus-name', '');
		$key = wpus_get_request_var('wpus-key', '');
		$sku = wpus_get_request_var('wpus-sku', '');
		
		if (!$pid || !$name || !$key)  {
			// there is no project ID specified in the save request
			$wpus->AppendMessage("Unable to save project: the project ID, name, and key are all required.");
		}
		elseif (!preg_match(WPUS_KEY_REGEX, $key)) {
			// make sure the key is in a valid format
			$wpus->AppendMessage("Unable to save project: the key must be in the format " . WPUS_KEY_REGEX . ".");
		}
		else {
			// gather useful things from the POST
			if (is_numeric($pid)) {
				// fix the key (append a letter if there's not one there already)
				if (!preg_match('/[a-z]$/', $key)) {
					// the key did not end with a letter, append the default
					$key .= 'v';
				}
				
				$fields = array(
					'name' => $name,
					'date_available' => $_POST['wpus-date'],
					'enabled' => ($_POST['wpus-enabled'] == 'on' ? 1 : 0),
					'cdn_url' => $_POST['wpus-cdn-url'],
					'cdn_url2' => $_POST['wpus-cdn-url2'],
					'cdn_server' => $_POST['wpus-cdn-server'],
					'image_url' => $_POST['wpus-image-url'],
					'key' => $key,
					'sku' => $sku,
				);
				
				if ($pid == -1) {
					// new project without an existing ID
					$query = $wpdb->insert($wpus->projects, $fields);
					$pid = $wpdb->insert_id;
				}
				else {
					// an old project to be updated!
					$query = $wpdb->update($wpus->projects, $fields, array('id' => $pid));
				}
				
				if ($query === false) {
					$wpus->AppendMessage('Unable to save project. The database error was ' . $wpdb->last_error);
				}
				else {
					$url = wpus_admin_url(array('project' => $pid));
					wp_redirect($url);
					exit();
				}
			}
		}
	}
	elseif (isset($_POST['wpus-create-order'])) {
		$required = array('wpus-order-ordernum', 'wpus-order-firstname', 'wpus-order-lastname', 'wpus-order-email', 'wpus-order-projects');
		foreach ($required as $k) {
			if (!isset($_POST[$k])) {
				$wpus->AppendMessage("Error: Missing required field $k.");
			}
		}
		
		if (!$wpus->HasMessages()) {
			$ordernum = trim($_POST['wpus-order-ordernum']);
			$firstname = trim($_POST['wpus-order-firstname']);
			$lastname = trim($_POST['wpus-order-lastname']);
			$email = trim($_POST['wpus-order-email']);
			$projects = $_POST['wpus-order-projects'];
			
			if (!$ordernum) {
				$wpus->AppendMessage("Error: required field order number is empty.");
			}
			if (!$firstname) {
				$wpus->AppendMessage("Error: required field first name is empty.");
			}
			if (!$lastname) {
				$wpus->AppendMessage("Error: required field last name is empty.");
			}
			if (!$email) {
				$wpus->AppendMessage("Error: required field e-mail address is empty.");
			}
			if (count($projects) == 0) {
				$wpus->AppendMessage("Error: no projects were selected.");
			}
		}
		
		if (!$wpus->HasMessages()) {
			// generate the order
			$fields = array(
				'ordernum' => $ordernum,
				'firstname' => $firstname,
				'lastname' => $lastname,
				'email' => $email,
			);
			
			$query = $wpdb->insert($wpus->sales, $fields);
			$saleID = $wpdb->insert_id;

			// generate the codes for the order
			$codes = array();
			foreach ($projects as $k => $projectID) {
				// Load the project and generate a code
				$p = wpus_project($projectID);
				$c = $p->GenerateCodes(1);
				
				// get the code ID from the returned array of things
				foreach ($c as $codeID => $codeCode) {
					$fields = array(
						'code_id' => $codeID,
						'sale_id' => $saleID
					);
					
					$query = $wpdb->insert($wpus->sale_codes, $fields);
				}
			}
			
			wp_redirect(wpus_admin_url(array('page' => WPUS_PAGES_SALES_PAGE)));
			
		}
		
	}
	elseif (isset($_POST['wpus-order-send'])) {
		/*
		 * Send the order notification e-mail
		 */
		if (isset($_POST['wpus-order-id'])) {
			$orderID = $_POST['wpus-order-id'];
			
			if (!is_numeric($orderID)) {
				$wpus->AppendMessage("Error: Order ID in send request is not a number.");
			}
			else {
				$sql = "SELECT ordernum,email,firstname,lastname FROM $wpus->sales WHERE $wpus->sales.id=%d";
				$query = $wpdb->prepare($sql, $orderID);
				
				if ( ! $row = $wpdb->get_row( $query ) ) {
					$wpus->AppendMessage("Error: Order ID $orderID does not exist.");
				}
				else {
					$mail = $row->email;
					$name = $row->firstname . " " . $row->lastname;
					$ordernum = $row->ordernum;
					
					if (wpus_send_order_email($orderID, $ordernum, $name, $mail)) {
						$wpus->AppendMessage("Sent order e-mail to $name <$mail> for order number $ordernum.", false);
					}
					else {
						$wpus->AppendMessage("Failed to send order e-mail to $name <$mail> for order number $ordernum.");
					}
				}
			}
		}
	}
	elseif (isset($_POST['wpus-history-submit'])) {
		/*
		 * Redirect to code history page with GET
		 */
		if (isset($_POST['wpus-history-code'])) {
			$code = trim($_POST['wpus-history-code']);
			if (!empty($code)) {
				wp_redirect(wpus_admin_url(array('page' => WPUS_PAGES_CODE_PAGE, 'code' => $code)));
			}
		}
	}
	elseif (isset($_POST['wpus-configure-save'])) {
		if ($t = wpus_get_request_var('wpus_cdn_server', false)) {
			if (is_array($t)) {
				update_option('unique_downloader_cdn_servers', serialize($t));
				$wpus->AppendMessage("Settings saved.", false);
			}
			else {
				$wpus->AppendMessage("The posted data is not in the expected format.");
			}
		}
		else {
			// the data wasn't posted, so let's assume that the array can be blown away
			update_option('unique_downloader_cdn_servers', serialize(array()));
		}
		
		if ($k = wpus_get_request_var('wpus_square_api_token', false)) {
			update_option('unique_downloader_square_api_token', $k);
		}
		else {
			update_option('unique_downloader_square_api_token', '');
		}
		
	}
}

add_action('wp_ajax_wpus_reconcile_square', 'wpus_reconcile_square');

function wpus_reconcile_square () {
	global $wpdb, $wpus;

	// the output is totally going to be valid JSON
	header("Content-Type: application/json");
	
	// initialize cURL
	if (!$ch = curl_init()) {
		echo json_encode("cURL library is not available. I'm so sorry.");
		exit();	// must die to prevent weird output
	}
	
	// get the list of purchases from the last month
	$startDate = new DateTime("now");
	$startDate->sub(new DateInterval('P1M'));
	
	// get the payment date in ISO 8601 format (well, just the year-month-date)
	$startDateISO = $startDate->format('Y-m-d');
	$url = "https://connect.squareup.com/v1/me/payments?begin_time=$startDateISO";
	curl_setopt($ch, CURLOPT_URL, $url);
	
	// need a token for auth
	$bearerHeader = 'Authorization: Bearer ' . $wpus->GetSquareAPIToken();
	$headers = array($bearerHeader, "Accept: application/json");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	// return the output in a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	// run the curl request
	if (!$result = curl_exec($ch)) {
		$err = curl_error($ch);
		curl_close($ch);
		
		echo json_encode(array(
			'result' => $result,
			'err' => $err,
		));
		
		exit();	// must die to prevent weird output
	}
	
	// process the returned sales
	$v = json_decode($result);
	$paymentsToReconcile = array();
	$html = '';
	foreach ($v as $squarePayment) {
		$isWebOrder = false;	// assume this order wasn't placed on the web
		
		$paymentID = $squarePayment->id;
		$createdAt = $squarePayment->created_at;
		$totalCollectedMoney = ($squarePayment->total_collected_money->amount) / 100;
		
		foreach ($squarePayment->tender as $tenderNum => $tenderInfo) {
			if ($tenderInfo->entry_method == "WEB_FORM") $isWebOrder = true;
		}
		
		// add this to the list of payments to reconcile if it is an online order
		if ($isWebOrder) {
			$paymentsToReconcile[] = $paymentID;
		}
	}
	
	// look for the payments in the sales database
	$paymentsToAdd = array();
	foreach ($paymentsToReconcile as $pID) {
		$sql = "SELECT COUNT(*) FROM $wpus->sales WHERE `square_id`=%s";
		$numCodesMatchingPayment = $wpdb->get_var($wpdb->prepare($sql, $pID));
		
		if ($numCodesMatchingPayment == 0) {
			$paymentsToAdd[] = $pID;
		}
	}	
	
	// Now, let's see if there are still any payments to add
	if (count($paymentsToAdd) > 0) {
		$html .= "<form method=\"post\" action=\"" . wpus_admin_url( array( 'page' => WPUS_PAGES_SALES_PAGE ) ) . "\" id=\"wpus-square-orders-reconcile-form\">\n";
		
		// there are payments to add, so do that
		foreach ($paymentsToAdd as $pID) {
			$url = "https://connect.squareup.com/v1/me/payments/" . $pID;
			curl_setopt($ch, CURLOPT_URL, $url);
			
			// run the curl request
			if (!$result = curl_exec($ch)) {
				$err = curl_error($ch);
				curl_close($ch);
				
				echo json_encode(array(
					'result' => $result,
					'err' => $err,
				));
				
				exit();	// must die to prevent weird output
			}
			
			// do the needful on the payment object
			$paymentObj = json_decode($result);
			
			/*// generate the codes for the order
			$codes = array();
			foreach ($projects as $k => $projectID) {
				// Load the project and generate a code
				$p = wpus_project($projectID);
				$c = $p->GenerateCodes(1);
				
				// get the code ID from the returned array of things
				foreach ($c as $codeID => $codeCode) {
					$fields = array(
						'code_id' => $codeID,
						'sale_id' => $saleID
					);
					
					$query = $wpdb->insert($wpus->sale_codes, $fields);
				}
			}*/
			// get the items for the sale
			$codesForSale = array();
			foreach ($paymentObj->itemizations as $itemization) {
				// load the project by its sku
				$sku = $itemization->item_detail->sku;
				$qty = $itemization->quantity;
				
				if (false !== ($p = wpus_project_by_sku($sku))) {
					// there is a project for this sku, so generate codes
					$c = $p->GenerateCodes($qty);
					
					$codesForSale[] = $c;
				}
			}
			
			// create a sale for the payment
			$sale = array(
				'square_id' => $paymentObj->id,
				'square_total_collected_money' => $paymentObj->total_collected_money->amount,
			);
			$query = $wpdb->insert($wpus->sales, $sale);
			$saleID = $wpdb->insert_id;
			
			// associate the generated code(s) with this sale
			// the codes for sale is an array of arrays, so need to nest foreach loops
			$finalCodes = array();
			foreach ($codesForSale as $codesForProject) {
				// iterate over the codes for a given project
				foreach ($codesForProject as $codeID => $code) {
					$fields = array(
						'code_id' => $codeID,
						'sale_id' => $saleID,
					);
				
					$query = $wpdb->insert($wpus->sale_codes, $fields);
					$finalCodes[] = $code;
				}
			}
			
			// Since this is a new sale, we need to provide some input boxen for the user to
			// enter the first name, last name, and e-mail address (the Square API doesn't expose these yet)
			$html .= "<div class=\"wpus-new-sale\"><table class=\"widefat\"><thead><tr><th colspan=\"2\">New Sale</th></tr></thead><tbody>\n";
			$html .= "<tr><td>Sale ID:</td><td>" . $saleID . "</td></tr>\n";
			$html .= "<tr><td>Codes:</td><td>" . implode('<br/>', $finalCodes) . "</td></tr>\n";
			$html .= "<tr><td>Square Payment ID:</td><td>" . $paymentObj->id . "</td></tr>\n";
			$html .= "<tr><td>First Name:</td><td><input type=\"text\" name=\"wpus-new-sale[$saleID][firstname]\" value=\"\" /></td></tr>\n";
			$html .= "<tr><td>Last Name:</td><td><input type=\"text\" name=\"wpus-new-sale[$saleID][lastname]\" value=\"\" /></td></tr>\n";
			$html .= "<tr><td>E-mail Address:</td><td><input type=\"text\" name=\"wpus-new-sale[$saleID][email]\" value=\"\" /></td></tr></tbody></table>\n";
			$html .= "<input type=\"hidden\" name=\"wpus-new-sale[$saleID][id]\" value=\"$saleID\" /></div>\n";
			
		}
		
		// finally, add a submit button
		$html .= "<div class=\"actions-link\" style=\"float: right; margin-top: 1em;\"><button type=\"button\" class=\"button button-highlighted\" name=\"wpus-batch-reconcile-payments\" onclick=\"reconcile_square_orders_final();\">Reconcile</button></div>\n";
		$html .= "</form>";
	}
	else {
		$html = "<p>There are no payments to reconcile at this time.</p>";
	}
	
	// close the curl handle
	curl_close($ch);
	echo json_encode(array(
		'html' => $html,
		'paymentsToReconcile' => count($paymentsToAdd),
	));
	
	exit();	// must die to prevent weird output
}

add_action('wp_ajax_wpus_reconcile_square_final', 'wpus_reconcile_square_final');

function wpus_reconcile_square_final () {
	global $wpus, $wpdb;
	
	// the output is totally going to be valid JSON
	header("Content-Type: application/json");
	
	// expecting order info to be POSTed here in the formData field
	if (!isset($_POST['formData'])) {
		$r = array(
			'error' => 'Missing required field formData',
		);
		echo json_encode($r);
		exit();
	}
	
	// grab the form data and parse it into a useful array
	$formData = array();
	parse_str($_POST['formData'], $formData);
	
	// the data we're looking for is in the 'wpus-new-sale' entry
	if (!isset($formData['wpus-new-sale'])) {
		$r = array(
			'error' => 'formData was present, but the new sale data is missing.',
		);
		echo json_encode($r);
		exit();
	}
	
	// copy the new sale data to a new array for ease of handling
	$newSaleData = $formData['wpus-new-sale'];
	if (!is_array($newSaleData)) {
		$r = array(
			'error' => 'New sale data was present, but not in the expected format.',
		);
		echo json_encode($r);
		exit();
	}
	
	// iterate over the posted sales and update the database
	$errors = array();
	foreach ($newSaleData as $saleID => $saleData) {
		// make sure the required fields have been specified
		if (!isset($saleData['firstname']) || !$saleData['firstname']) {
			$errors[] = "Missing first name for sale $saleID";
		}
		if (!isset($saleData['lastname']) || !$saleData['lastname']) {
			$errors[] = "Missing last name for sale $saleID";
		}
		if (!isset($saleData['email']) || !$saleData['email']) {
			$errors[] = "Missing e-mail address for sale $saleID";
		}
		
		// they have (and there are no previous errors), so update this sale
		if (count($errors) == 0) {
			$fields = array(
				'email' => $saleData['email'],
				'lastname' => $saleData['lastname'],
				'firstname' => $saleData['firstname'],
			);
			
			$wpdb->update($wpus->sales, $fields, array ('id' => $saleID));
		}	
	}
	
	if (count($errors) > 0) {
		// there are errors, so alert the user
		$r = array(
			'error' => implode("\n", $errors),
		);
		
		echo json_encode($r);
		exit();
	}

	// there are no errors
	echo json_encode(array('status' => 'OK'));
	exit();
}

function wpus_send_order_email ($orderID, $ordernum, $name, $mail) {
	global $wpus, $wpdb;
	
	$subject = "vtONE Store Order $ordernum";
	$sender = "vtONE Store <store@vt-one.org>";
	$recipient = $name . ' <' . $mail . '>';
	
	$body = "$name,\r\n\r\n";
	$body .= "Thanks for placing an order in the vtONE online store. Below are the download codes for ";
	$body .= "the products you ordered. You can redeem your code(s) at http://digitaldownloads.vt-one.org.\r\n\r\n";
	$body .= "Order Number: $ordernum\r\n";
	
	/*
	 * Pull the codes related to this sale
	 */
	$query = $wpdb->prepare("SELECT $wpus->sale_codes.sale_id,$wpus->projects.name,$wpus->codes.code FROM $wpus->sale_codes INNER JOIN ($wpus->projects INNER JOIN $wpus->codes ON $wpus->projects.id=$wpus->codes.project_id) ON $wpus->sale_codes.code_id=$wpus->codes.id WHERE $wpus->sale_codes.sale_id=%d", $orderID);
	$rows = $wpdb->get_results($query);
	foreach ($rows as $row) {
		$pcode['name'] = $row->name;
		$pcode['code'] = $row->code;
		
		$body .= "$row->name Download Code: $row->code\r\n";
	}
	
	$body .= "\r\nIf you have any questions or have trouble downloading content, please contact us by replying to this e-mail.\r\n\r\n";
	$body .= "----\r\n";
	$body .= "vtONE\r\n";
	$body .= "uniting His campus for His glory.\r\n";
	
	$headers = "From: $sender\r\n";
	$headers .= "Cc: $sender\r\n";
	
	return @wp_mail( $recipient, $subject, $body, $headers );
	
}
