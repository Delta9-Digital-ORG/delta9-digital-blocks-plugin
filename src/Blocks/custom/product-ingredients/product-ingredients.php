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

$productIngredients = array();

foreach($productCategories as $prodCat) {
	if($prodCat->parent == $parentCatID) {
		$productIngredients[] = get_term_by( 'term_id', $prodCat->term_id, 'product_cat' );
	}
}

$productIngredientsText = array();

foreach($productIngredients as $productIngredient) {
	$plainDesc = wp_strip_all_tags($productIngredient->description);
	$descLines = array_filter(preg_split('/\r\n|\r|\n/', $plainDesc), function($line) {
		return !empty(trim($line));
	});
	$descLines = array_values($descLines);

	switch($productIngredientsDisplay) {
		case 'ingredients':
			$productIngredientsText[] = $productIngredient->name;
			break;
		case 'top-benefits':
			if(!empty($descLines)) {
				$productIngredientsText[] = trim($descLines[0]);
			}
			break;
		case 'all-benefits':
			foreach($descLines as $line) {
				$productIngredientsText[] = trim($line);
			}
			break;
	}
}

// Collect ingredient short benefits (first line of description).
$ingredientBenefits = array_values(array_filter($productIngredientsText, function($text) {
	return !empty(trim($text));
}));

// Collect cana-facts short benefits (first line of description).
$canaFactsBenefits = array();
$canaFactsArgs = array(
	'taxonomy' => 'product_cat',
	'hide_empty' => false,
	'slug' => 'cana-facts',
	'parent' => 0
);
$canaFactsCat = get_terms($canaFactsArgs);

if(!empty($canaFactsCat) && !is_wp_error($canaFactsCat)) {
	$canaFactsParentID = $canaFactsCat[0]->term_id;

	foreach($productCategories as $prodCat) {
		if($prodCat->parent == $canaFactsParentID) {
			$canaFactTerm = get_term_by('term_id', $prodCat->term_id, 'product_cat');
			if($canaFactTerm) {
				// Strip HTML tags and split by newlines to get all benefits.
				$plainText = wp_strip_all_tags($canaFactTerm->description);
				$factLines = preg_split('/\r\n|\r|\n/', $plainText);
				foreach($factLines as $line) {
					$line = trim($line);
					if(!empty($line)) {
						$canaFactsBenefits[] = $line;
					}
				}
			}
		}
	}
}

// Interleave: ingredient 1st, cana-fact 1st, ingredient 2nd, cana-fact 2nd, etc.
// Dedupe case-insensitive as we go.
$productIngredientsText = array();
$seen = array();
$maxFacts = 4;
$maxIndex = max(count($ingredientBenefits), count($canaFactsBenefits));

for($i = 0; $i < $maxIndex && count($productIngredientsText) < $maxFacts; $i++) {
	if(isset($ingredientBenefits[$i])) {
		$lower = strtolower(trim($ingredientBenefits[$i]));
		if(!isset($seen[$lower])) {
			$seen[$lower] = true;
			$productIngredientsText[] = $ingredientBenefits[$i];
		}
	}
	if(isset($canaFactsBenefits[$i]) && count($productIngredientsText) < $maxFacts) {
		$lower = strtolower(trim($canaFactsBenefits[$i]));
		if(!isset($seen[$lower])) {
			$seen[$lower] = true;
			$productIngredientsText[] = $canaFactsBenefits[$i];
		}
	}
}

// Sort by character count and pair short with long, alternating order.
usort($productIngredientsText, function($a, $b) {
	return strlen(trim($a)) - strlen(trim($b));
});

$sorted = array();
$total = count($productIngredientsText);
$half = ceil($total / 2);

for($i = 0; $i < $half; $i++) {
	$short = $productIngredientsText[$i];
	$long = isset($productIngredientsText[$total - 1 - $i]) && ($total - 1 - $i) !== $i
		? $productIngredientsText[$total - 1 - $i]
		: null;

	if($i % 2 === 0) {
		// Even pair: short first, then long.
		$sorted[] = $short;
		if($long) $sorted[] = $long;
	} else {
		// Odd pair: long first, then short.
		if($long) $sorted[] = $long;
		$sorted[] = $short;
	}
}

$productIngredientsText = $sorted;

if(!empty($productIngredientsText)) {
	echo '<div class="' . $blockClass . '">';

	foreach($productIngredientsText as $ingredientText) {
		if(!empty(trim($ingredientText))) {
			echo '<div class="product-ingredients-container">';
				echo '<span class="product-ingredients-name"><strong>' . wp_kses_post($ingredientText) . '</strong></span>';
			echo "</div>";
		}
	}

	echo "</div>";
}
?>
