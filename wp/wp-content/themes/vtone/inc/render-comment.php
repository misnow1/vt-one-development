<?php
/**
 * @package WordPress
 * @subpackage vtONE
 */

/*
 * Comments in the modularity style
 */

function modularity_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>" class="comment-wrapper">
			<div class="comment-meta">
				<?php echo get_avatar( $comment, 75 ); ?>
				<div class="comment-author vcard">
					<strong class="fn"><?php comment_author_link(); ?></strong>
				</div><!-- .comment-author .vcard -->
			</div>
			<div class="comment-entry">
				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em><?php _e( 'Your comment is awaiting moderation.', 'modularity' ); ?></em>
					<br />
				<?php endif; ?>
				<?php comment_text(); ?>
				<p class="post-time">
					<?php
						/* translators: 1: date, 2: time */
						printf( __( '%1$s at %2$s', 'modularity' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'modularity' ), ' ' );
					?>
					<br />
				</p>
				<div class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</div><!-- .reply -->
			</div>
	</div><!-- #comment-##  -->

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="pingback">
		<p><?php _e( 'Pingback:', 'modularity' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'modularity'), ' ' ); ?></p>
	<?php
			break;
	endswitch;
}
