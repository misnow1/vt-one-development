<?php
function wpus_admin_management_page() {
	global $wpdb, $wpus;
	
	$projects = wpus_projects();

	$unsaved = false;
	
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
	
	if (is_a($cf, 'WPUS_Project')) {
		$codes = wpus_codes_for_project($cf);
	}
	
	require_once WPUS_PLUGIN_DIR . '/admin/pages/edit.php';
}

function wpus_admin_code_page() {
	global $wpus, $wpdb;
	
	/*
	 * Render the various things about a given code
	 */
	$code = '';
	if (isset($_GET['code'])) {
		$code = trim($_GET['code']);
	}
	
	/*
	 * Main logic for a code is defined
	 */
	$codeID = -1;
	if ($code != '') {
		$sql = "SELECT id FROM $wpus->codes WHERE $wpus->codes.code = '%s'";
		$query = $wpdb->prepare($sql, $code);
		$rows = $wpdb->get_results($query);
		if (count($rows) < 1) {
			$wpus->AppendMessage("The code $code does not exist.");
		}
		elseif (count($rows) > 1) {
			$wpus->AppendMessage("The code $code returned multiple results from the database. This is sort of a problem.");
		}
		else {
			$codeID = $rows[0]->id;
		}
	}
	
	if ($codeID != -1) {
		/*
		 * Code -> Status
		 */
		$codeObj = wpus_code_by_code($code);
		
		
		/*
		 * Code -> Project(s)
		 */
		$sql = "SELECT $wpus->projects.id, $wpus->projects.name FROM $wpus->codes LEFT JOIN $wpus->projects ON $wpus->codes.project_id=$wpus->projects.id WHERE $wpus->codes.code='%s'";
		$query = $wpdb->prepare($sql, $code);
		$rows = $wpdb->get_results($query);
		$projects = array();
		foreach ($rows as $row) {
			$projects[$row->id] = $row->name;
		}
		
		/* 
		 * Project -> User(s)
		 */
		$sql = "SELECT $wpus->users.id,$wpus->users.firstname,$wpus->users.lastname,$wpus->users.email,$wpus->user_projects.code,$wpus->user_projects.ipaddress,$wpus->user_projects.`when` FROM $wpus->user_projects LEFT JOIN $wpus->users ON $wpus->user_projects.user_id=$wpus->users.id WHERE $wpus->user_projects.code='%s'";
		$query = $wpdb->prepare($sql, $code);
		$rows = $wpdb->get_results($query);
		$users = array();
		foreach ($rows as $row) {
			$u = array();
			
			$u['id'] = $row->id;
			$u['name'] = $row->firstname . ' ' . $row->lastname;
			$u['email'] = $row->email;
			$u['ipaddress'] = $row->ipaddress;
			$u['when'] = $row->when;
			
			$users[$row->id] = $u;
		}
		
		/* 
		 * Code -> Sales
		 */
		$sql = "SELECT $wpus->sales.id,$wpus->sales.firstname,$wpus->sales.lastname,$wpus->sales.email,$wpus->sales.ordernum FROM $wpus->sales INNER JOIN $wpus->sale_codes ON $wpus->sales.id=$wpus->sale_codes.sale_id WHERE $wpus->sale_codes.code_id=%d";
		$query = $wpdb->prepare($sql, $codeID);
		$rows = $wpdb->get_results($query);
		$sales = array();
		foreach ($rows as $row) {
			$sale = array();
			
			$s['id'] = $row->id;
			$s['name'] = $row->firstname . ' ' . $row->lastname;
			$s['email'] = $row->email;
			$s['ordernum'] = $row->ordernum;
			
			$sales[$row->id] = $s;
		}
		
		/*
		 * Code -> Registration Attempts
		 */
		$sql = "SELECT $wpus->code_attempts.* FROM $wpus->code_attempts WHERE code='%s' ORDER BY `when` DESC";
		$query = $wpdb->prepare($sql, $code);
		$rows = $wpdb->get_results($query);
		$regAttempts = array();
		foreach ($rows as $row) {
			$r = array();
			
			$r['id'] = $row->id;
			$r['code'] = $row->code;
			$r['ipaddress'] = $row->ipaddress;
			$r['when'] = $row->when;
			$r['status'] = $row->status;
			
			$regAttempts[$row->id] = $r;
		}
		
	}
	
	require_once WPUS_PLUGIN_DIR . '/admin/pages/code.php';
}

