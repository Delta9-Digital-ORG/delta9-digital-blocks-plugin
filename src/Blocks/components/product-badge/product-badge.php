<?php

/**
 * Template for the Product Badge Component.
 *
 * A small pill printed over a product image: "Sold Out", "Coming Soon",
 * "Price Drop", "#1 Best Seller", "New", "Staff Favorite" or any other
 * `product_badge` term. Not a block on its own: ProductBadges and OutOfStock
 * inject it into woocommerce/product-image, and the single-product block
 * prints it on its hero and flavor cards.
 *
 * Variants are keyed on `data-badge` (the slug) rather than a modifier
 * class, so the single-product hero can swap the badge client-side by
 * binding one attribute.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$productBadgeSlug = (string) ($attributes['productBadgeSlug'] ?? '');
$productBadgeLabel = (string) ($attributes['productBadgeLabel'] ?? '');

if ($productBadgeLabel === '') {
	return;
}

?>
<span
	class="<?php echo esc_attr(Helpers::classnames([$componentClass, $additionalClass])); ?>"
	data-badge="<?php echo esc_attr($productBadgeSlug); ?>"
><?php echo esc_html($productBadgeLabel); ?></span>
