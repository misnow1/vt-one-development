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
    <meta name="viewport" content="width=device-width, initial-scale=1" />

	<title><?php wp_title(); ?></title>
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head(); ?>
</head>

<?php
    $classes = array();
    if (basename(get_page_template()) == 'full-width-page.php') {
        $classes[] = 'full-width-page-wrap';
		$classes[] = 'container-fluid';
    }
	else {
		$classes[] = 'page-wrap';
	}
?>

<body <?php body_class(); ?> data-spy="scroll" data-target=".page-sidebar" style="position:relative;">

<div class="<?php echo implode($classes, ' ') ?>">
