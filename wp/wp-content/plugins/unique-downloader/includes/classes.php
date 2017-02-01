<?php

//ALTER TABLE wp_unique_downloader_project ADD COLUMN `key` VARCHAR(10) NOT NULL;
//ALTER TABLE `wp_unique_downloader_project` ADD UNIQUE (`key`);
//ALTER TABLE `wp_unique_downloader_code` ADD CONSTRAINT UNIQUE(`code`);

class WPUS_Manager {

	/*
	 * Database tables
	 */
	var $projects;
	var $codes;
	var $attempts;
	var $users;
	var $user_projects;
	var $code_attempts;
	var $sales;
	var $sale_codes;
	var $pwreset_requests;

	var $processing_within;
	var $widget_count;
	var $unit_count;
	var $global_unit_count;
	var $user;
	private $messages;

	/*
	 * Project waiting to be activated
	 */
	var $pendingProjectID = 0;
	var $pendingProjectCode = '';

	protected $cdn_servers = array();
	protected $square_api_token = '';

	function WPUS_Manager() {
		global $wpdb;

		$this->projects = $wpdb->prefix . 'unique_downloader_project';
		$this->codes = $wpdb->prefix . 'unique_downloader_code';
		$this->attempts = $wpdb->prefix . 'unique_downloader_attempt';
		$this->users = $wpdb->prefix . 'unique_downloader_user';
		$this->user_projects = $wpdb->prefix . 'unique_downloader_user_project';
		$this->code_attempts = $wpdb->prefix . 'unique_downloader_code_attempt';
		$this->sales = $wpdb->prefix . 'unique_downloader_sale';
		$this->sale_codes = $wpdb->prefix . 'unique_downloader_sale_code';
		$this->pwreset_requests = $wpdb->prefix . 'unique_downloader_pwreset_request';
        $this->project_assets = $wpdb->prefix . 'unique_downloader_project_asset';
		$this->processing_within = '';
		$this->widget_count = 0;
		$this->unit_count = 0;
		$this->global_unit_count = 0;
		$this->user = false;
		$this->messages = array();
	}

	function LoadMessagesFromSession() {
		session_start();
		if (isset($_SESSION['wpus-messages'])) {
			$this->messages = $_SESSION['wpus-messages'];
			unset($_SESSION['wpus-messages']);
		}
	}
	function ClearMessages () {
		$this->messages = array();
	}

	function PersistMessages () {
		$_SESSION['wpus-messages'] = $this->messages;
	}

	function AppendMessage ($msg, $isError = true) {
		$this->messages[] = new WPUS_Message($msg, $isError);
	}

	function HasMessages () {
		return (count($this->messages) != 0);
	}

	function GetMessages($msg_class = array()) {
		if (is_admin()) {
			return $this->DisplayAdminMessages();
		}
		else {
			return $this->GetBootstrapMessages($msg_class);
		}
	}

	function DisplayAdminMessages() {
		foreach ($this->messages as $msg) {
			if ($msg->IsError()) {
                ?>
				<div class="notice error"><p><?php echo $msg->GetMessage() ?></p></div>
				<?php
			}
			else {
				?>
				<div class="notice updated"><p><?php echo $msg->GetMessage() ?></p></div>
				<?php
    		}
        }
	}

	function GetBootstrapMessages ($msg_class = array()) {
		$str = '';

		foreach ($this->messages as $msg) {
			// use a temporary array to potentially add an error message class
			$t = $msg_class;

			if ($msg->IsError()) {
				array_push($t, 'wpus-error-message');
                $str .= "<div class=\"alert alert-danger\" role=\"alert\">\n";
                $str .= "  <span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span>\n";
                $str .= "  <span class=\"sr-only\">Error:</span>\n";
                $str .= "  " . $msg->GetMessage() . "\n";
                $str .= "</div>\n";
			}
			else {
				array_push($t, 'wpus-message');
                $str .= "<div class=\"alert alert-success\" role=\"alert\">\n";
                $str .= "  <span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span>\n";
                $str .= "  <span class=\"sr-only\">Success:</span>\n";
                $str .= "  " . $msg->GetMessage() . "\n";
                $str .= "</div>\n";
    		}
        }

		return $str;
	}

