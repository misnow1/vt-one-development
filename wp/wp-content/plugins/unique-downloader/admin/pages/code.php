<div class="wrap wpus">

<?php 
if (empty($code)) {
?>
<h1>Code Report</h1>

<form method="post" action="<?php echo wpus_admin_url( array( 'page' => WPUS_PAGES_CODE_PAGE ) ); ?>" id="wpus-admin-form-element">
<p>Enter a code to find the history for it:</p>
<p>Code: <input type="text" name="wpus-history-code" value="" />
<input type="submit" class="button button-highlighted" name="wpus-history-submit" value="Submit" /></p>
</form>

<?php
}
else {
?>
<h1>Code Report for <?php echo $code ?></h1>

<h2>Code Status</h2>

<table class="widefat" style="margin-top: 1em;">
	<thead>
	<tr>
		<th scope="col">ID</th>
		<th scope="col">Code</th>
		<th scope="col">Remaining Uses</th>
		<th scope="col">Enabled</th>

	</tr>
	</thead>

	<tbody>
	<tr>
		<td><?php echo $codeObj->id ?></td>
		<td><?php echo $codeObj->code ?></td>
		<td><?php echo $codeObj->remaining_uses ?></td>
		<td><?php echo (($codeObj->enabled == 1) ? "Yes" : "No") ?></td>
	</tr>
	</tbody>
</table>


<h2>Projects</h2>

<?php
if (count($projects) == 0) {
	echo "<p>This code is not associated with any projects.</p>\n";
}
else {
?>
	<table class="widefat" style="margin-top: 1em;">
	<thead>
	<tr>
		<th scope="col">ID</th>
		<th scope="col">Project Name</th>
	</tr>
	</thead>
	<tbody>
<?php
	foreach ($projects as $pid => $pname) {
		echo "<tr><td>$pid</td><td>$pname</td></tr>\n";
	}
?>

	</tbody>
	</table>
<?php
} // end projects
?>
<br/>
<h2>Registered Users</h2>
<?php
if (count($users) == 0) {
	echo "<p>This code is not associated with any registered users.</p>\n";
}
else {
?>
	<table class="widefat" style="margin-top: 1em;">
	<thead>
	<tr>
		<th scope="col">ID</th>
		<th scope="col">Name</th>
		<th scope="col">E-Mail Address</th>
		<th scope="col">IP Address</th>
		<th scope="col">Date/Time</th>
	</tr>
	</thead>
	<tbody>
<?php
	foreach ($users as $u) {
		echo "<tr>\n";
		echo "<td>" . $u['id'] . "</td>\n";
		echo "<td>" . $u['name'] . "</td>\n";
		echo "<td>" . $u['email'] . "</td>\n";
		echo "<td>" . $u['ipaddress'] . "</td>\n";
		echo "<td>" . $u['when'] . "</td>\n";
		echo "</tr>\n";
	}
?>

	</tbody>
	</table>
<?php
} // end users
?>
<br/>
<h2>Sales</h2>
<?php
if (count($sales) == 0) {
	echo "<p>This code is not associated with any sales.</p>\n";
}
else {
?>
	<table class="widefat" style="margin-top: 1em;">
	<thead>
	<tr>
		<th scope="col">ID</th>
		<th scope="col">Name</th>
		<th scope="col">E-Mail Address</th>
		<th scope="col">Order Number</th>
	</tr>
	</thead>
	<tbody>
<?php
	foreach ($sales as $s) {
		echo "<tr>\n";
		echo "<td>" . $s['id'] . "</td>\n";
		echo "<td>" . $s['name'] . "</td>\n";
		echo "<td>" . $s['email'] . "</td>\n";
		echo "<td>" . $s['ordernum'] . "</td>\n";
		echo "</tr>\n";
	}
?>

	</tbody>
	</table>
<?php
} // end sales


?>
<br/>
<h2>Registration Attempts</h2>
<?php
if (count($regAttempts) == 0) {
	echo "<p>No registration attempts have been made using this code.</p>\n";
}
else {
?>
	<p>Most recent attempts are listed first.</p>

	<table class="widefat" style="margin-top: 1em;">
	<thead>
	<tr>
		<th scope="col">ID</th>
		<th scope="col">Code</th>
		<th scope="col">IP Address</th>
		<th scope="col">Date/Time</th>
		<th scope="col">Status</th>
	</tr>
	</thead>
	<tbody>
<?php
	foreach ($regAttempts as $r) {
		echo "<tr>\n";
		echo "<td>" . $r['id'] . "</td>\n";
		echo "<td>" . $r['code'] . "</td>\n";
		echo "<td>" . $r['ipaddress'] . "</td>\n";
		echo "<td>" . $r['when'] . "</td>\n";

		$status = $r['status'];
		if ($status < 0) {
			echo "<td><font color=\"red\"><strong>Failed: " . wpus_human_readable_error($status) . "</strong></font></td>\n";
		}
		else {
			echo "<td><font color=\"green\"><strong>Success: Project ID $status</strong></font></td>\n";
		}

		echo "</tr>\n";
	}
?>

	</tbody>
	</table>
<?php
} // end registration attempts

}	// end if code given
?>
</div>