function wpus_admin_users_page() {
	global $wpdb, $wpus;
	
	if (isset($_GET['user'])) {
		/*
		 * Show/edit a single user - get a user object from the given ID
		 */
		require_once WPUS_PLUGIN_DIR . '/admin/pages/singleuser.php';
	}
	else {
		/*
		 * Show all users
		 */
		$users = array();
		$query = $wpdb->prepare("SELECT * FROM $wpus->users", array());
		$rows = $wpdb->get_results($query);
		foreach ($rows as $row) {
			$u = new WPUS_User();
			
			$vars = array('id', 'firstname', 'lastname', 'email');
			foreach ($vars as $v) {
				$u->$v = $row->$v;
			}
			
			$u->projects = array();
			
			$users[$u->id] = $u;
		}
		
		/*
		 * Get all projects per user
		 */
		$query = $wpdb->prepare("SELECT $wpus->projects.name AS pname,$wpus->user_projects.user_id,$wpus->user_projects.project_id,$wpus->user_projects.code FROM $wpus->user_projects LEFT JOIN $wpus->projects ON $wpus->user_projects.project_id=$wpus->projects.id", array());
		$rows = $wpdb->get_results($query);
		foreach ($rows as $row) {
			$code = $row->code;
			$href = wpus_admin_url(array('page' => WPUS_PAGES_CODE_PAGE, 'code' => $code));
			
			$users[$row->user_id]->projects[] = "$row->pname (<a href=\"$href\">$code</a>)";
		}
		
		/*
		 * Grab all registered users and projects
		 */
		$user_projects = array();
		$query = $wpdb->prepare("select name, COUNT(project_id) AS c from $wpus->projects LEFT JOIN $wpus->user_projects ON $wpus->projects.id = $wpus->user_projects.project_id GROUP BY project_id", array());
		$rows = $wpdb->get_results($query);
		foreach ($rows as $row) {
			$user_projects[$row->name] = $row->c;
		}
		
		require_once WPUS_PLUGIN_DIR . '/admin/pages/users.php';
	}
}

function wpus_admin_attempts_page() {
	global $wpdb, $wpus;
	
	/*
	 * Show all users
	 */
	$attempts = array();
	$query = $wpdb->prepare("select $wpus->code_attempts.code,$wpus->code_attempts.ipaddress,$wpus->code_attempts.`when`,$wpus->code_attempts.status,$wpus->projects.name AS pname FROM $wpus->code_attempts LEFT JOIN $wpus->projects ON $wpus->code_attempts.status=$wpus->projects.id ORDER BY `when` DESC;", array());
	$rows = $wpdb->get_results($query);
	
	require_once WPUS_PLUGIN_DIR . '/admin/pages/attempts.php';
}

function wpus_admin_sales_page() {
	global $wpdb, $wpus;
	
	/*
	 * Show all sales
	 */
	$sales = array();
	$query = "SELECT id,ordernum,firstname,lastname,email,square_id,square_total_collected_money FROM $wpus->sales";
	$rows = $wpdb->get_results($query);
	foreach ($rows as $row) {
		$s = new WPUS_Sale();
		
		$vars = array('id', 'ordernum', 'firstname', 'lastname', 'email', 'square_id', 'square_total_collected_money');
		foreach ($vars as $v) {
			$s->$v = $row->$v;
		}
		
		$sales[$s->id] = $s;
	}
	
	/*
	 * Pull the codes related to the sales
	 */
	$query = "SELECT $wpus->sale_codes.sale_id,$wpus->projects.name,$wpus->codes.code FROM $wpus->sale_codes INNER JOIN ($wpus->projects INNER JOIN $wpus->codes ON $wpus->projects.id=$wpus->codes.project_id) ON $wpus->sale_codes.code_id=$wpus->codes.id";
	$rows = $wpdb->get_results($query);
	foreach ($rows as $row) {
		$code = $row->code;
		$href = wpus_admin_url(array('page' => WPUS_PAGES_CODE_PAGE, 'code' => $code));
		$sales[$row->sale_id]->projects[] = "$row->name (<a href=\"$href\">$code</a>)";
	}
	
	/*
	 * Show all projects
	 */
	$projects = array();
	$query = "SELECT `id`,`name` FROM $wpus->projects WHERE `enabled`=1";
	$rows = $wpdb->get_results($query);
	foreach ($rows as $row) {
		$p = new WPUS_Project();
		
		$vars = array('id', 'name');
		foreach ($vars as $v) {
			$p->$v = $row->$v;
		}
		
		$projects[] = $p;
	}
		
	/*
	 * Grab all registered users and projects
	 */
	/*$user_projects = array();
	$query = $wpdb->prepare("select name, COUNT(project_id) AS c from $wpus->projects LEFT JOIN $wpus->user_projects ON $wpus->projects.id = $wpus->user_projects.project_id GROUP BY project_id");
	$rows = $wpdb->get_results($query);
	foreach ($rows as $row) {
		$user_projects[$row->name] = $row->c;
	}*/
	
	require_once WPUS_PLUGIN_DIR . '/admin/pages/sales.php';
}

function wpus_admin_configure_page() {
	global $wpdb, $wpus;
	
	$cdn_servers = $wpus->GetCDNServers();
	
	require_once WPUS_PLUGIN_DIR . '/admin/pages/configure.php';
}
