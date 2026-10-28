<?php

/**
 * Product badges: "#1 Best Seller", "New", "Coming Soon", "Staff Favorite",
 * "Price Drop", "Sold Out".
 *
 * Editors tag a product under Products > Badges (the `product_badge`
 * taxonomy). Two badges are not editorial but come straight from
 * WooCommerce: a product that is out of stock reads "Sold Out" (or "Coming
 * Soon" when it also carries that badge), and a product with a sale price
 * reads "Price Drop". One badge shows per product, picked by priority.
 *
 * The badge is printed into every woocommerce/product-image block (homepage
 * grids, category and tag archives, related products) and into the
 * single-product block's hero and flavor cards.
 *
 * @package Delta9DigitalBlocksPlugin\ProductBadges
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\ProductBadges;

use Delta9DigitalBlocksPlugin\OutOfStock\OutOfStock;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Services\ServiceInterface;
use WP_Block;
use WP_HTML_Tag_Processor;

/**
 * Class ProductBadges
 */
class ProductBadges implements ServiceInterface
{
	/**
	 * Taxonomy name.
	 */
	public const TAXONOMY = 'product_badge';

	/**
	 * Slugs for the badges WooCommerce decides on its own.
	 */
	public const SLUG_SOLD_OUT = 'sold-out';
	public const SLUG_PRICE_DROP = 'price-drop';
	public const SLUG_COMING_SOON = 'coming-soon';

