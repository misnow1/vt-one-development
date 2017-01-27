<?php
require_once('functions.php');

get_header();

?>
<!-- nav menu -->
<?php vtone_logo_menu() ?>

<!-- event image -->
<?php
$eventLink = get_option('vtone-theme-event-link', '');
$eventImage = get_option('vtone-theme-event-image', '');

if ($eventLink && $eventImage) {
?>
<div id="event-image-wrapper">
	<a href="<?php echo $eventLink ?>"><img id="event-image" src="<?php echo $eventImage ?>" alt="Event Image" /></a>
</div>
<script type="text/javascript">
jQuery(window).on('load', function() {
	jQuery('#event-image-wrapper').height(function() {
		var height = jQuery('#event-image').height();
		console.log("derp");
		console.log(height);
		return height + "px";
	});
});
</script>
<?php
}
?>

<!-- Headline -->
<div class="headline">
<div class="container">
<?php bloginfo('description') ?>
</div>  <!-- end container -->
</div>  <!-- end headline -->

<!-- slideshow -->
<div id="slideshow-wrapper">

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
	<div class="slider-wrapper theme-light">
		<div class="ribbon"></div>
		<div id="slider" class="nivoSlider">
		<?php
		foreach ($s as $key => $obj) {
			// skip disabled images
			if ($obj->enabled != 1) continue;

			// get the image URL
			$img = "<img src=\"$obj->url\" alt=\"vtONE\" />";
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

<!-- Content -->
<div class="content-front">
<div class="container">
    <div class="row">
        <div class="col-md-9" role="main">
        	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        	<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
        		<?php the_content( __( 'Read the rest of this page &raquo;', 'modularity' ) ); ?>
        		<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'modularity' ), 'after' => '</div>' ) ); ?>
        	</div>
        	<?php endwhile; endif; ?>
        </div>
        <div class="col-md-3" role="complimentary">
            <div class="hidden-xs hidden-sm">
                <!-- Right sidebar (twitter feed?) -->
                <?php dynamic_sidebar('sidebar'); ?>
            </div>
        </div>
    </div> <!-- end row -->
</div> <!-- end container -->
</div> <!-- end content-front -->

<?php
get_footer();
?>
