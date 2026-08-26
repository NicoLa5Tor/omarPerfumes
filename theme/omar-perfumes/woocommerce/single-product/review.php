<?php
/**
 * Single product review as a Flowbite-style chat bubble.
 *
 * @package OmarPerfumes
 */

defined( 'ABSPATH' ) || exit;

$commenter_rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
$avatar           = get_avatar_url( $comment, array( 'size' => 64 ) );
$author           = get_comment_author();
?>
<li <?php comment_class( 'perfumes-chat__item' ); ?> id="li-comment-<?php comment_ID(); ?>">
	<div id="comment-<?php comment_ID(); ?>" class="perfumes-chat__row">
		<?php if ( $avatar ) : ?>
			<img
				class="perfumes-chat__avatar"
				src="<?php echo esc_url( $avatar ); ?>"
				alt="<?php echo esc_attr( $author ); ?>"
				width="32"
				height="32"
			/>
		<?php else : ?>
			<span class="perfumes-chat__avatar" aria-hidden="true"></span>
		<?php endif; ?>
		<div class="perfumes-chat__bubble">
			<div class="perfumes-chat__meta">
				<span class="perfumes-chat__author"><?php echo esc_html( $author ); ?></span>
				<time class="perfumes-chat__time" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>">
					<?php echo esc_html( get_comment_date( 'j M Y' ) ); ?>
				</time>
			</div>
			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="perfumes-chat__pending"><?php esc_html_e( 'Tu opinión está pendiente de aprobación.', 'omar-perfumes' ); ?></p>
			<?php endif; ?>
			<div class="perfumes-chat__text">
				<?php comment_text(); ?>
			</div>
			<?php if ( $commenter_rating && wc_review_ratings_enabled() ) : ?>
				<span class="perfumes-chat__rating" aria-label="<?php echo esc_attr( sprintf( __( '%s de 5', 'omar-perfumes' ), $commenter_rating ) ); ?>">
					<?php echo esc_html( str_repeat( '★', $commenter_rating ) . str_repeat( '☆', 5 - $commenter_rating ) ); ?>
				</span>
			<?php else : ?>
				<span class="perfumes-chat__status"><?php esc_html_e( 'Publicado', 'omar-perfumes' ); ?></span>
			<?php endif; ?>
		</div>
	</div>
