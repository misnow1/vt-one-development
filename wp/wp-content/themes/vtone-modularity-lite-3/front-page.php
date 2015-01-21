<?php
require_once('functions.php');

get_header();

?>
<!-- slideshow -->
<div id="slideshow-wrapper">
	<?php vtone_modularity_lite_logo_menu() ?>

	<?php
	// Use the new picker for slideshow images!
	$s = get_option('vtone-modularity-lite-slideshow-images', '');
	if ($s == '') {
		// nothing was stored, return an empty array
		$s = array();
	}
	else {
		// something is available, unserialize it
		$s = unserialize($s);
	}
	
	// sort the images array
	uasort($s, 'slideshowSorter');
	
	?>
	<div class="slider-wrapper theme-bar">
		<div class="ribbon"></div>
		<div id="slider" class="nivoSlider">
		<?php 
		foreach ($s as $key => $obj) {
			// skip disabled images
			if ($obj->enabled != 1) continue;
			
			// get the image URL
			$img = "<img src=\"$obj->url\" width=\"1000\" height=\"425\" alt=\"vtONE\" />";			
			if ($obj->href) {
				// output with linky!
				echo "<a href=\"" . $obj->href . "\">$img</a>\n";
			}
			else {
				echo "$img\n";
			}
		}
		?>
		</div>
	</div>
		
	<script type="text/javascript">
		jQuery(window).load(function() {
		    jQuery('#slider').nivoSlider({
				effect: 'fade',
				slices: 1,
				animSpeed: 500,
				pauseTime: 5000,
				startSlide: 0,
				directionNav: false
			});
		});
	</script>
	<!-- end slideshow -->
</div>

<!-- Headline -->
<div class="headline-wrapper">
<div class="headline">
<?php bloginfo('description') ?>
</div>
</div>

<!-- Content -->
<div class="content-wrapper">

<div class="content-front">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
		<?php the_content( __( 'Read the rest of this page &raquo;', 'modularity' ) ); ?>
		<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'modularity' ), 'after' => '</div>' ) ); ?>
	</div>
	<?php endwhile; endif; ?>
</div>

<!-- Right sidebar (twitter feed?) -->
<div id="sidebar-right-wrapper">
<div id="sidebar-right">
<?php
dynamic_sidebar('sidebar');
?>
</div>
</div>

<div class="clear"></div>

</div> <!-- End content-wrapper -->
<?php 
get_footer();
?>
