<?php

function GetSidebarMenu () {
	global $post;
	
	/*
	 * Main logic to render this widget!
	 * Find the parents, sibblings, and children of this page. 
	 * * If the page has children, display the children and bail out.
	 * * If this page has sibblings, display the parent and sibblings.
	 */
	$parentIsActive = false;
	if (is_page()) {
		$pageid = $post->ID;
		
		/*
		 * Look for children
		 */
		$menu = wp_list_pages(array(
			'echo' => 0,
			'child_of' => $pageid,
			'depth' => 1,
			'title_li' => '',
		));
		 
		if (function_exists('vtone_tabgroup_shortcode') && has_shortcode($tmp_content = get_the_content(), 'tabgroup')) {
			// OK, so we have tab groups in this page. Let's make a pretty menu for them
			
			// Infomations on the current page!
			$parentTitle = $post->post_title;
			$parentLink = get_permalink($pageid);
			$parentIsActive = true;
							
			$menu = "<ul class=\"sibbling-menu\">\n";
			
			// ram them through the shortcode processor
			// yes, this has some overhead. I'm sorry.
			$processedContent = do_shortcode($tmp_content);
			$tabGroupsForMenu = vtone_tabs_get_tabgroups();
			
			// Now, reset the tab group processing so we don't double the needful later
			vtone_tabs_reset_tabgroups();
			
			// render the tab links
			foreach ($tabGroupsForMenu as $tabGroupIndex => $tabGroup) {
				foreach ($tabGroup as $tabIndex => $tabName) {
					$menu .= "<li class=\"page_item\"><a href=\"#tabs-$tabGroupIndex-$tabIndex\" onClick=\"makeTabActive($tabGroupIndex, $tabIndex);\" id=\"vtone_tabgroup_" . $tabGroupIndex . "_$tabIndex\">$tabName</a></li>\n";
				}
			}
			
			// and close the menu
			$menu .= "</ul><!-- close tabbed menu -->";
							
			
		}
		elseif ($menu) {
			// display the children
			$parentTitle = $post->post_title;
			$parentLink = get_permalink($pageid);
			$parentIsActive = true;
			
			$menu = "<ul class=\"sibbling-menu\">\n$menu\n</ul>";
			
		}
		elseif ($post->post_parent) {
			// display the parent and sibblings
			
			$parentTitle = get_the_title($post->post_parent);
			$parentLink = get_permalink($post->post_parent);
			
			$menu = "<ul class=\"sibbling-menu\">\n";
			$menu .= wp_list_pages(array(
				'echo' => 0,
				'depth' => 1,
				'child_of' => $post->post_parent,
				'title_li' => '',
			));
			$menu .= "</ul>";
		}
		else {
			// just display the page
			$parentTitle = "Home";
			$parentLink = get_bloginfo('url');
			
			$menu = wp_nav_menu(array(
				'echo' => 0,
				'theme_location' => 'sidebar_menu',
				'depth' => 1,
				'container' => '',
				'menu_class' => 'sibbling-menu',
			));
		}
		
	}

	/*
	 * Look for an active item!
	 */
	$menu = preg_replace('#<a([^>]*)>(' . $post->post_title . ')</a>#si', '<a class="active" $1>$2</a>', $menu);
	
	if ($parentIsActive) {
		$parentClass = "class=\"active\"";
	}
	
	echo "<div class=\"parent-title\"><a $parentClass href=\"$parentLink\">$parentTitle</a></div>";
	if ($menu) echo $menu;
}

/**
 * Add animation script to the header
 */
function wp_current_and_children_menu_widget_head() {
?>
<script type="text/javascript">

jQuery(document).ready(function($) {
	$('.sibbling-menu').fadeIn(1000);
});

</script>
<?php	
}
add_action('wp_head', 'wp_current_and_children_menu_widget_head');

?>