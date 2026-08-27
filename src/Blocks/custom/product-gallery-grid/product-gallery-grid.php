<?php

/**
 * Template for the Product Gallery Grid block.
 *
 * Renders the current product's WooCommerce gallery images (not the
 * featured image — that's the hero can) as a photo grid: first image
 * large, the rest stacked beside it. Outputs nothing when the product
 * has no gallery images, so the pattern collapses gracefully.
 *
 * @package Delta9DigitalBlocksPlugin
 */

if ( ! function_exists( 'wc_get_product' ) ) {
	return;
}

$product = wc_get_product( get_the_ID() );
if ( ! $product instanceof \WC_Product ) {
	return;
}

$gallery_ids = array_map( 'intval', (array) $product->get_gallery_image_ids() );
$gallery_ids = array_values( array_filter( $gallery_ids ) );

if ( empty( $gallery_ids ) ) {
	return;
}

$max_images  = (int) ( $attributes['productGalleryGridMaxImages'] ?? 4 );
$gallery_ids = array_slice( $gallery_ids, 0, max( 1, $max_images ) );

?>
<div class="yb-product-gallery-grid" data-count="<?php echo (int) count( $gallery_ids ); ?>">
	<?php foreach ( $gallery_ids as $attachment_id ) : ?>
		<figure class="yb-product-gallery-grid__item">
			<?php
			echo wp_get_attachment_image(
				$attachment_id,
				'large',
				false,
				[
					'class'   => 'yb-product-gallery-grid__image',
					'loading' => 'lazy',
				]
			);
			?>
		</figure>
	<?php endforeach; ?>
</div>
