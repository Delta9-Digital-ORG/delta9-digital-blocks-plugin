<?php

/**
 * Out-of-stock badge for WooCommerce product images.
 *
 * Product grids keep showing out-of-stock products (the product-collection
 * blocks include `outofstock` in their stock filter); this marks them as
 * sold out instead of letting them disappear. Any woocommerce/product-image
 * block whose product is out of stock gets an `is-out-of-stock` class and the
 * product-badge component printed inside it. Stock status is WooCommerce's
 * own: nothing is configured here.
 *
 * A product that is out of stock and tagged "Coming Soon" (product_badge
 * term) reads "Coming Soon" instead of "Sold Out" on the badge and buttons.
 *
 * @package Delta9DigitalBlocksPlugin\OutOfStock
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\OutOfStock;

use Delta9DigitalBlocksPlugin\ProductBadges\ProductBadges;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Services\ServiceInterface;
use WP_Block;

/**
 * Class OutOfStock
 */
class OutOfStock implements ServiceInterface
{
	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter('render_block_woocommerce/product-image', [$this, 'addBadgeToProductImage'], 10, 3);
	}

	/**
	 * Badge and button text for an out-of-stock product.
	 *
	 * @param int $productId Product post id; when given and the product
	 *                       carries the "Coming Soon" badge, that is the label.
	 *
	 * @return string
	 */
	public static function getLabel(int $productId = 0): string
	{
		if ($productId > 0 && ProductBadges::isComingSoon($productId)) {
			return (string) \apply_filters('wc_coming_soon_badge_text', \__('Coming Soon', 'delta9-digital-blocks-plugin'), $productId);
		}

		return (string) \apply_filters('wc_out_of_stock_badge_text', \__('Sold Out', 'delta9-digital-blocks-plugin'), $productId);
	}

	/**
	 * Whether the product (by id) exists and is out of stock.
	 *
	 * @param int $productId Product post id.
	 *
	 * @return bool
	 */
	public static function isOutOfStock(int $productId): bool
	{
		if (!$productId || !\function_exists('wc_get_product')) {
			return false;
		}

		$product = \wc_get_product($productId);

		return $product instanceof \WC_Product && !$product->is_in_stock();
	}

	/**
	 * Print the badge inside an out-of-stock product's image block.
	 *
	 * @param string $content Rendered block markup.
	 * @param array<string, mixed> $parsedBlock Parsed block.
	 * @param WP_Block|null $instance Block instance; carries the loop's postId.
	 *
	 * @return string
	 */
	public function addBadgeToProductImage(string $content, array $parsedBlock, $instance = null): string
	{
		$productId = ProductBadges::resolveProductId($parsedBlock, $instance);

		if ($content === '' || !self::isOutOfStock($productId)) {
			return $content;
		}

		$badge = ProductBadges::getBadge($productId);

		if ($badge === null) {
			return $content;
		}

		return ProductBadges::injectIntoProductImage($content, ProductBadges::renderBadge($badge), 'is-out-of-stock');
	}
}