	function LogEvent($msg, $category) {
		global $wpdb;

		// make sure the message and category are set
		if (!$msg || !$category) {
			die("In LogEvent: Message and category must be set.");
		}

		// some useful variables
		$logTable = $wpdb->prefix . 'unique_downloader_log';
		$now = date('Y-m-d H:i:s');
		$sourceIP = $_SERVER['REMOTE_ADDR'];
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
        //$caller = debug_backtrace()[1]['function'];

		$data = array (
			'when' => $now,
			'ipaddress' => $sourceIP,
			'useragent' => $userAgent,
			'message' => $msg,
			'category' => $category,
		);

		$c = $wpdb->insert($logTable, $data);
		if ($c === false) {
            //error_log("WPUS::LogEvent: Error while running query: " . print_r($wpdb->last_query, True));
            //error_log("WPUS::LogEvent: Traceback: " . print_r(debug_backtrace(), true));
        }

        $line = "$sourceIP \"$userAgent\" $caller $category $msg";
		error_log($line);

        return true;

	}

	function GetCDNServers($withBlank = false) {
		if ($t = get_option('unique_downloader_cdn_servers', false)) {
			// a value was set, unserialize the array and move on
			$this->cdn_servers = unserialize($t);

			if ($withBlank) {
				// add a blank element to the beginning
				array_unshift($this->cdn_servers, '');
			}
		}
		else {
			// no value, return an empty array
			$this->cdn_servers = array();
		}

		return $this->cdn_servers;
	}

	function GetSquareAPIToken () {
		$this->square_api_token = get_option('unique_downloader_square_api_token', '');

		return $this->square_api_token;
	}

	function SetPendingCode ($projectID, $code) {
		$this->pendingProjectID = $projectID;
		$this->pendingProjectCode = $code;
	}

	function ClearPendingCode () {
		$this->pendingProjectID = 0;
		$this->pendingProjectCode = '';
	}

	function GetPendingCode () {
		if ($this->pendingProjectCode == '') return false;
		return $this->pendingProjectCode;
	}

	function GetPendingProjectID () {
		if ($this->pendingProjectID == 0) return false;
		return $this->pendingProjectID;
	}

	function AddPendingProjectToActiveUser() {
		global $wpdb;

		// check for required things
		if ($this->GetPendingCode() === false) return false;
		if (!$this->user) return false;

		// the variables!
		$projectID = $this->GetPendingProjectID();
		$code = $this->GetPendingCode();

		// logging!
		$this->LogEvent("AddPendingProjectToActiveUser: Adding project $projectID (code $code) to user " . $this->user->email . ".", WPUS_LOG_USER_ADD_CODE);

		// check for some particularly ugly errors
		if ($projectID < 0) {
			die("In wpus_add_project_to_active_user: $projectID is not a valid project ID.");
		}
		if ($code == '') {
			die("In wpus_add_project_to_active_user: $code is not a valid code.");
		}
		if (!($this->user instanceof WPUS_User)) {
			die("In wpus_add_project_to_active_user: The user is not an instance of WPUS_User.");
		}

		$userID = $this->user->id;

		$fields = array(
				'user_id' => $userID,
				'project_id' => $projectID,
				'code' => $code,
				'ipaddress' => $_SERVER['REMOTE_ADDR'],
				'when' => date('Y-m-d H:i:s', time()),
		);

		// make sure the project is already associated with this user
		$query = $wpdb->prepare( "SELECT * FROM $this->user_projects WHERE user_id=%d AND project_id=%d", $userID, $projectID );

		if (! $row = $wpdb->get_row( $query ) ) {
			// the user does not already have this project, so insert a row into the DB
			if (false !== $wpdb->insert($this->user_projects, $fields)) {
			    // logging!
			    $this->LogEvent("Successfully added project $projectID (code $code) to user " . $this->user->email . ".", WPUS_LOG_USER_ADD_CODE);

			    $this->ClearPendingCode();

                // update the remaining uses counter for this code
			    if (wpus_register_code($code)) {
                    return true;
                }
            }
            else {
                $wpdb->print_error();
                $this->LogEvent("Database update failed to add info " . print_r($fields, true), WPUS_LOG_USER_ADD_CODE);
            }
		}
		else {
			// logging!
			$this->LogEvent("Warning: Project $projectID (code $code) is already available for " . $wpus->user->email . ", not adding.", WPUS_LOG_USER_ADD_CODE);
            $this->ClearPendingCode();
            return true;
		}

		return false;

	}

}

