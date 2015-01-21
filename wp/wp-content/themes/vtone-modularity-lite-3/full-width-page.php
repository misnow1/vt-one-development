<?php
/**
 * @package WordPress
 * @subpackage Modularity
 */
?>
<?php
/*
Template Name: Full-width, no sidebar
*/
?>
<?php get_header(); ?>
<div class="span-24 first last">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
			<?php the_content( __( 'Read the rest of this page &raquo;', 'modularity' ) ); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'modularity' ), 'after' => '</div>' ) ); ?>
		</div>
		<div class="clear"></div>
		<?php endwhile; endif; ?>
	<?php edit_post_link( __( 'Edit', 'modularity' ), '<p>', '</p>'); ?>

<?php get_footer(); ?>
