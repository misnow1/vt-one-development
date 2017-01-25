<?php
/**
 * @package WordPress
 * @subpackage Modularity
 */

/*
Template Name: Event Page with Sections
*/
?>


<?php get_header(); ?>

<?php vtone_logo_menu(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<?php vtone_template_sections_menu(); ?>
<?php the_content(); ?>
<?php edit_post_link( __( 'Edit', 'modularity' ), '<div class="clearfix"></div><div class="edit-link">', '</div>'); ?>
<?php endwhile; endif; ?>

<?php get_footer(); ?>