class WPUS_Message {
	var $msg;
	var $isError = true;

	function WPUS_Message($msg, $isError = true) {
		$this->msg = $msg;
		$this->isError = $isError;
	}

	function GetMessage() {
		return $this->msg;
	}

	function IsError() {
		return $this->isError;
	}
}

class WPUS_Project {
	var $initial = true;

	var $id = 0;

	var $name = '';
	var $enabled = 1;
	var $image_url = '';
	var $cdn_server = '';
	var $cdn_url = '';
	var $cdn_url2 = '';
	var $date_available = '';
    var $date_available_date = '';
	var $key = '';
	var $sku = '';
    var $assets = array();
    var $is_available = false;

	/**
	 * Loads the project from an associative array from the database
	 * Enter description here ...
	 * @param array $row
	 */
	function LoadFromRow ($row) {
		// the row must be an array
		if (!is_array($row)) return false;

		// copy the row into the needful
		foreach ($row as $key => $value) {
			$this->$key = $value;
		}

        // determine if the project is available for download
        $this->is_available = false;    // assume false
        if (!$this->date_available) {
            // it has or no date was set
            $this->is_available = true;
        }
        else {
            $projectAvailableTS = strtotime($this->date_available);
            if (time() >= $projectAvailableTS) {
                $this->is_available = true;
            }

            $this->date_available_date = date('M j, Y', $projectAvailableTS);
        }

        $this->FakeLoadAssets();
	}

    function FakeLoadAssets() {
        $asset = new WPUS_ProjectAsset();
        $asset->LoadFromRow(array(
            'id' => 0,
            'project_id' => $this->id,
            'cdn_server' => $this->cdn_server,
            'cdn_url' => $this->cdn_url,
            'date_available' => $this->date_available,
            'name' => 'MP3 Version',
            'enabled' => $this->enabled,
            'image_url' => $this->image_url,
            'order' => 1,
        ));

        $this->assets[$asset->id] = $asset;

        if ($this->cdn_url2) {
            $asset = new WPUS_ProjectAsset();
            $asset->LoadFromRow(array(
                'id' => 1,
                'project_id' => $this->id,
                'cdn_server' => $this->cdn_server,
                'cdn_url' => $this->cdn_url2,
                'date_available' => $this->date_available,
                'name' => 'FLAC Version',
                'enabled' => $this->enabled,
                'image_url' => $this->image_url,
                'order' => 2,
            ));
            $this->assets[$asset->id] = $asset;
        }
    }

    function LoadAssets() {
        global $wpdb, $wpus;

        $query = $wpdb->prepare( "SELECT * FROM $wpus->projects WHERE id = %d", $id );

        while ($row = $wpdb->get_row($query, ARRAY_A)) {
            $asset = new WPUS_ProjectAsset();
            $asset->LoadFromRow($row);

            $assets[$asset['id']] = $asset;
        }
    }

    function GetAssetByID($id) {
        if (array_key_exists($id, $this->assets)) {
            return $this->assets[$id];
        }
        return false;
    }

	/**
	 * Generates download codes for the project
	 * Codes are now in the format KEY-dddddddddd where d is a digit, 0-9
	 * @param $num The number of codes to generate
	 */
	function GenerateCodes ($num) {
		global $wpus, $wpdb;

		if ($num <= 0) return false; // need to generate at least one code!

		if ($this->key == '') return false; // need a key to generate codes

		$codes = array();
		$counter = 0;
		while ($counter < $num) {
			$code = $this->key . str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_RIGHT) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_RIGHT);

