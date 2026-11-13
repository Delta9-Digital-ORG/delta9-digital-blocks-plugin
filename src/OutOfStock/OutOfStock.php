<?php

/**
 * Out-of-stock badge for WooCommerce product images.
 *
 * Product grids keep showing out-of-stock products (the product-collection
 * blocks include `outofstock` in their stock filter); this marks them as
 * sold out instead of letting them disappear. Any woocommerce/product-image
 * block whose product is out of stock gets an `is-out-of-stock` class and the
 * out-of-stock-badge component printed inside it.
 *
 * @package Delta9DigitalBlocksPlugin\OutOfStock
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\OutOfStock;

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;
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
	}

	/**
	 * Badge and button text for an out-of-stock product.
	 *
	 * @return string
	 */
	public static function getLabel(): string
	{
		return (string) \apply_filters('wc_out_of_stock_badge_text', \__('Sold Out', 'delta9-digital-blocks-plugin'));
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
		$productId = (int) ($parsedBlock['attrs']['productId'] ?? 0);

		if (!$productId && $instance instanceof WP_Block) {
			$productId = (int) ($instance->context['postId'] ?? 0);
		}

		if (!$productId) {
			$productId = (int) \get_the_ID();
		}

		if ($content === '' || !self::isOutOfStock($productId)) {
			return $content;
		}

		$tags = new WP_HTML_Tag_Processor($content);

		if (!$tags->next_tag(['class_name' => 'wc-block-components-product-image'])) {
			return $content;
		}

		$tags->add_class('is-out-of-stock');
		$content = $tags->get_updated_html();

		// Badge goes right after the wrapper's opening tag. Attribute values
		// are escaped, so the first `>` after the class closes that tag.
		$classAt = \strpos($content, 'wc-block-components-product-image');
		$openEnd = $classAt === false ? false : \strpos($content, '>', $classAt);

		if ($openEnd === false) {
			return $content;
		}

		$badge = Helpers::render('out-of-stock-badge', []);

		return \substr_replace($content, $badge, $openEnd + 1, 0);
	}
}
