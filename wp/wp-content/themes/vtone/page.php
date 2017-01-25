<?php
/**
 * @package WordPress
 * @subpackage vtONE Modularity v3
 */
?>
<?php get_header(); ?>

<?php vtone_logo_menu(); ?>

<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">

    <div class="container panel page-content">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="page-header">
			<h1 class="page-title" id="title"><?php the_title(); ?></h1>
		</div>
		<div class="col-md-9" role="main">
			<?php the_content(); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'modularity' ), 'after' => '</div>' ) ); ?>
		</div>
        <div class="col-md-3 sidebar-wrapper" role="complementary">
            <nav class="page-sidebar hidden-print hidden-xs hidden-sm affix-top" data-spy="affix" data-offset-top="185">
                <?php get_sidebar_menu(); ?>
            </nav>
        </div>
        <?php edit_post_link( __( 'Edit', 'modularity' ), '<div class="clearfix"></div><div class="edit-link">', '</div>'); ?>
	</div> <!-- end container -->
	<?php endwhile; endif; ?>
</div> <!-- end post -->

<?php //if ( comments_open() ) comments_template(); ?>

<!-- Begin Footer -->
<?php get_footer(); ?>
