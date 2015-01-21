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
	//register_widget('WP_Current_And_Children_Menu_Widget');
	
	// slideshow picker
	require_once(get_template_directory() . '/inc/slideshow-picker.php');
	
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
	
	/*register_sidebar(array(
		'name' => 'Navigation Menu (deprecated)',
		'id' => 'fixed-nav-menu',
		'description' => 'The menu displayed next to the logo at the top of the page.',
		'before_widget' => '<div id="%1$s" class="item %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="sub">',
		'after_title' => '</h3>',
	));
	
	register_sidebar(array(
		'name' => 'Subpage Menu (deprecated)',
		'id' => 'subpage-menu',
		'description' => 'The menu displayed on posts and pages on the left.',
		'before_widget' => '<div id="%1$s" class="item %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="sub">',
		'after_title' => '</h3>',
	));*/
	
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

// Comments in the Modularity style
function modularity_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>" class="comment-wrapper">
			<div class="comment-meta">
				<?php echo get_avatar( $comment, 75 ); ?>
				<div class="comment-author vcard">
					<strong class="fn"><?php comment_author_link(); ?></strong>
				</div><!-- .comment-author .vcard -->
			</div>
			<div class="comment-entry">
				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em><?php _e( 'Your comment is awaiting moderation.', 'modularity' ); ?></em>
					<br />
				<?php endif; ?>
				<?php comment_text(); ?>
				<p class="post-time">
					<?php
						/* translators: 1: date, 2: time */
						printf( __( '%1$s at %2$s', 'modularity' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'modularity' ), ' ' );
					?>
					<br />
				</p>
				<div class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</div><!-- .reply -->				
			</div>
	</div><!-- #comment-##  -->

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="pingback">
		<p><?php _e( 'Pingback:', 'modularity' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'modularity'), ' ' ); ?></p>
	<?php
			break;
	endswitch;
}

function vtone_modularity_lite_scripts () {
	/*
	 * Core theme styles and such
	 */
	wp_enqueue_style('vtone-modularity-lite', get_stylesheet_uri(), array(), '20140219.01');
	wp_enqueue_style('vtone-modularity-lite-ie', get_stylesheet_directory_uri() . '/../modularity-lite/library/styles/ie.css', array(), null, 'screen, projection');
	wp_style_add_data( 'vtone-modularity-lite-ie', 'conditional', 'IE' );
	wp_enqueue_style('vtone-modularity-lite-ie7', get_stylesheet_directory_uri() . '/../modularity-lite/library/styles/ie-nav.css', array(), null);
	wp_style_add_data( 'vtone-modularity-lite-ie7', 'conditional', 'lte IE 7' );

	/*
	 * NGG overrides
	 */
	wp_enqueue_style('vtone-modularity-lite-ngg-overrides', get_stylesheet_directory_uri() . '/ngg-overrides.css', array(), '20140206.01');
	
	/*
	 * Support for Retina displays with retina.js
	 */
	wp_enqueue_script('retina.js', get_template_directory_uri() . '/js/retina-1.1.0.min.js', array(), '1.1.0', true);
	
	/*
	 * Front page image slider
	 */
	wp_enqueue_script('nivo.slider', get_template_directory_uri() . '/nivo-slider/jquery.nivo.slider.pack.js', array('jquery'), '3.2');
	wp_enqueue_style('nivo.slider', get_template_directory_uri() . '/nivo-slider/nivo-slider.css', array(), '3.2');
	wp_enqueue_style('nivo.slider.theme.bar', get_template_directory_uri() . '/nivo-slider/themes/bar/bar.css', array(), '3.2');
	
	/*
	 * Various awesome menus
	 */
	wp_enqueue_script('hoverIntent');
	
}
add_action('wp_enqueue_scripts', 'vtone_modularity_lite_scripts');

function vtone_modularity_lite_logo_menu ($isSub = false) {
	?>
	<div id="logo-menu-wrapper" <?php echo ($isSub ? 'class="sub"' : '') ?>>
		<div id="logo-menu-inner">
			<div id="logo">
				<a href="<?php bloginfo('url') ?>">
					<img src="<?php bloginfo('stylesheet_directory') ?>/images/vtone_logo_tag.png" data-at2x="<?php bloginfo('stylesheet_directory') ?>/images/vtone_logo_tag@2x.png" alt="vtone logo" />
				</a>
			</div>
			<div id="home-menu">
				<?php wp_nav_menu( array( 'fallback_cb' => '', 
					'theme_location' => 'main_nav_menu', 
					'items_wrap' => '<div id="%1$s" class="%2$s">%3$s</div>',
					'walker' => new Walker_Slider_Nav_Menu() ) ); ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<?php
}

