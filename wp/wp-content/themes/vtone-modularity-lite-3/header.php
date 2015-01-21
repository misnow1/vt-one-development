<?php
/**
 * @package WordPress
 * @subpackage vtONE Modularity v3
 */
?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width" />

	<title><?php wp_title(); ?></title>
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class('vtone'); ?>>

<div id="page-wrapper">
<div id="header">

<?php 
if (!is_front_page()) {
?>
<!-- Subpage header image -->
<div id="subpage-image-header">
</div>
<!-- Subpage header menu -->
<?php vtone_modularity_lite_logo_menu(true) ?>
<!-- End header menu -->

<?php
}	// end is not front page
?>

</div> <!-- End page header -->

