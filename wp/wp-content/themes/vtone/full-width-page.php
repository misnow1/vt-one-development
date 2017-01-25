<?php
/**
 * @package WordPress
 * @subpackage Modularity
 */

/*
Template Name: Full-width, no sidebar
*/
?>


<?php get_header(); ?>

<?php vtone_logo_menu(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<?php the_content(); ?>
<?php edit_post_link( __( 'Edit', 'modularity' ), '<div class="clearfix"></div><div class="edit-link">', '</div>'); ?>
<?php endwhile; endif; ?>

<?php get_footer(); ?>
