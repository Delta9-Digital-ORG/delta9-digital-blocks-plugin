<?php

/**
 * Template for the Product Gallery Slot block.
 *
 * Renders a single positional image (or cover) from the current product's
 * WC gallery. Resolution order at render time:
 *
 *   1. The `productImageGallerySlotProductId` attribute, if set explicitly.
 *   2. The `postId` context from a parent core/query-loop (set via
 *      `usesContext` in the manifest).
 *   3. `get_the_ID()` of the current request (single-product page, etc.).
 *
 * Saved markup contains no image URL — every render fetches fresh, so the
 * same slot block placed inside a query loop yields the correct image for
 * each iterated product.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest   = Helpers::getManifestByDir(__DIR__);
$blockClass = $attributes['blockClass'] ?? '';

if ( ! function_exists( 'wc_get_product' ) ) {
	return;
}

$slot_index   = (int) ( $attributes['productImageGallerySlotIndex'] ?? 0 );
$slot_type    = (string) ( $attributes['productImageGallerySlotType'] ?? 'image' );
$id_override  = (int) ( $attributes['productImageGallerySlotProductId'] ?? 0 );
$cover_min_h  = (string) ( $attributes['productImageGallerySlotCoverMinHeight'] ?? '400px' );
$cover_dim    = (int) ( $attributes['productImageGallerySlotCoverDimRatio'] ?? 50 );
$cover_color  = (string) ( $attributes['productImageGallerySlotCoverOverlayColor'] ?? '#000000' );
$min_height   = (string) ( $attributes['productImageGallerySlotMinHeight'] ?? '' );
$object_fit   = (string) ( $attributes['productImageGallerySlotObjectFit'] ?? 'cover' );

// `productImageGallerySlotMinHeight` applies to both image and cover
// variants. For covers it overrides the legacy
// `productImageGallerySlotCoverMinHeight` when set, but the cover-only
// attr is still honored if the general one is blank.
$effective_height_cover = '' !== $min_height ? $min_height : $cover_min_h;

// Resolve product. block.context isn't directly available in Eightshift's
// render template, but `get_the_ID()` already follows the_post() inside
// any core/query-loop iteration — so we get the iterated product for free.
$product_id = $id_override > 0 ? $id_override : (int) get_the_ID();
if ( ! $product_id ) {
	return;
}

$product = wc_get_product( $product_id );
if ( ! $product ) {
	return;
}

// Build the gallery: featured image first, then product gallery image IDs.
$gallery_ids = array_filter( array_merge(
	[ (int) $product->get_image_id() ],
	array_map( 'intval', (array) $product->get_gallery_image_ids() )
) );

if ( ! isset( $gallery_ids[ $slot_index ] ) ) {
	// Out of range — render nothing on the frontend.
	return;
}

$attachment_id = (int) $gallery_ids[ $slot_index ];
$image_url     = wp_get_attachment_image_url( $attachment_id, 'full' );
if ( ! $image_url ) {
	return;
}

$image_alt = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
if ( '' === $image_alt ) {
	$image_alt = $product->get_name();
}

$wrapper_classes = trim( $blockClass . ' product-image-gallery-slot' );

// iAPI hookup. When the single-product block is also on the page it
// registers the `delta9/singleProduct` store; our `data-wp-bind--src` /
// `data-wp-bind--alt` then swap the slot's image whenever the user
// picks a different flavor card. On pages without the single-product
// block, the store never registers and the server-rendered `src` / `alt`
// just stay put — progressive enhancement.
$wp_context = wp_json_encode( [ 'slotIndex' => $slot_index ] );

if ( 'cover' === $slot_type ) {
	// Emit core/cover-compatible markup. Mirrors the structure WP outputs
	// for a published core/cover block so theme styles apply uniformly.
	$cover_styles = sprintf(
		'min-height:%s;',
		esc_attr( $effective_height_cover )
	);
	$overlay_styles = sprintf(
		'background-color:%s;opacity:%s;',
		esc_attr( $cover_color ),
		esc_attr( max( 0, min( 100, $cover_dim ) ) / 100 )
	);
	?>
	<div
		class="wp-block-cover <?php echo esc_attr( $wrapper_classes ); ?>"
		style="<?php echo esc_attr( $cover_styles ); ?>"
		data-wp-interactive="delta9/singleProduct"
		data-wp-context="<?php echo esc_attr( $wp_context ); ?>"
	>
		<img
			class="wp-block-cover__image-background"
			src="<?php echo esc_url( $image_url ); ?>"
			alt="<?php echo esc_attr( $image_alt ); ?>"
			data-object-fit="cover"
			data-wp-bind--src="state.slotImageSrc"
			data-wp-bind--alt="state.slotImageAlt"
		/>
		<span aria-hidden="true" class="wp-block-cover__background has-background-dim" style="<?php echo esc_attr( $overlay_styles ); ?>"></span>
		<div class="wp-block-cover__inner-container"></div>
	</div>
	<?php
	return;
}

// Default: image type. Mirrors core/image's rendered markup but adds
// optional min-height + object-fit so the figure can act like a fixed
// crop frame (matches the Figma right-column grid layout).
$figure_style = '' !== $min_height
	? sprintf( 'min-height:%s;', esc_attr( $min_height ) )
	: '';
$img_style = '' !== $min_height
	? sprintf( 'width:100%%;height:100%%;object-fit:%s;display:block;', esc_attr( $object_fit ) )
	: '';
?>
<figure
	class="wp-block-image <?php echo esc_attr( $wrapper_classes ); ?>"
	<?php if ( $figure_style ) : ?>style="<?php echo esc_attr( $figure_style ); ?>"<?php endif; ?>
	data-wp-interactive="delta9/singleProduct"
	data-wp-context="<?php echo esc_attr( $wp_context ); ?>"
>
	<img
		src="<?php echo esc_url( $image_url ); ?>"
		alt="<?php echo esc_attr( $image_alt ); ?>"
		<?php if ( $img_style ) : ?>style="<?php echo esc_attr( $img_style ); ?>"<?php endif; ?>
		data-wp-bind--src="state.slotImageSrc"
		data-wp-bind--alt="state.slotImageAlt"
	/>
</figure>
