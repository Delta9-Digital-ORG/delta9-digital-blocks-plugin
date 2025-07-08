<?php

/**
 * Template for the Product Ingredients Block.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$unique = Helpers::getUnique();

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$productIngredientsDisplay = (Helpers::checkAttr('productIngredientsDisplay', $attributes, $manifest))['value'];
$productIngredientsBorder = Helpers::checkAttr('productIngredientsBorder', $attributes, $manifest);
$productIngredientsBorderThick = Helpers::checkAttr('productIngredientsBorderThick', $attributes, $manifest);
$productIngredientsFormat = (Helpers::checkAttr('productIngredientsFormat', $attributes, $manifest))['value'];

if($productIngredientsBorder) {
	$blockClass .= " product-ingredients-format-border";
}

if($productIngredientsBorderThick) {
	$blockClass .= " product-ingredients-format-border-thick";
}

if($productIngredientsFormat == 'Stacked') {
	$blockClass .= " product-ingredients-format-stacked";
} elseif($productIngredientsFormat == 'Reverse Order') {
	$blockClass .= " product-ingredients-format-reverse-order";
}

global $product;

$categoriesArray = array();
$productParentCats = array();
$productChildCats = array();
$productCategories = get_the_terms( $product->ID, 'product_cat' );

$args = array(
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
    'slug' => 'ingredients',
    'parent'   => 0
);
$product_cat = get_terms( $args );

$parentCatID = ($product_cat[0])->term_id;

foreach($productCategories as $prodCat) {
	if($prodCat->parent == $parentCatID) {
		$productIngredients[] = get_term_by( 'term_id', $prodCat->term_id, 'product_cat' );
	}
}

$productIngredientsText = array();

foreach($productIngredients as $productIngredient) {
	switch($productIngredientsDisplay) {
		case 'ingredients':
			$productIngredientsText[] = $productIngredient->name;
			break;
		case 'top-benefits':
			$productIngredientsText[] = (explode(PHP_EOL, $productIngredient->description))[0];
			break;
		case 'all-benefits':
			$tempTextArray = explode(PHP_EOL, $productIngredient->description);
			$productIngredientsText = array_merge($productIngredientsText, $tempTextArray);
			break;
	}
}

$productIngredientsText = array_unique($productIngredientsText);

echo '<div class="' . $blockClass . '">';

if(!empty($productIngredientsText)) {
	foreach($productIngredientsText as $ingredientText) {
		echo '<div class="product-ingredients-container">';
			echo '<span class="product-ingredients-name">' . $ingredientText . '</span>';
		echo "</div>";
	}
}

echo "</div>";
?>