			$fields = array(
				'project_id' => $this->id,
				'code' => $code,
				'remaining_uses' => 1,
				'enabled' => 1,
			);

			if ($wpdb->insert($wpus->codes, $fields)) {
				// add this to the list of successful codes
				$codeID = $wpdb->insert_id;
				$codes[$codeID] = $code;

				// increment the counter if the save was successful
				$counter++;
			}
		}

		// return all of the codes
		return $codes;
	}

    /**
     * Returns a welcome panel for the current project
     * @return string
     */
    function GetWelcomePanel() {
        $url = array();
        $url['Download'] = wpus_redirect_url(array('item' => $this->id));

        if ($this->enabled != 1) return '';

        return $this->GetPanel($url);
    }

    /**
     * Returns a download panel (that will eventually lead to assets for a project)
     * @return string
     */
    function GetAssetsPanel() {
        if ($this->enabled != 1) {
            $wpus->AppendMessage("The project you attempted to download has been administratively disabled.  Please contact the administrator.");
            return '';
        }

        foreach ($this->assets as $asset) {
            $url["Download " . $asset->name] = $asset->GetInterstitialDownloadLink();
        }
        return $this->GetPanel($url);
    }

    /**
     * Returns a pretty panel of URLs
     * @return string
     */
    function GetPanel($urls = array()) {
        $form = "<div class=\"panel panel-default\">\n";
        $form .= "  <div class=\"panel-heading\"><h2 class=\"panel-title\">" . $this->name . "</h2></div>\n";
        $form .= "  <div class=\"panel-body\">\n";
        $form .= "    <div class=\"wpus-download-image-wrapper\">\n";

        $form .= "      <img src=\"" . $this->image_url . "\" alt=\"Project Image\" height=\"100px\" width=\"100px\" style=\"float: none; padding: 5px;\"/>\n";
        $form .= "    </div>\n";

        $form .= "    <div class=\"wpus-download-link-group\">\n";
        if ($this->is_available) {
            foreach ($urls as $description => $url) {
                $form .= "      <a href=\"$url\" class=\"wpus-download-link\">$description</a>\n";
            }
        }
        else {
            $form .= "      Available " . $this->date_available_date . "\n";
        }

        $form .= "    </div> <!-- end download-link-wrapper -->\n";
        $form .= "  </div> <!-- end panel-body -->\n</div> <!-- end panel -->\n";

        return $form;
    }

}

class WPUS_ProjectAsset {
    var $id = 0;
    var $project_id = 0;
    var $cdn_server = '';
    var $cdn_url = '';
    var $date_available = 0;
    var $name = '';
    var $enabled = false;
    var $image_url = '';
    var $order = 1;

    /**
     * Loads the asset from an associative array from the database
     * Enter description here ...
     * @param array $row
     */
    function LoadFromRow ($row) {
        // the row must be an array
        if (!is_array($row)) return false;

        // copy the row into the needful
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
    }

    function GetInterstitialDownloadLink() {
        $url = wpus_redirect_url(array('item' => $this->project_id, 'asset' => $this->id));
        return $url;
    }

    function GetSignedDownloadLink() {
        $url = getSignedURL("http://" . $this->cdn_server . "/" . $this->cdn_url, 1200);
        return $url;
    }
}

class WPUS_ProjectCode {
	var $id = 0;
	var $project_id = 0;
	var $code = '';
	var $remaining_uses = 0;
	var $enabled = 0;
	var $date_available = 0;
}

class WPUS_Sale {
	var $id = -1;
	var $email = '';
	var $realname = '';
	var $ordernum = '';
	var $square_id = '';
	var $square_total_collected_money = 0;
	var $entered = 0;

	var $projects = array();
}

class WPUS_PWResetRequest {
	var $id = -1;
	var $email = -1;
	var $code = '';
	var $expires = '';
}

