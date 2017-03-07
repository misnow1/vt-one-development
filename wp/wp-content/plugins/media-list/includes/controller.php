<?php

add_action('wp_enqueue_scripts','wpml_enqueue_scripts');

function wpml_enqueue_scripts () {
	wp_enqueue_style('bootstrap-theme');
}


add_shortcode( 'media-list', 'wpml_handle_shortcode' );

function wpml_handle_shortcode ( $atts ) {
	if ( is_feed() )
		return '[media-list]';

	$typeKey = '';
	if (isset($atts['typekey'])) {
		$typeKey = $atts['typekey'];
	}

	/*
	 * Get the list of things to display
	 */
	$items = get_posts(array(
		'posts_per_page' => 20,
		'meta_key' => '_wpml_type_key',
		'meta_value' => $typeKey,
		'post_type' => 'attachment',
	));

	/*
	 * Stuff the items into a more useful array
	 */
	$sortedItems = array();
	foreach ($items as $item) {
		$order = get_post_meta($item->ID, '_wpml_order', true);
		$eventImage = get_post_meta($item->ID, '_wpml_event_image', true);
		$eventName = get_post_meta($item->ID, '_wpml_event_name', true);
		$mediaDescription = get_post_meta($item->ID, '_wpml_media_description', true);

		$sortedItems[] = array(
			'order' => $order,
			'eventImage' => $eventImage,
			'eventName' => $eventName,
			'mediaDescription' => $mediaDescription,
			'guid' => $item->guid,
		);
	}

	/*
	 * Sort the output by the order field
	 */
	usort($sortedItems, 'wpml_items_sorter_inverse');

	/*
	 * Start the output
	 */
	?>
	<div class="row">
	<?php
	$counter = 1;
	foreach ($sortedItems as $item) {
		$order = $item['order'];
		$eventImage = $item['eventImage'];
		$eventName = $item['eventName'];
		$mediaDescription = $item['mediaDescription'];
	?>

	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo $eventName ?></h3>
			</div>
			<div class="panel-body">
				<img class="img-rounded img-responsive" alt="<?php echo $eventName ?>" src="<?php echo $eventImage ?>" />
				<p><?php echo $mediaDescription ?></p>
				<p><a href="<?php echo $item['guid'] ?>">Download</a>
				<br/>
				<span class="save-media" style="font-style: italic">(To save the audio file, right-click the link and select Save Link As)</span></p>
			</div>
		</div> <!-- end panel -->
	</div> <!-- end column -->

	<?php
	}
	?>
	</div> <!-- end row -->
	<?php
}

function wpml_items_sorter_inverse ($a, $b) {
	$orderA = $a['order'];
	$orderB = $b['order'];

	if ($orderA == $orderB) return 0;

	return ($orderA < $orderB) ? 1 : -1;
}

?>
