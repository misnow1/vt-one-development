<?php

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
		);
	}
	
	/*
	 * Sort the output by the order field
	 */
	usort($sortedItems, 'wpml_items_sorter_inverse');
	
	/*
	 * Start the output
	 */
	$output = '';
	
	foreach ($sortedItems as $item) {
		$order = $item['order'];
		$eventImage = $item['eventImage'];
		$eventName = $item['eventName'];
		$mediaDescription = $item['mediaDescription'];
	?>
	<div class="media-list-item-wrapper">
		<div class="media-list-event-image-wrapper">
			<img class="media-list-event-image" src="<?php echo $eventImage ?>" />
		</div>
		<div class="media-list-item-meta-wrapper">
			<div class="media-list-event-name"><?php echo $eventName ?></div>
			<div class="media-list-description"><?php echo $mediaDescription ?></div>
			<div class="media-list-download-link"><a href="<?php echo $item->guid ?>">Download</a></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php 
	}
	
	return $output;
}

function wpml_items_sorter_inverse ($a, $b) {
	$orderA = $a['order'];
	$orderB = $b['order'];
	
	if ($orderA == $orderB) return 0;
	
	return ($orderA < $orderB) ? 1 : -1;
}

?>
