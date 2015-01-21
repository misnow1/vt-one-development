<?php
/**
 * @package WordPress
 * @subpackage vtONE Modularity v3
 */
?>
<?php get_header(); ?>

<div class="content-wrapper">
<div class="content">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
		<h1 class="page-title"><?php the_title(); ?></h1>
		
		<div class="subpage-menu">
		<?php GetSidebarMenu() ?>
		</div>
		
		<div class="page-content">
		<?php the_content( __( 'Read the rest of this page &raquo;', 'modularity' ) ); ?>
		<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'modularity' ), 'after' => '</div>' ) ); ?>
		</div>
		
		<div class="clear"></div>

	</div>
	<?php endwhile; endif; ?>
	<?php edit_post_link( __( 'Edit', 'modularity' ), '<div class="edit-link">', '</div>'); ?>
</div> <!-- End content -->
</div> <!-- End content-wrapper -->

<?php //if ( comments_open() ) comments_template(); ?>

<!-- Begin Footer -->
<?php get_footer(); ?>