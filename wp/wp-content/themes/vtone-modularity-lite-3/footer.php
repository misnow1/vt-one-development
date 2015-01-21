<?php
/**
 * @package WordPress
 * @subpackage Modularity
 */
?>

<div class="footer-wrapper">

<div class="footer">

<div class="social-icons">
	<span class="social-icon">
		<a href="http://www.facebook.com/vtoneorg" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/social-icons/facebook.png" data-at2x="<?php bloginfo('stylesheet_directory'); ?>/images/social-icons/facebook@2x.png" alt="facebook icon" /></a>
	</span>
	<span class="social-icon">
		<a href="http://www.twitter.com/#!/vtoneworship" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/social-icons/twitter.png" data-at2x="<?php bloginfo('stylesheet_directory'); ?>/images/social-icons/twitter@2x.png" alt="twitter icon" /></a>
	</span>
</div>

<div class="credits">
	<span class="small quiet">&copy; <?php echo date('Y')?> vtONE, all rights reserved | <a href="credits">credits</a> | <a href="contact">contact us</a></span>
</div>


<div class="clear"></div>

</div> <!-- End footer -->
</div> <!-- End footer-wrapper -->

</div> <!-- End page-wrapper -->

<!-- script to "lazy"-load the background image -->
<script type="text/javascript">
	// once the background image div is ready, fade it in
	jQuery('div#spring-2014-bk div#bk-img').ready(function () {
		jQuery('#spring-2014-bk').fadeTo(3000, 0.3);
	});
</script>

<?php wp_footer(); ?>

</body>

</html>
