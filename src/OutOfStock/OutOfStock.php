<?php

/**
 * Sold-out treatment for WooCommerce product cards.
 *
 * Product grids keep showing out-of-stock products (the product-collection
 * blocks include `outofstock` in their stock filter); this marks them as
 * sold out instead of letting them disappear: the product-image wrapper gets
 * an `is-out-of-stock` class, the post-title block gets `is-sold-out` (struck
 * through) and the card button reads "Sold Out", grayed out and disabled
 * (product-count-button component). Stock status is WooCommerce's own:
 * nothing is configured here.
 *
 * A product that is out of stock and tagged "Coming Soon" (product_badge
 * term) keeps a "Coming Soon" pill, no strikethrough, and its button reads
 * "Coming Soon".
 *
 * @package Delta9DigitalBlocksPlugin\OutOfStock
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\OutOfStock;

use Delta9DigitalBlocksPlugin\ProductBadges\ProductBadges;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Services\ServiceInterface;
use WP_Block;
use WP_HTML_Tag_Processor;

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
		\add_filter('render_block_core/post-title', [$this, 'markSoldOutTitle'], 10, 3);
	}

	/**
	 * Whether the product is out of stock and not tagged "Coming Soon".
	 *
	 * @param int $productId Product post id.
	 *
	 * @return bool
	 */
	public static function isSoldOut(int $productId): bool
	{
		return self::isOutOfStock($productId) && !ProductBadges::isComingSoon($productId);
	}

	/**
	 * Add `is-sold-out` to a sold-out product's title block (struck through
	 * in product-badge-style.scss).
	 *
	 * @param string $content Rendered block markup.
	 * @param array<string, mixed> $parsedBlock Parsed block.
	 * @param WP_Block|null $instance Block instance; carries the loop's postId.
	 *
	 * @return string
	 */
	public function markSoldOutTitle(string $content, array $parsedBlock, $instance = null): string
	{
		$productId = ProductBadges::resolveProductId($parsedBlock, $instance);

		if ($content === '' || \get_post_type($productId) !== 'product' || !self::isSoldOut($productId)) {
			return $content;
		}

		$tags = new WP_HTML_Tag_Processor($content);

		if (!$tags->next_tag()) {
			return $content;
		}

		$tags->add_class('is-sold-out');

		return $tags->get_updated_html();
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

		return ProductBadges::injectIntoProductImage(
			$content,
			$badge === null ? '' : ProductBadges::renderBadge($badge),
			'is-out-of-stock'
		);
	}
}
