<?php

/**
 * Template for the Product Badge Component.
 *
 * A white pill above a product title: "Sold Out", "Coming Soon",
 * "Price Drop", "#1 Best Seller", "New", "Staff Favorite" or any other
 * `product_badge` term, with the text in the product's own color. Not a
 * block on its own: ProductBadges injects it into core/post-title on
 * product cards, and the single-product block prints it above the name.
 *
 * The outer span is a block-level row; the inner one is inline-block so a
 * struck-through title's line doesn't run through the pill. `data-badge`
 * carries the slug so a variant can be styled, and so the single-product
 * block can swap the badge client-side by binding one attribute.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$productBadgeSlug = (string) ($attributes['productBadgeSlug'] ?? '');
$productBadgeLabel = (string) ($attributes['productBadgeLabel'] ?? '');
$productBadgeColor = (string) ($attributes['productBadgeColor'] ?? '');

if ($productBadgeLabel === '') {
	return;
}

$style = $productBadgeColor !== '' ? sprintf('--product-badge-color: %s', $productBadgeColor) : '';

?>
<span
	class="<?php echo esc_attr(Helpers::classnames([$componentClass, $additionalClass])); ?>"
	data-badge="<?php echo esc_attr($productBadgeSlug); ?>"
	<?php echo $style !== '' ? 'style="' . esc_attr($style) . '"' : ''; ?>
><span class="<?php echo esc_attr($componentClass); ?>__label"><?php echo esc_html($productBadgeLabel); ?></span></span>
