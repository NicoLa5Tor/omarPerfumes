<?php
/**
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

$title       = $attributes['title'] ?? '';
$description = $attributes['description'] ?? '';
$image_url   = $attributes['imageUrl'] ?? '';

$wrapper_attributes = get_block_wrapper_attributes();
?>
<div <?php echo $wrapper_attributes; ?>>
	<div class="perfume-card">
		<?php if ( $image_url ) : ?>
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
		<?php endif; ?>
		<h3><?php echo wp_kses_post( $title ); ?></h3>
		<p><?php echo wp_kses_post( $description ); ?></p>
	</div>
</div>
