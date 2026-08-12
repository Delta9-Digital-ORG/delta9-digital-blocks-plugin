<?php

/**
 * Template for the Product Top Benefits Block.
 *
 * Renders the manually-curated benefits stored in the "Product Top Benefits"
 * meta box (see TopBenefits service). Works on the frontend and in the block
 * editor preview (via ServerSideRender) by resolving the product from either
 * the global WooCommerce product or the post currently being edited.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;
use Delta9DigitalBlocksPlugin\TopBenefits\TopBenefits;

$manifest = Helpers::getManifestByDir(__DIR__);

$unique = Helpers::getUnique();

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$productTopBenefitsServerSideRender = Helpers::checkAttr('productTopBenefitsServerSideRender', $attributes, $manifest);
$productTopBenefitsBorder = Helpers::checkAttr('productTopBenefitsBorder', $attributes, $manifest);
$productTopBenefitsBorderThick = Helpers::checkAttr('productTopBenefitsBorderThick', $attributes, $manifest);
$productTopBenefitsFormat = (Helpers::checkAttr('productTopBenefitsFormat', $attributes, $manifest))['value'];

// Clamp the maximum number of benefits to show (1–8, default 6).
$productTopBenefitsMax = (int) Helpers::checkAttr('productTopBenefitsMax', $attributes, $manifest);
if ($productTopBenefitsMax < 1) {
	$productTopBenefitsMax = 6;
}
if ($productTopBenefitsMax > 8) {
	$productTopBenefitsMax = 8;
}

if ($productTopBenefitsBorder) {
	$blockClass .= " product-top-benefits-format-border";
}

if ($productTopBenefitsBorderThick) {
	$blockClass .= " product-top-benefits-format-border-thick";
}

$isStacked = ($productTopBenefitsFormat === 'Stacked');
$blockClass .= $isStacked ? " product-top-benefits-format-stacked" : " product-top-benefits-format-inline";

/**
 * Render a list of benefit strings as chips.
 *
 * @param string[] $benefits  Benefit strings.
 * @param string   $wrapClass Wrapper CSS classes.
 * @param bool     $stacked   Whether to stack each benefit's words onto separate lines.
 *
 * @return void
 */
$renderBenefits = static function (array $benefits, string $wrapClass, bool $stacked): void {
	echo '<div class="' . esc_attr($wrapClass) . '">';
	foreach ($benefits as $benefit) {
		if (trim($benefit) === '') {
			continue;
		}

		if ($stacked) {
			$words = preg_split('/\s+/', trim($benefit));
			$safe = implode('<br>', array_map('esc_html', $words));
		} else {
			$safe = esc_html($benefit);
		}

		echo '<div class="product-top-benefits-container">';
			echo '<span class="product-top-benefits-name"><strong>' . $safe . '</strong></span>';
		echo '</div>';
	}
	echo '</div>';
};

// Resolve the product from the current context (frontend or editor preview).
global $product;
$productObj = null;

if (isset($product) && is_object($product) && method_exists($product, 'get_id')) {
	$productObj = $product;
} else {
	$currentId = get_the_ID();
	if ($currentId && get_post_type($currentId) === 'product' && function_exists('wc_get_product')) {
		$productObj = wc_get_product($currentId);
	}
}

// No product context (e.g. abstract template preview). Show placeholder in the editor only.
if (!$productObj || !method_exists($productObj, 'get_id')) {
	if ($productTopBenefitsServerSideRender) {
		$renderBenefits(
			array('Body Wellness', 'Uplifting', 'Sleep Support', 'Soothe Discomfort'),
			$blockClass,
			$isStacked
		);
	}
	return;
}

// Read the curated benefits from the product meta box.
$rawBenefits = get_post_meta($productObj->get_id(), TopBenefits::META_KEY, true);
$benefits = $rawBenefits ? json_decode($rawBenefits, true) : array();

$benefits = is_array($benefits)
	? array_values(array_filter(array_map('trim', $benefits), function ($benefit) {
		return $benefit !== '';
	}))
	: array();

$benefits = array_slice($benefits, 0, $productTopBenefitsMax);

if (empty($benefits)) {
	return;
}

$renderBenefits($benefits, $blockClass, $isStacked);
?>
