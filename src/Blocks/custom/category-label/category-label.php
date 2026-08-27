<?php

/**
 * Template for the Category Label Block.
 *
 * Displays nested product category names with flexible ordering.
 * Works on taxonomy archives and in the product loop.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$unique = Helpers::getUnique();

$componentClass = $manifest['componentClass'] ?? 'category-label';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$categoryLabelSource = (Helpers::checkAttr('categoryLabelSource', $attributes, $manifest))['value'];
$categoryLabelParentSlug = (Helpers::checkAttr('categoryLabelParentSlug', $attributes, $manifest))['value'];
$categoryLabelFormat = (Helpers::checkAttr('categoryLabelFormat', $attributes, $manifest))['value'];
$categoryLabelSeparator = Helpers::checkAttr('categoryLabelSeparator', $attributes, $manifest);
$categoryLabelTransform = (Helpers::checkAttr('categoryLabelTransform', $attributes, $manifest))['value'];

$parentName = '';
$childName = '';

if ($categoryLabelSource === 'archive') {
	// Get the current queried term on a taxonomy archive page.
	$currentTerm = get_queried_object();

	if ($currentTerm instanceof WP_Term && $currentTerm->taxonomy === 'product_cat') {
		if ($currentTerm->parent !== 0) {
			// Current term has a parent — it's the child.
			$parentTerm = get_term($currentTerm->parent, 'product_cat');

			if ($parentTerm && !is_wp_error($parentTerm)) {
				$parentName = $parentTerm->name;
			}

			$childName = $currentTerm->name;
		} else {
			// Current term is a top-level category — treat it as parent only.
			$parentName = $currentTerm->name;
		}
	}
} elseif ($categoryLabelSource === 'product') {
	// Get categories from the current product in the loop.
	global $product;

	if (isset($product)) {
		$productID = $product->get_id();
	} else {
		$productID = 584;
	}

	$productCategories = get_the_terms($productID, 'product_cat');

	if ($productCategories && !is_wp_error($productCategories)) {
		if (!empty($categoryLabelParentSlug)) {
			// Find the parent category matching the specified slug.
			$matchedParent = null;

			foreach ($productCategories as $cat) {
				if ($cat->parent === 0 && $cat->slug === $categoryLabelParentSlug) {
					$matchedParent = $cat;
					break;
				}
			}

			if ($matchedParent) {
				$parentName = $matchedParent->name;

				// Find a child of that parent assigned to this product.
				foreach ($productCategories as $cat) {
					if ($cat->parent === $matchedParent->term_id) {
						$childName = $cat->name;
						break;
					}
				}
			}
		} else {
			// No parent slug specified — find the deepest assigned category.
			// Build a map of term_id => term for quick lookup.
			$termMap = [];
			foreach ($productCategories as $cat) {
				$termMap[$cat->term_id] = $cat;
			}

			// Find the deepest child (a term whose term_id is not a parent of any other term).
			$parentIds = array_map(function ($cat) {
				return $cat->parent;
			}, $productCategories);

			foreach ($productCategories as $cat) {
				if (!in_array($cat->term_id, $parentIds) && $cat->parent !== 0) {
					$childName = $cat->name;

					$parentTerm = get_term($cat->parent, 'product_cat');

					if ($parentTerm && !is_wp_error($parentTerm)) {
						$parentName = $parentTerm->name;
					}

					break;
				}
			}
		}
	}
}

// Build the output based on format.
$output = '';

switch ($categoryLabelFormat) {
	case 'child-parent':
		$parts = array_filter([$childName, $parentName]);
		$output = implode($categoryLabelSeparator, $parts);
		break;

	case 'parent-child':
		$parts = array_filter([$parentName, $childName]);
		$output = implode($categoryLabelSeparator, $parts);
		break;

	case 'parent-only':
		$output = $parentName;
		break;

	case 'child-only':
		$output = $childName;
		break;
}

// Apply text transform.
switch ($categoryLabelTransform) {
	case 'uppercase':
		$output = mb_strtoupper($output);
		break;

	case 'lowercase':
		$output = mb_strtolower($output);
		break;

	case 'capitalize':
		$output = mb_convert_case($output, MB_CASE_TITLE);
		break;
}

if (!empty($output) && !empty($renderContent)) {
	// Inject the dynamic category text into the inner block markup.
	// The inner block (heading/paragraph) provides the styling wrapper.
	// Use preg_replace_callback to avoid backreference issues in replacement string.
	$innerHtml = preg_replace_callback(
		'/(<(?:h[1-6]|p)[^>]*>).*?(<\/(?:h[1-6]|p)>)/s',
		function ($matches) use ($output) {
			return $matches[1] . esc_html($output) . $matches[2];
		},
		$renderContent
	);

	echo '<div class="' . esc_attr(trim("{$blockClass} {$additionalClass} category-label")) . '">';
	echo $innerHtml;
	echo '</div>';
} elseif (!empty($output)) {
	// Fallback if no inner blocks exist.
	echo '<div class="' . esc_attr(trim("{$blockClass} {$additionalClass} category-label")) . '">';
	echo esc_html($output);
	echo '</div>';
}
