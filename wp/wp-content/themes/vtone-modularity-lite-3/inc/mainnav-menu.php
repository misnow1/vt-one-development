<?php
/**
 * Create HTML list of nav menu items.
 *
 * @package WordPress
 * @since 3.0.0
 * @uses Walker
 */
class Walker_Slider_Nav_Menu extends Walker {
	/**
	 * levelSubsCount
	 * Used to determine if a level has submenus (and whether or not to display the arrow)
	 * @var unknown_type
	 */
	var $levelSubsCount = 0;
	
	/**
	 * @see Walker::$tree_type
	 * @since 3.0.0
	 * @var string
	 */
	var $tree_type = array( 'post_type', 'taxonomy', 'custom' );

	/**
	 * @see Walker::$db_fields
	 * @since 3.0.0
	 * @todo Decouple this.
	 * @var array
	 */
	var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );

	/**
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function start_lvl(&$output, $depth) {
		$output .= "<div class=\"menu-down-arrow\" style=\"display: none; opacity: 0\"></div>\n";
		
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<dd class=\"sub-menu-depth-$depth\"><ul>\n";
	}

	/**
	 * @see Walker::end_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function end_lvl(&$output, $depth) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
		$output .= "</dd>\n";
	}

	/**
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	function start_el(&$output, $item, $depth, $args) {
		global $wp_query;
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$class_names = $value = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'slider-depth-' . $depth;
		$classes[] = 'menu-item-' . $item->ID;

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = ' class="' . esc_attr( $class_names ) . '"';
		
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
		$id = strlen( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';

		if ($depth == 0) {
			$output .= "<dl class=\"slider-upper\">\n";
			$output .= "<dt class=\"slider-upper\">\n";
		}
		else {
			$output .= $indent . '<li' . $id . $value . $class_names . $style . '>';
		}

		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

		$item_output = $args->before;
		$item_output .= '<a'. $attributes .'>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_slider_menu_start_el', $item_output, $item, $depth, $args );
		
		if ($depth == 0) {
			$output .= "</dt>\n";
		}
	}

	/**
	 * @see Walker::end_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 */
	function end_el(&$output, $item, $depth) {
		if ($depth == 0) {
			$output .= "</dl>\n";
		}
		else {
			$output .= "</li>\n";
		}
	}
}

/**
 * Add animation script to the header
 */
function wp_slider_menu_widget_head() {
?>
<script type="text/javascript">

jQuery(document).ready(function($) {
	var sliderMenuConfig = {
		over: showSliderMenu,
		timeout: 200,
		out: hideSliderMenu
	};
	
	$('dl.slider-upper').hoverIntent(sliderMenuConfig);
	
	function showSliderMenu() {
		$(this).addClass('active');
		$(this).children('dd').show().fadeTo(500,1);
		$(this).children('div.menu-down-arrow').show().fadeTo(500,1);
	}
	function hideSliderMenu() {
		$(this).removeClass('active');
		$(this).children('dd').fadeTo(500,0).hide();
		$(this).children('div.menu-down-arrow').fadeTo(500,0).hide();
	}
	
});

</script>
<?php	
}
add_action('wp_head', 'wp_slider_menu_widget_head');

?>