function wpus_code($id) {
	global $wpdb, $wpus;

	$query = $wpdb->prepare( "SELECT * FROM $wpus->codes WHERE id = %d", $id );

	if ( ! $row = $wpdb->get_row( $query ) )
		return false; // No data

	$unique_downloader_project_code = new WPUS_ProjectCode();
	$unique_downloader_project_code->id = $row->id;
	$unique_downloader_project_code->code = $row->code;
	$unique_downloader_project_code->remaining_uses = $row->remaining_uses;
	$unique_downloader_project_code->enabled = $row->enabled;

	return $unique_downloader_project_code;
}

function wpus_code_by_code ($code) {
	global $wpdb, $wpus;

	$query = $wpdb->prepare( "SELECT * FROM $wpus->codes WHERE code = '%s'", $code );

	if ( ! $row = $wpdb->get_row( $query ) )
		return false; // No data

	$unique_downloader_project_code = new WPUS_ProjectCode();
	$unique_downloader_project_code->id = $row->id;
	$unique_downloader_project_code->project_id = $row->project_id;
	$unique_downloader_project_code->code = $row->code;
	$unique_downloader_project_code->remaining_uses = $row->remaining_uses;
	$unique_downloader_project_code->enabled = $row->enabled;

	return $unique_downloader_project_code;
}

function wpus_project( $id ) {
	global $wpdb, $wpus;

	$query = $wpdb->prepare( "SELECT * FROM $wpus->projects WHERE id = %d", $id );

	if ( ! $row = $wpdb->get_row( $query, ARRAY_A ) )
		return false; // No data

	$unique_downloader_project = new WPUS_Project();
	$unique_downloader_project->LoadFromRow($row);

	return $unique_downloader_project;
}

function wpus_project_by_sku($sku) {
	global $wpdb, $wpus;

	$query = $wpdb->prepare( "SELECT * FROM $wpus->projects WHERE sku = %s", $sku );

	if ( ! $row = $wpdb->get_row( $query, ARRAY_A ) )
		return false; // No data

	$unique_downloader_project = new WPUS_Project();
	$unique_downloader_project->LoadFromRow($row);

	return $unique_downloader_project;
}


function wpus_project_default_pack( $locale = null ) {
	$unique_downloader_project = new WPUS_Project();
	$unique_downloader_project->initial = true;

	$unique_downloader_project->name = __( 'Untitled', 'wpus' );
	$unique_downloader_project->enabled = 1;

	return $unique_downloader_project;
}

function wpus_get_current_unique_downloader_project() {
	global $wpus_project;

	if ( ! is_a( $wpus_project, 'WPUS_Project' ) )
		return null;

	return $wpus_project;
}

function wpus_is_posted() {
	if ( ! $unique_downloader_project = wpus_get_current_unique_downloader_project() )
		return false;

	return $unique_downloader_project->is_posted();
}

function wpus_codes_for_project($project) {
	global $wpdb, $wpus;

	if (!is_a($project, 'WPUS_Project'))
		return false;

	$codes = array();
	$id = $project->id;
	$query = $wpdb->prepare( "SELECT $wpus->codes.*, $wpus->users.realname FROM $wpus->codes LEFT JOIN ($wpus->users INNER JOIN $wpus->user_projects ON $wpus->users.id=$wpus->user_projects.user_id) ON $wpus->codes.code=$wpus->user_projects.code WHERE $wpus->codes.project_id = %d ORDER BY $wpus->codes.remaining_uses DESC,$wpus->users.realname ASC", $id );

	if ($rows = $wpdb->get_results($query)) {
		foreach ($rows as $row) {
			$cc = $row->code;
			$href = wpus_admin_url(array('page' => WPUS_PAGES_CODE_PAGE, 'code' => $cc));

			$code = array(
				'id' => $row->id,
				'code' => $cc,
				'href' => "<a href=\"$href\">$cc</a>",
				'remaining_uses' => $row->remaining_uses,
				'enabled' => $row->enabled,
				'user' => $row->realname,
			);

			$codes[] = $code;
		}
	}

	return $codes;
}

