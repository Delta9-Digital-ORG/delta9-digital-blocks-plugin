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
	 * Mark a sold-out product's title block: add `is-sold-out` (struck through
	 * in product-badge-style.scss) and print a "Sold Out" pill above the title
	 * text, inside the heading so it sits on the card's colored panel.
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
		$content = $tags->get_updated_html();

		$ink = self::getPackageInk($productId);
		$style = $ink !== '' ? \sprintf(' style="--yb-sold-out-pill-color: %s"', \esc_attr($ink)) : '';

		// The outer span is a block-level row; the inner one is inline-block so
		// the title's line-through doesn't run through the pill.
		$pill = \sprintf(
			'<span class="yb-sold-out-pill"%s><span class="yb-sold-out-pill__label">%s</span></span>',
			$style,
			\esc_html(self::getLabel($productId))
		);

		// Attribute values are escaped, so the first `>` closes the opening tag.
		$openEnd = \strpos($content, '>');

		return $openEnd === false ? $content : \substr_replace($content, $pill, $openEnd + 1, 0);
	}

	/**
	 * Pill text color for a product: the darker of its brand package pair
	 * (product_tag `package-N` → theme.json `package-N-primary/secondary`),
	 * so it reads on the pill's white background. Empty when the product has
	 * no package tag; the stylesheet then falls back to black.
	 *
	 * @param int $productId Product post id.
	 *
	 * @return string Hex color or ''.
	 */
	public static function getPackageInk(int $productId): string
	{
		$tags = \get_the_terms($productId, 'product_tag');

		if (!\is_array($tags)) {
			return '';
		}

		$package = 0;

		foreach ($tags as $tag) {
			if (\preg_match('/^package-(\d+)$/', $tag->slug, $match)) {
				$package = (int) $match[1];
				break;
			}
		}

		if ($package === 0) {
			return '';
		}

		static $palette = null;

		if ($palette === null) {
			$palette = [];

			foreach ((array) \wp_get_global_settings(['color', 'palette', 'theme']) as $color) {
				if (isset($color['slug'], $color['color'])) {
					$palette[$color['slug']] = (string) $color['color'];
				}
			}
		}

		$colors = \array_filter([
			$palette["package-{$package}-primary"] ?? '',
			$palette["package-{$package}-secondary"] ?? '',
		], static fn(string $color): bool => self::isHex($color));

		if ($colors === []) {
			return '';
		}

		\usort($colors, static fn(string $a, string $b): int => self::luminance($a) <=> self::luminance($b));

		return $colors[0];
	}

	/**
	 * Whether a string is a #rgb or #rrggbb hex color.
	 *
	 * @param string $color Color.
	 *
	 * @return bool
	 */
	private static function isHex(string $color): bool
	{
		return (bool) \preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $color);
	}

	/**
	 * WCAG relative luminance of a hex color.
	 *
	 * @param string $hex #rgb or #rrggbb.
	 *
	 * @return float
	 */
	private static function luminance(string $hex): float
	{
		$hex = \ltrim($hex, '#');

		if (\strlen($hex) === 3) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$channel = static function (string $pair): float {
			$c = \hexdec($pair) / 255;

			return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
		};

		return 0.2126 * $channel(\substr($hex, 0, 2))
			+ 0.7152 * $channel(\substr($hex, 2, 2))
			+ 0.0722 * $channel(\substr($hex, 4, 2));
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
