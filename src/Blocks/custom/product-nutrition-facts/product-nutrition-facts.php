<?php

/**
 * Template for the Product Nutrition Facts block.
 *
 * Renders Nutrition Facts and Cana Facts tables plus serving info in a
 * translucent green card, matching the Figma "Layout / 28" middle panel.
 * Loop-safe: resolves the product via get_the_ID() so the same block
 * works inside a query loop.
 *
 * Data sources:
 *   - `_yb_nutrition_rows` (NEW)   → multiline "Label|Value" → nutrition table
 *   - `_yb_cana_rows`      (NEW)   → multiline "Label|Value" → cana facts table
 *   - `_custom_product_serving_size_text_field`        → "Serving Size:" footer
 *   - `_custom_product_servings_per_container_text_field` → "Servings Per Container:" footer
 *   - `_custom_product_suggested_use_text_field`       → "Suggested Use:" footer
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest   = Helpers::getManifestByDir(__DIR__);
$blockClass = $attributes['blockClass'] ?? '';

if ( ! function_exists( 'wc_get_product' ) ) {
	return;
}

$nutrition_heading = (string) ( $attributes['productNutritionFactsHeading'] ?? 'Nutrition Facts' );
$cana_heading      = (string) ( $attributes['productNutritionFactsCanaHeading'] ?? 'Cana Facts' );
$id_override       = (int) ( $attributes['productNutritionFactsProductId'] ?? 0 );

$product_id = $id_override > 0 ? $id_override : (int) get_the_ID();
if ( ! $product_id ) {
	return;
}

$product = wc_get_product( $product_id );
if ( ! $product ) {
	return;
}

/**
 * Parse a multiline "Label|Value" string into rows.
 *
 * @param string $value Raw meta value.
 * @return array<int, array{label:string,value:string}>
 */
$parse_rows = static function ( string $value ): array {
	$rows = [];
	foreach ( preg_split( '/\r\n|\r|\n/', $value ) as $line ) {
		if ( false === strpos( $line, '|' ) ) {
			continue;
		}
		$parts = explode( '|', $line, 2 );
		$label = trim( $parts[0] ?? '' );
		$val   = trim( $parts[1] ?? '' );
		if ( '' === $label || '' === $val ) {
			continue;
		}
		$rows[] = [ 'label' => $label, 'value' => $val ];
	}
	return $rows;
};

$nutrition_rows = $parse_rows( (string) get_post_meta( $product_id, '_yb_nutrition_rows', true ) );
$cana_rows      = $parse_rows( (string) get_post_meta( $product_id, '_yb_cana_rows', true ) );
$serving_size   = (string) get_post_meta( $product_id, '_custom_product_serving_size_text_field', true );
$servings_pc    = (string) get_post_meta( $product_id, '_custom_product_servings_per_container_text_field', true );
$suggested_use  = (string) get_post_meta( $product_id, '_custom_product_suggested_use_text_field', true );

if ( empty( $nutrition_rows ) && empty( $cana_rows ) && '' === $serving_size && '' === $servings_pc && '' === $suggested_use ) {
	return;
}

$wrapper_classes = trim( $blockClass . ' product-nutrition-facts' );

$render_table = static function ( array $rows ): void {
	echo '<div class="product-nutrition-facts__table">';
	foreach ( $rows as $row ) {
		echo '<div class="product-nutrition-facts__row">';
		echo '<span class="product-nutrition-facts__rowLabel">' . esc_html( $row['label'] ) . '</span>';
		echo '<span class="product-nutrition-facts__rowValue">' . esc_html( $row['value'] ) . '</span>';
		echo '</div>';
	}
	echo '</div>';
};
?>
<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
	<?php if ( ! empty( $nutrition_rows ) ) : ?>
		<h3 class="product-nutrition-facts__heading"><?php echo esc_html( $nutrition_heading ); ?></h3>
		<?php $render_table( $nutrition_rows ); ?>
	<?php endif; ?>

	<?php if ( ! empty( $cana_rows ) ) : ?>
		<h3 class="product-nutrition-facts__heading"><?php echo esc_html( $cana_heading ); ?></h3>
		<?php $render_table( $cana_rows ); ?>
	<?php endif; ?>

	<?php if ( '' !== $serving_size || '' !== $servings_pc || '' !== $suggested_use ) : ?>
		<div class="product-nutrition-facts__notes">
			<?php if ( '' !== $serving_size ) : ?>
				<p>
					<strong><?php esc_html_e( 'Serving Size:', 'delta9-digital-blocks-plugin' ); ?></strong>
					<?php echo esc_html( $serving_size ); ?>
				</p>
			<?php endif; ?>
			<?php if ( '' !== $servings_pc ) : ?>
				<p>
					<strong><?php esc_html_e( 'Servings Per Container:', 'delta9-digital-blocks-plugin' ); ?></strong>
					<?php echo esc_html( $servings_pc ); ?>
				</p>
			<?php endif; ?>
			<?php if ( '' !== $suggested_use ) : ?>
				<p>
					<strong><?php esc_html_e( 'Suggested Use:', 'delta9-digital-blocks-plugin' ); ?></strong>
					<?php echo esc_html( $suggested_use ); ?>
				</p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