function wpus_create_pwreset_request ($email) {
	global $wpdb, $wpus;

	/*
	 * Set expiration to +10 minutes
	 */
	$expires = new DateTime();
	$expires->add(new DateInterval("PT10M"));
	$expiresSQL = $expires->format('Y-m-d H:i:s');

	/*
	 * Generate the code using a very sophisticated algorithm. Smash 2 pseudo-random numbers onto
	 * the e-mail address. It's a predictable pattern, but less intense than using OpenSSL's RNG and
	 * hopefully not so predictable that it's easy to guess what the codes will be.
	 */
	$code = sha1(mt_rand(1, mt_getrandmax()) . $email . mt_rand(1, mt_getrandmax()));

	/*
	 * clean up previous requests from this e-mail address
	 */
	$wpdb->delete($wpus->pwreset_requests, array(
		'email' => $email,
	));

	/*
	 * Insert the current request into the database
	 */
	if ($wpdb->insert($wpus->pwreset_requests, array(
		'code' => $code,
		'expires' => $expiresSQL,
		'email' => $email,
	))) {

		$id = $wpdb->insert_id;	// this isn't working for some reason
		return $code;	// return true
	}

	return false;	//insert failed

}

function wpus_pwreset_request ($id) {
	global $wpdb, $wpus;

	$query = $wpdb->prepare( "SELECT * FROM $wpus->pwreset_requests WHERE id = %d", $id );

	if ( ! $row = $wpdb->get_row( $query ) )
		return false; // No data

	$unique_downloader_pwreset_request = new WPUS_PWResetRequest();
	$unique_downloader_pwreset_request->id = $row->id;
	$unique_downloader_pwreset_request->code = $row->code;
	$unique_downloader_pwreset_request->expires = $row->expires;
	$unique_downloader_pwreset_request->email = $row->email;

	return $unique_downloader_pwreset_request;
}

/**
 * wpus_verify_pwreset_request
 * Given lots of parameters, verifies that the reset request is valid
 * @param string $email
 * @param string $code
 */
function wpus_verify_pwreset_request ($email, $code) {
	global $wpdb, $wpus;

	// get the current time using PHP (so that the time zone is respected)
	$now = new DateTime();
	$nowSQL = $now->format('Y-m-d H:i:s');

	// look for a code that fits!
	$query = $wpdb->prepare( "SELECT * FROM $wpus->pwreset_requests WHERE `email`=%s AND `code`=%s AND `expires` > %s", $email, $code, $nowSQL );

	if ( ! $row = $wpdb->get_row( $query ) )
		return false; // No data

	// the query returned something so the request is clearly valid
	return true;
}

