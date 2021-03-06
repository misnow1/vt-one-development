<div class="wrap wpus">

<h2>Online Sales</h2>

	<form method="post" action="<?php echo wpus_admin_url( array( 'page' => WPUS_PAGES_SALES_PAGE ) ); ?>" id="wpus-admin-sale-new">
	<table class="widefat" style="margin-top: 1em;">
		<thead>
			<tr>
				<th scope="col" colspan=2>New Sale</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<table>
						<tbody>
							<tr>
								<td>Order Number:</td>
								<td><input type="text" name="wpus-order-ordernum" value="<?php echo $ordernum ?>" /></td>
							</tr>
							<tr>
								<td>E-mail Address:</td>
								<td><input type="text" name="wpus-order-email" value="<?php echo $email ?>" /></td>
							</tr>
							<tr>
								<td>Name:</td>
								<td><input type="text" name="wpus-order-realname" value="<?php echo $realname ?>" /></td>
							</tr>
							<tr>
								<td>Projects:</td>
								<td>
									<table>
										<thead>
											<tr>
												<td>Qty</td>
												<td>Project</td>
											</tr>
										</thead>
										<tbody>
									<?php
				foreach ($projects as $p) {
					echo "<tr><td>";
					echo "<input type=\"textbox\" name=\"wpus-order-projects[$p->id]\" value=\"\" maxlength=2 size=4/></td><td>" . $p->name;
					echo "</td></td>\n";
				}
								?>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="actions-link" style="float: right">
							<button type="submit" class="button button-highlighted" name="wpus-create-order">Create Order</button>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	</form>

	<table class="widefat" style="margin-top: 1em;">
	<thead>
	<tr>
		<th scope="col">ID</th>
		<th scope="col">Order Number</th>
		<th scope="col">Name</th>
		<th scope="col">E-mail</th>
		<th scope="col">Projects</th>
		<th scope="col">Entered</th>
		<th scope="col"></th>
	</tr>
	</thead>
	<tbody>

<?php
	foreach ($sales as $s) {
		echo "<tr>\n";
		echo "<td>" . $s->id . "</td>\n";
		echo "<td>" . $s->ordernum . "</td>\n";
		echo "<td>" . $s->realname . "</td>\n";
		echo "<td>" . $s->email . "</td>\n";
		echo "<td>" . join('<br/>', $s->projects) . "</td>\n";
		echo "<td>" . $s->entered . "</td>";
		echo "<td>";
		?>
	<form method="post" action="<?php echo wpus_admin_url( array( 'page' => WPUS_PAGES_SALES_PAGE ) ); ?>" id="wpus-admin-form-element">
		<input type="submit" class="button button-highlighted" name="wpus-order-send" value="Send E-mail" />
		<input type="hidden" name="wpus-order-id" value="<?php echo $s->id ?>" />
	</form>
		<?php
		echo "</td>\n";
		echo "</tr>";
	}
?>

	</tbody>
	</table>
</div>

<div id="reconcile-square-orders" style="display: none;">
	<div id="reconcile-square-inner"></div>
</div>

<script type="text/javascript">
function reconcile_square_orders() {
	var data = {
		action: 'wpus_reconcile_square'
	};

	// show the loading graphic from the thickbox library
	jQuery("body").append("<div id='TB_load'><img src='"+imgLoader.src+"' width='208' /></div>");//add loader to the page
	jQuery('#TB_load').show();//show loader


	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(ajaxurl, data, function(response) {
		if (response.paymentsToReconcile > 0) {
			tb_show("Reconcile Orders", "#TB_inline?inlineId=reconcile-square-orders&modal=true");

			// set the inner div to whatever the AJAX call returns
			jQuery("#reconcile-square-inner").html("<div>" + response.html + "</div>");
		}
		else {
			jQuery('#TB_load').hide(); // hide the loader
			alert("There are no payments to reconcile at this time.");
		}

	});
}

function reconcile_square_orders_final() {
	var data = {
		formData: jQuery("#wpus-square-orders-reconcile-form").serialize(),
		action: 'wpus_reconcile_square_final'
	};

	// show the loading graphic from the thickbox library
	jQuery("body").append("<div id='TB_load'><img src='"+imgLoader.src+"' width='208' /></div>");//add loader to the page
	jQuery('#TB_load').show();//show loader

	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(ajaxurl, data, function(response) {
		if (typeof response.error === "undefined") {
			// ok! reload the page!
			location.reload();
		}
		else {
			// something went wrong. Alert the user and move on.
			alert(response.error);
			return false;
		}

	});
}

</script>
