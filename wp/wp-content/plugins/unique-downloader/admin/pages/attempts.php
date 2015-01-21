<div class="wrap wpus">
<?php echo wpus_get_messages() ?>
<h2>Registration Attempts</h2>

	<table class="widefat" style="margin-top: 1em;">
	<thead>
	<tr>
		<th scope="col">Code</th>
		<th scope="col">IP Address</th>
		<th scope="col">Date/Time</th>
		<th scope="col">Status</th>
	</tr>
	</thead>
	<tbody>
	
<?php 
	foreach ($rows as $row) {
		echo "<tr>\n";
		
		$code = $row->code;
		if (!empty($code)) {
			echo "<td><a href=\"". wpus_admin_url(array('page' => WPUS_PAGES_CODE_PAGE, 'code' => $code)) ."\">" . $code . "</a></td>\n";
		}
		else {
			echo "<td>&nbsp;</td>\n";
		}
		echo "<td>" . $row->ipaddress . "</td>\n";
		echo "<td>" . $row->when . "</td>\n";
		
		$status = $row->status;
		$projectName = $row->pname;
		if ($status < 0) {
			echo "<td><font color=\"red\"><strong>Failed: " . wpus_human_readable_error($status) . "</strong></font></td>\n";
		}
		else {
			echo "<td><font color=\"green\"><strong>Success: $projectName</strong></font></td>\n";
		}
		echo "</tr>";
	}
?>

	</tbody>
	</table>
</div>