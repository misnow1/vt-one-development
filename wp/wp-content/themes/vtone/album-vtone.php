<?php
/**
 * @package WordPress
 * @subpackage vtONE Modularity v3
 */

/**
 * Template Page for the album overview (extended)
 *
 * Follow variables are useable :
 *
 * $album      : Contain information about the album
 * $galleries  : Contain all galleries inside this album
 * $pagination : Contain the pagination content
 *
 * You can check the content when you insert the tag <?php var_dump($variable) ?>
 * If you would like to show the timestamp of the image ,you can use <?php echo $exif['created_timestamp'] ?>
 */
?>
<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><?php if (!empty ($galleries)) : ?>

<div class="ngg-albumoverview-vtone">
	<!-- List of galleries -->
	<?php foreach ($galleries as $gallery) : ?>

	<div class="ngg-album-vtone">
			<div class="ngg-albumcontent-vtone">
				<div class="ngg-thumbnail-vtone">
					<a href="<?php echo $gallery->pagelink ?>"><img class="Thumb" alt="<?php echo $gallery->title ?>" src="<?php echo $gallery->previewurl ?>"/></a>
				</div>
				<div class="ngg-albumtitle-vtone"><a href="<?php echo $gallery->pagelink ?>"><?php echo $gallery->title ?></a></div>

				<?php if ($gallery->counter > 0) : ?>
				<div class="ngg-photocount-vtone"><?php echo $gallery->counter ?> <?php _e('Photos', 'nggallery') ?></div>
				<?php endif; ?>

				<div class="ngg-description-vtone">
					<?php echo $gallery->galdesc ?>
				</div>
			</div>
	</div>

 	<?php endforeach; ?>

	<!-- Pagination -->
 	<?php echo $pagination ?>

</div>

<?php endif; ?>