function getSignedURL($resource, $timeout)
{
	//This comes from key pair you generated for cloudfront
	$keyPairId = "APKAIBIYGH2ZZ435NOLA";

	$expires = time() + $timeout; //Time out in seconds
	$json = '{"Statement":[{"Resource":"'.$resource.'","Condition":{"DateLessThan":{"AWS:EpochTime":'.$expires.'}}}]}';

	//Read Cloudfront Private Key Pair
	//$fp=fopen("private_key.pem","r");
	//$priv_key=fread($fp,8192);
	//fclose($fp);

	$priv_key='-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAgQ1FNupCZ4LJd7alxdYgeUOJjD44cjZmZsK0FZuY0DniCSsO
bFPUOXyuMQ7eL34KTVgl6rxoyd52jv2/JKjJUsrrHsaU/WEHT+WRH54gq6vXhfD+
Bd8F6oeeEyeCet11mfxtDC2+TkjGodX1tyLvZbJ8z07EtUeRPfyn4hoLs/IWHXs9
uj+Ck6k+C8wwP3mIWoaXpb7r0LGe/yD1mgH+GRiT5xAo8aOPjFdn7L06J6sbImIq
FwHuRE/FBcrXGbns0O7rNrd+F4dvMsxe3cCdJ7zNAhjH5LHAZFNcmyCiVHlXPPJ0
BRrXx86Az4jlKIKJJKweoZzwP2cDW1RKYfWFnwIDAQABAoIBAEbJ7baSx3waHMMj
GEmuDEAYUOHx22qi9obVtIzJvggySA/5Yz7+uMIT50UXv77TZ3lHqfzZ/q0E74m+
HNRSFaTplBFcoqteRvGHnpR2W7tvVpitOdoknQ0p+QbOvF8DDZg7A+ITUXmFqBdr
0w3zBtiFELtynKpHqJ8U8U4wNU0t6d+RagJD7eSLODPw84GYXLMcBUMCVhSl5WOc
JIZeQKdx3AJ5786S+LPJABa6ekyD8L1ZeI2TV45WsVwTpSd1lvmU92f1NVSoqgaK
1kTIi3CdY6jFswIdNLfoyVXkx+fLlKhfjR03PIjns6oERmDqOGo/XvX7+mh3X0cA
FiQuImkCgYEA3F+609MYQb0im5KFN1QZ3N27vMXR154k32sKwg8cNfEFTxFG4sK0
MuLzmAC+njQXBn0r3+4csNFBPEXxxis+cIN+lq6oVzNKQ3cVmCWsL5f7Zj/s0283
qwBUQkrqM1e550Vo3t45N8D/w3uoFrs9ylQE9pRQMI6lTeTQY6+tEqUCgYEAleoi
0UMnDYFIy8S31xUtFCpuO+hwVD7iRhDafTcBGxIhlh00gqGuM3XMhfEPklomhrtm
dUrivVO8aFQGmsTNxmleWWj4hMkXzRAEBXAr10R6fmDbou/Npusq4dZeOm3B/Zuh
NuPll+hMddUzFsizUi2FpIGyPMHFUjR8IYQAF/MCgYEAnlBjXhNTZL6kIxEyhJn1
bncYjLesVXL12E8Ezn6ebJ32i2PFAdiQLdJe3v8B8ZNIS1AW+esMT3Y0oEE7PHsK
gzfj9AoLQ4HEQw1ExSWjOhm78CvSTd6jJkS5Q1qgPzwxgFSbzyfkAQq0ctHd4l6n
ODf9zMqlhQyk8n2Du2mUM0UCgYBi3f/KTGQz9uBgakLn2PJay0TZw4hZNwOZO8Is
NBtJlCKMUoRv5lrxWy3f48PmPAgOcQa4MgPo4pFtqISWi1Y+FP2BL8Y+JDTLK1XL
lFeFZ4b1U8Fl6oqRG6SzPeH03K/EJmAiyBeBoFTUnR9NVl1Uw+rQPCyk/xG4Dh8T
J2+8WwKBgQDMdoRrAo0/fD2cY91TNc3nlE2p2TvgfK9I4n7QMaj52V9iOPgIa8fV
9GaZZgWZMeq2E3rHyjBnDeMXFp3fD9C4kIw5fwUya1h4q0rkv5hR7qwyCZgDvGQy
p6GBle8YANbiN+fcIwTP4m3GdrQKqXSwEH9fVehuZR13TXC74fdpwA==
-----END RSA PRIVATE KEY-----';

	//Create the private key
	$key = openssl_get_privatekey($priv_key);
	if(!$key)
	{
		echo "<p>Failed to load private key!</p>";
		return;
	}

	//Sign the policy with the private key
	if(!openssl_sign($json, $signed_policy, $key, OPENSSL_ALGO_SHA1))
	{
		echo '<p>Failed to sign policy: '.openssl_error_string().'</p>';
		return;
	}

	//Create url safe signed policy
	$base64_signed_policy = base64_encode($signed_policy);
	$signature = str_replace(array('+','=','/'), array('-','_','~'), $base64_signed_policy);

	//Construct the URL
	#$url = $resource.'?Expires='.$expires.'&Signature='.$signature.'&AWSAccessKeyId='.$keyPairId;
	$url = $resource.'?Expires='.$expires.'&Signature='.$signature.'&Key-Pair-Id='.$keyPairId;

	// Until we can sort out CDN permissions
	//$url = $resource;

	return $url;
}
