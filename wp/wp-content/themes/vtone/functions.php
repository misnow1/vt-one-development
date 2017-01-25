<?php
/**
 * @package WordPress
 * @subpackage vtONE Modularity v3
 */

/*
 * Original items from the modularity theme by Graph Paper Press
 */

function vtone_modularity_lite_setup () {
	// load the required classes
	require_once(get_template_directory() . '/inc/classes.php');

	// register the menu widget for this theme
	require_once(get_template_directory() . '/inc/mainnav-menu.php');
	require_once(get_template_directory() . '/inc/sidebar-menu.php');
	require_once(get_template_directory() . '/inc/logo-menu.php');

	// slideshow picker
	require_once(get_template_directory() . '/inc/slideshow-picker.php');

	// other support files
	require_once(get_template_directory() . '/inc/render-comment.php');

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Add post thumbnail theme support
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 150, 150, true );

	/*
	 * Main navigation and sidebar menus
	 */
	register_nav_menus( array(
		'main_nav_menu' => 'Main Navigation Menu',
		'sidebar_menu' => 'Sidebar Menu (Fallback Only)'
	));

	/*
	 * Front page sidebar
	 */
	register_sidebar(array(
		'name' => 'Front Page Right Sidebar',
		'id' => 'sidebar',
		'description' => $optional_description,
		'before_widget' => '<div id="%1$s" class="item %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="sub">',
		'after_title' => '</h3>',
	));

}
add_action( 'after_setup_theme', 'vtone_modularity_lite_setup' );

function vtone_modularity_lite_scripts () {
	wp_enqueue_script('bootstrapjs');
	wp_enqueue_style('bootstrap-theme');

	/*
	 * Core theme styles and such
	 */
	wp_enqueue_style('vtone', get_stylesheet_uri(), array(), '20151208.01');

	/*
	 * NGG overrides
	 */
	//wp_enqueue_style('vtone-modularity-lite-ngg-overrides', get_stylesheet_directory_uri() . '/css/ngg-overrides.css', array(), '20140206.01');

	/*
	 * Support for Retina displays with retina.js
	 */
	wp_enqueue_script('retina.js', get_template_directory_uri() . '/js/retina-1.1.0.min.js', array(), '1.1.0', true);

	/*
	 * Front page image slider
	 */
 	if (is_front_page()) {
		wp_enqueue_script('nivo.slider', get_template_directory_uri() . '/nivo-slider/jquery.nivo.slider.pack.js', array('jquery'), '3.2');
		wp_enqueue_style('nivo.slider', get_template_directory_uri() . '/nivo-slider/nivo-slider.css', array(), '3.2');
		wp_enqueue_style('nivo.slider.theme.bar', get_template_directory_uri() . '/nivo-slider/themes/bar/bar.css', array(), '3.2');
	}

	/*
	 * Various awesome menus
	 */
	wp_enqueue_script('hoverIntent');

}
add_action('wp_enqueue_scripts', 'vtone_modularity_lite_scripts');