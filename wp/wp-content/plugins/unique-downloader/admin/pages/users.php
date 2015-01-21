<div class="wrap wpus">

<?php echo wpus_get_messages() ?>

<h2>Project Registration Counts</h2>

	<table class="widefat" style="margin-top: 1em;">
	<thead>
	<tr>
		<th scope="col">Name</th>
		<th scope="col">Count</th>
	</tr>
	</thead>
	<tbody>
	
<?php 
	foreach ($user_projects as $upKey => $upValue) {
		echo "<tr>\n";
		echo "<td>" . $upKey . "</td>\n";
		echo "<td>" . $upValue . "</td>\n";
		echo "</tr>";
	}
?>

	</tbody>
	</table>

<h2>Users</h2>

	<table class="widefat" style="margin-top: 1em;">
	<thead>
	<tr>
		<th scope="col">ID</th>
		<th scope="col">Name</th>
		<th scope="col">E-mail</th>
		<th scope="col">Registered Codes</th>
	</tr>
	</thead>
	<tbody>
	
<?php 
	foreach ($users as $u) {
		echo "<tr>\n";
		echo "<td>" . $u->id . "</td>\n";
		echo "<td>" . $u->firstname . ' ' . $u->lastname . "</td>\n";
		echo "<td>" . $u->email . "</td>\n";
		echo "<td>";
		foreach ($u->projects as $pk => $pp) {
			echo "$pp<br/>\n";
		}
		echo "</td>\n";
		echo "</tr>";
	}
?>

	</tbody>
	</table>
</div>