<?php

/**
 * Template for the Product Description block.
 *
 * Renders the current product's description / ingredients / other
 * ingredients in a white card with a primary-green border, matching the
 * Figma "Layout / 28" left panel. Loop-safe: each render resolves the
 * product via `get_the_ID()` so the same block works inside a query loop.
 *
 * Data sources (all existing WC product meta):
 *   - `_custom_product_description_text_field`        → intro paragraph
 *   - `_custom_product_ingredients_text_field`        → body (split on ` | ` or newline)
 *   - `_custom_product_other_ingredients_text_field`  → "Other Ingredients:" footer
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest   = Helpers::getManifestByDir(__DIR__);
$blockClass = $attributes['blockClass'] ?? '';

if ( ! function_exists( 'wc_get_product' ) ) {
	return;
}

$heading    = (string) ( $attributes['productDescriptionHeading'] ?? 'Description' );
$id_override = (int) ( $attributes['productDescriptionProductId'] ?? 0 );

$product_id = $id_override > 0 ? $id_override : (int) get_the_ID();
if ( ! $product_id ) {
	return;
}

$product = wc_get_product( $product_id );
if ( ! $product ) {
	return;
}

$description      = (string) get_post_meta( $product_id, '_custom_product_description_text_field', true );
$ingredients      = (string) get_post_meta( $product_id, '_custom_product_ingredients_text_field', true );
$other_ingredients = (string) get_post_meta( $product_id, '_custom_product_other_ingredients_text_field', true );

// Split ingredient body on ' | ' (existing admin convention) and on
// newlines so the admin can write either format.
$paragraphs = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n|\s\|\s/u', $ingredients ) ?: [] ) );

// "Other Ingredients" admin text usually starts with a label like
// "Clean & Natural Ingredients:". Strip any leading "Label:" prefix
// because the block emits its own bolded label.
$other_ingredients_clean = trim( preg_replace( '/^[^:]*:\s*/u', '', $other_ingredients ) ?? '' );

if ( '' === $description && empty( $paragraphs ) && '' === $other_ingredients_clean ) {
	return;
}

$wrapper_classes = trim( $blockClass . ' product-description' );
?>
<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
	<h3 class="product-description__heading"><?php echo esc_html( $heading ); ?></h3>

	<?php if ( '' !== $description ) : ?>
		<p class="product-description__intro"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $paragraphs ) ) : ?>
		<div class="product-description__body">
			<?php foreach ( $paragraphs as $p ) : ?>
				<p><?php echo esc_html( $p ); ?></p>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== $other_ingredients_clean ) : ?>
		<p class="product-description__other">
			<strong><?php esc_html_e( 'Other Ingredients:', 'delta9-digital-blocks-plugin' ); ?></strong>
			<?php echo esc_html( $other_ingredients_clean ); ?>
		</p>
	<?php endif; ?>
</div>