	/**
	 * Option holding the seeded-terms version, so a deploy (which does not
	 * re-run plugin activation) still gets the default terms.
	 */
	private const SEED_OPTION = 'yb_product_badges_seed_version';
	private const SEED_VERSION = 1;

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('init', [$this, 'registerTaxonomy'], 5);
		\add_action('init', [$this, 'maybeSeedTerms'], 20);
		// After OutOfStock (10): an out-of-stock product already has its badge.
		\add_filter('render_block_woocommerce/product-image', [$this, 'addBadgeToProductImage'], 11, 3);
	}

	/**
	 * Default terms, in display priority order: slug => label.
	 *
	 * `coming-soon` is editorial too (it can show on an in-stock product),
	 * `sold-out` and `price-drop` are not terms.
	 *
	 * @return array<string, string>
	 */
	public static function defaultTerms(): array
	{
		return (array) \apply_filters('yb_product_badge_default_terms', [
			self::SLUG_COMING_SOON => \__('Coming Soon', 'delta9-digital-blocks-plugin'),
			'best-seller' => \__('#1 Best Seller', 'delta9-digital-blocks-plugin'),
			'new' => \__('New', 'delta9-digital-blocks-plugin'),
			'staff-favorite' => \__('Staff Favorite', 'delta9-digital-blocks-plugin'),
		]);
	}

	/**
	 * Register the `product_badge` taxonomy on products.
	 *
	 * Non-hierarchical (no parents), but shown as a checklist rather than a
	 * free-text tag box so editors pick from the existing badges. No archive
	 * pages: badges are a display flag, not a browse facet.
	 *
	 * @return void
	 */
	public function registerTaxonomy(): void
	{
		\register_taxonomy(self::TAXONOMY, ['product'], [
			'labels' => [
				'name' => \_x('Badges', 'taxonomy general name', 'delta9-digital-blocks-plugin'),
				'singular_name' => \_x('Badge', 'taxonomy singular name', 'delta9-digital-blocks-plugin'),
				'menu_name' => \__('Badges', 'delta9-digital-blocks-plugin'),
				'all_items' => \__('All Badges', 'delta9-digital-blocks-plugin'),
				'edit_item' => \__('Edit Badge', 'delta9-digital-blocks-plugin'),
				'update_item' => \__('Update Badge', 'delta9-digital-blocks-plugin'),
				'add_new_item' => \__('Add New Badge', 'delta9-digital-blocks-plugin'),
				'new_item_name' => \__('New Badge Name', 'delta9-digital-blocks-plugin'),
				'search_items' => \__('Search Badges', 'delta9-digital-blocks-plugin'),
				'not_found' => \__('No badges found.', 'delta9-digital-blocks-plugin'),
				'back_to_items' => \__('← Back to Badges', 'delta9-digital-blocks-plugin'),
			],
			'description' => \__('Label shown on the product image, e.g. "#1 Best Seller". One badge shows per product; "Sold Out" and "Price Drop" come from stock status and sale price automatically.', 'delta9-digital-blocks-plugin'),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_in_rest' => true,
			'show_tagcloud' => false,
			'show_in_quick_edit' => true,
			'show_admin_column' => true,
			'hierarchical' => false,
			'meta_box_cb' => 'post_categories_meta_box',
			'query_var' => false,
			'rewrite' => false,
		]);
	}

	/**
	 * Create the default terms once per seed version.
	 *
	 * @return void
	 */
	public function maybeSeedTerms(): void
	{
		if ((int) \get_option(self::SEED_OPTION, 0) >= self::SEED_VERSION) {
			return;
		}

		self::seedTerms();
	}

	/**
	 * Create any default term that does not exist yet. Idempotent; existing
	 * terms (including renamed ones) are left alone.
	 *
	 * @return void
	 */
	public static function seedTerms(): void
	{
		if (!\taxonomy_exists(self::TAXONOMY)) {
			return;
		}

		foreach (self::defaultTerms() as $slug => $label) {
			if (\term_exists($slug, self::TAXONOMY)) {
				continue;
			}

			\wp_insert_term($label, self::TAXONOMY, ['slug' => $slug]);
		}

		\update_option(self::SEED_OPTION, self::SEED_VERSION, false);
	}

	/**
	 * Whether the product carries the "Coming Soon" badge.
	 *
	 * @param int $productId Product post id.
	 *
	 * @return bool
	 */
	public static function isComingSoon(int $productId): bool
	{
		return $productId > 0 && \has_term(self::SLUG_COMING_SOON, self::TAXONOMY, $productId);
	}

	/**
	 * Text for the automatic "Price Drop" badge.
	 *
	 * @return string
	 */
	public static function getPriceDropLabel(): string
	{
		return (string) \apply_filters('wc_price_drop_badge_text', \__('Price Drop', 'delta9-digital-blocks-plugin'));
	}

	/**
	 * The one badge to show for a product, or null.
	 *
	 * Priority: out of stock ("Sold Out", or "Coming Soon" when tagged) >
	 * on sale ("Price Drop") > editorial terms in `defaultTerms()` order >
	 * any other term alphabetically. Filterable via `yb_product_badge`.
	 *
	 * @param int $productId Product post id.
	 *
	 * @return array{slug: string, label: string}|null
	 */
	public static function getBadge(int $productId): ?array
	{
		if ($productId <= 0) {
			return null;
		}

		$badge = null;

		if (OutOfStock::isOutOfStock($productId)) {
			$badge = self::isComingSoon($productId)
				? ['slug' => self::SLUG_COMING_SOON, 'label' => OutOfStock::getLabel($productId)]
				: ['slug' => self::SLUG_SOLD_OUT, 'label' => OutOfStock::getLabel($productId)];
		} elseif (self::isOnSale($productId)) {
			$badge = ['slug' => self::SLUG_PRICE_DROP, 'label' => self::getPriceDropLabel()];
		} else {
			$badge = self::getTermBadge($productId);
		}

		$badge = \apply_filters('yb_product_badge', $badge, $productId);

		if (!\is_array($badge) || empty($badge['label'])) {
			return null;
		}

		return [
			'slug' => \sanitize_title((string) ($badge['slug'] ?? 'custom')),
			'label' => (string) $badge['label'],
		];
	}

	/**
	 * Highest-priority editorial badge term on the product, or null.
	 *
	 * @param int $productId Product post id.
	 *
	 * @return array{slug: string, label: string}|null
	 */
	private static function getTermBadge(int $productId): ?array
	{
		$terms = \get_the_terms($productId, self::TAXONOMY);

		if (!\is_array($terms) || $terms === []) {
			return null;
		}

		$order = \array_keys(self::defaultTerms());

		\usort($terms, static function ($a, $b) use ($order): int {
			$ia = \array_search($a->slug, $order, true);
			$ib = \array_search($b->slug, $order, true);
			$ia = $ia === false ? \PHP_INT_MAX : $ia;
			$ib = $ib === false ? \PHP_INT_MAX : $ib;

			return $ia <=> $ib ?: \strcasecmp($a->name, $b->name);
		});

		return [
			'slug' => $terms[0]->slug,
			'label' => $terms[0]->name,
		];
	}

	/**
	 * Whether the product has an active WooCommerce sale price.
	 *
	 * @param int $productId Product post id.
	 *
	 * @return bool
	 */
	private static function isOnSale(int $productId): bool
	{
		if (!\function_exists('wc_get_product')) {
			return false;
		}

		$product = \wc_get_product($productId);

		return $product instanceof \WC_Product && $product->is_on_sale();
	}

	/**
	 * Render the product-badge component for a badge array.
	 *
	 * @param array{slug: string, label: string} $badge Badge.
	 * @param string $additionalClass Extra class on the pill.
	 *
	 * @return string
	 */
	public static function renderBadge(array $badge, string $additionalClass = ''): string
	{
		return Helpers::render('product-badge', [
			'productBadgeSlug' => $badge['slug'],
			'productBadgeLabel' => $badge['label'],
			'additionalClass' => $additionalClass,
		]);
	}

	/**
	 * Print the badge inside an in-stock product's image block.
	 *
	 * Out-of-stock products are handled by OutOfStock at priority 10, which
	 * already printed theirs.
	 *
	 * @param string $content Rendered block markup.
	 * @param array<string, mixed> $parsedBlock Parsed block.
	 * @param WP_Block|null $instance Block instance; carries the loop's postId.
	 *
	 * @return string
	 */
	public function addBadgeToProductImage(string $content, array $parsedBlock, $instance = null): string
	{
		$productId = self::resolveProductId($parsedBlock, $instance);

		if ($content === '' || !$productId || OutOfStock::isOutOfStock($productId)) {
			return $content;
		}

		$badge = self::getBadge($productId);

		if ($badge === null) {
			return $content;
		}

		return self::injectIntoProductImage($content, self::renderBadge($badge), 'has-product-badge');
	}

	/**
	 * Product id for a product-image block: explicit attribute, then the
	 * product-collection loop context, then the global post.
	 *
	 * @param array<string, mixed> $parsedBlock Parsed block.
	 * @param WP_Block|null $instance Block instance.
	 *
	 * @return int
	 */
	public static function resolveProductId(array $parsedBlock, $instance = null): int
	{
		$productId = (int) ($parsedBlock['attrs']['productId'] ?? 0);

		if (!$productId && $instance instanceof WP_Block) {
			$productId = (int) ($instance->context['postId'] ?? 0);
		}

		if (!$productId) {
			$productId = (int) \get_the_ID();
		}

		return $productId;
	}

	/**
	 * Add a class to the product-image wrapper and print markup right after
	 * its opening tag.
	 *
	 * @param string $content Rendered product-image block.
	 * @param string $markup Markup to insert.
	 * @param string $wrapperClass Class to add to the wrapper.
	 *
	 * @return string
	 */
	public static function injectIntoProductImage(string $content, string $markup, string $wrapperClass): string
	{
		$tags = new WP_HTML_Tag_Processor($content);

		if (!$tags->next_tag(['class_name' => 'wc-block-components-product-image'])) {
			return $content;
		}

		$tags->add_class($wrapperClass);
		$content = $tags->get_updated_html();

		// Attribute values are escaped, so the first `>` after the class
		// closes the wrapper's opening tag.
		$classAt = \strpos($content, 'wc-block-components-product-image');
		$openEnd = $classAt === false ? false : \strpos($content, '>', $classAt);

		if ($openEnd === false) {
			return $content;
		}

		return \substr_replace($content, $markup, $openEnd + 1, 0);
	}
}
