<?php

/**
 * Template for the Product Nutrition Facts block.
 *
 * Prints the current product's FDA nutrition panel. Shares its renderer with
 * the single-product block's "Nutritional facts" tab, so the copy in the
 * description row's nutrition card is always the same grid. Outputs nothing
 * when the product declares no values, so the card collapses gracefully.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPlugin\SingleProduct\NutritionFactsTable;
use Delta9DigitalBlocksPlugin\SingleProduct\SingleProductData;

if (! function_exists('wc_get_product')) {
	return;
}

$product = wc_get_product(get_the_ID());

if (! $product instanceof \WC_Product) {
	return;
}

echo NutritionFactsTable::render(
	SingleProductData::nutritionFacts($product->get_id())
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — renderer escapes labels and values.
