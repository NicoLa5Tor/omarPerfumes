<?php
/**
 * Product reviews as chat bubbles.
 *
 * @package OmarPerfumes
 * @see     https://woocommerce.com/document/template-structure/
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product && function_exists( 'wc_get_product' ) ) {
	$product = wc_get_product();
}

if ( ! $product instanceof WC_Product ) {
	return;
}

$review_comments = function_exists( 'omar_perfumes_get_product_reviews' )
	? omar_perfumes_get_product_reviews( $product )
	: array();
$count           = count( $review_comments );

if ( ! comments_open() && ! $count ) {
	return;
}
?>
<section id="reviews" class="woocommerce-Reviews perfumes-pdp-reviews">
	<?php omar_perfumes_review_submission_notice(); ?>
	<div id="comments">
		<h2 class="woocommerce-Reviews-title">
			<?php
			if ( $count ) {
				printf(
					/* translators: 1: reviews count 2: product name */
					esc_html( _n( '%1$s opinión de %2$s', '%1$s opiniones de %2$s', $count, 'omar-perfumes' ) ),
					esc_html( number_format_i18n( $count ) ),
					'<span>' . wp_kses_post( $product->get_name() ) . '</span>'
				);
			} else {
				esc_html_e( 'Opiniones', 'omar-perfumes' );
			}
			?>
		</h2>

		<?php if ( $review_comments ) : ?>
			<ol class="commentlist perfumes-chat">
				<?php
				wp_list_comments(
					apply_filters(
						'woocommerce_product_review_list_args',
						array(
							'callback'          => 'woocommerce_comments',
							'style'             => 'ol',
							'type'              => 'all',
							'reverse_top_level' => false,
						)
					),
					$review_comments
				);
				?>
			</ol>
			<?php
			if ( get_comment_pages_count( $review_comments ) > 1 && get_option( 'page_comments' ) ) {
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links(
					array(
						'type' => 'list',
					)
				);
				echo '</nav>';
			}
			?>
		<?php else : ?>
			<p class="woocommerce-noreviews"><?php esc_html_e( 'Aún no hay opiniones. Sé el primero en comentar.', 'omar-perfumes' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( comments_open() && ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) ) : ?>
		<div id="review_form_wrapper" class="perfumes-chat-form">
			<div id="review_form">
				<?php
				$commenter = wp_get_current_commenter();
				$comment_form = array(
					'title_reply'          => $review_comments ? __( 'Escribe una opinión', 'omar-perfumes' ) : __( 'Sé el primero en opinar', 'omar-perfumes' ),
					'title_reply_to'       => __( 'Responder a %s', 'omar-perfumes' ),
					'title_reply_before'   => '<span id="reply-title" class="comment-reply-title">',
					'title_reply_after'    => '</span>',
					'comment_notes_before' => '',
					'comment_notes_after'  => '',
					'label_submit'         => __( 'Publicar opinión', 'omar-perfumes' ),
					'class_submit'         => 'perfumes-chat-form__submit',
					'logged_in_as'         => '',
					'comment_field'        => '',
				);

				$name_email_required = (bool) get_option( 'require_name_email', 1 );
				$fields              = array(
					'author' => '<p class="comment-form-author"><label for="author">' . esc_html__( 'Nombre', 'omar-perfumes' ) . ( $name_email_required ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . ( $name_email_required ? ' required' : '' ) . ' /></p>',
					'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Correo', 'omar-perfumes' ) . ( $name_email_required ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30"' . ( $name_email_required ? ' required' : '' ) . ' /></p>',
				);

				$comment_form['fields'] = $fields;

				$rating_required = wc_review_ratings_required();
				if ( wc_review_ratings_enabled() ) {
					$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating">' . esc_html__( 'Tu valoración', 'omar-perfumes' ) . ( $rating_required ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating"' . ( $rating_required ? ' required' : '' ) . '>
						<option value="">' . esc_html__( 'Valora…', 'omar-perfumes' ) . '</option>
						<option value="5">' . esc_html__( 'Perfecto', 'omar-perfumes' ) . '</option>
						<option value="4">' . esc_html__( 'Muy bueno', 'omar-perfumes' ) . '</option>
						<option value="3">' . esc_html__( 'Bueno', 'omar-perfumes' ) . '</option>
						<option value="2">' . esc_html__( 'Regular', 'omar-perfumes' ) . '</option>
						<option value="1">' . esc_html__( 'Muy malo', 'omar-perfumes' ) . '</option>
					</select></div>';
				}

				$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Tu opinión', 'omar-perfumes' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="4" required placeholder="' . esc_attr__( 'Cuéntanos cómo te fue con esta fragancia…', 'omar-perfumes' ) . '"></textarea></p>';

				comment_form(
					apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ),
					$product->get_id()
				);
				?>
			</div>
		</div>
	<?php elseif ( comments_open() ) : ?>
		<p class="woocommerce-verification-required"><?php esc_html_e( 'Solo los clientes que compraron este producto pueden dejar una opinión.', 'omar-perfumes' ); ?></p>
	<?php endif; ?>
</section>
