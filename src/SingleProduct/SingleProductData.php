<?php

/**
 * Flavor-state builder for the Single Product block.
 *
 * Extracted from the block template (single-product.php) so the same
 * product → flavor-array resolution can serve both the frontend render
 * (wp_interactivity_state seed) and the editor-preview REST route.
 *
 * @package Delta9DigitalBlocksPlugin\SingleProduct
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\SingleProduct;

use WC_Product;

/**
 * Class SingleProductData
 */
final class SingleProductData
{
	/**
	 * Build the full block state for a product.
	 *
	 * @param WC_Product $product Product the page/preview is for.
	 * @param int[] $explicitIds Optional explicit sibling override from the block attribute.
	 *
	 * @return array{active: array<string, mixed>, flavors: array<int, array<string, mixed>>}|null
	 *         Null when the product has no resolvable top-level category.
	 */
	public static function getState(WC_Product $product, array $explicitIds = []): ?array
	{
		$topCat = self::resolveTopCategory($product->get_id());

		if (!$topCat) {
			return null;
		}

		$queryArgs = [
			'limit' => -1,
			'status' => 'publish',
			'orderby' => 'menu_order',
			'order' => 'ASC',
		];

		if (!empty($explicitIds)) {
			$queryArgs['include'] = \array_map('intval', $explicitIds);
		} else {
			$queryArgs['category'] = [$topCat->slug];
		}

		$siblings = \wc_get_products($queryArgs);

		if (empty($siblings)) {
			$siblings = [$product];
		}

		$flavors = \array_values(\array_map([self::class, 'buildFlavor'], $siblings));

		// Active flavor — the current product if it's in the sibling list,
		// otherwise fall back to the first sibling.
		$active = null;
		foreach ($flavors as $flavor) {
			if ($flavor['id'] === $product->get_id()) {
				$active = $flavor;
				break;
			}
		}
		if (!$active) {
			$active = $flavors[0];
		}

		return [
			'active' => $active,
			'flavors' => $flavors,
		];
	}

	/**
	 * Resolve the product's top-level product_cat.
	 *
	 * The taxonomy has a wrapper term `product-categories` sitting above the
	 * real top-level buckets (`beverages`, `gummies`, `chocolate-bars`), so we
	 * walk ancestors and pick the term whose direct parent is that wrapper
	 * (or the term itself if it has no parent at all).
	 *
	 * @param int $productId Product post ID.
	 *
	 * @return \WP_Term|null
	 */
	private static function resolveTopCategory(int $productId): ?\WP_Term
	{
		$wrapper = \get_term_by('slug', 'product-categories', 'product_cat');
		$wrapperId = ($wrapper && !\is_wp_error($wrapper)) ? (int) $wrapper->term_id : 0;

		$termIds = \wp_get_post_terms($productId, 'product_cat', ['fields' => 'ids']);
		if (\is_wp_error($termIds)) {
			return null;
		}

		foreach ($termIds as $termId) {
			$current = \get_term((int) $termId, 'product_cat');
			while ($current && !\is_wp_error($current)) {
				// Match only the direct child of the wrapper. Without the wrapper
				// fall back to a true top-level term (parent === 0).
				if ($wrapperId ? (int) $current->parent === $wrapperId : 0 === (int) $current->parent) {
					return $current;
				}
				if (0 === (int) $current->parent) {
					// Hit a top-level that isn't under the wrapper — skip and try
					// the next assigned term.
					break;
				}
				$current = \get_term((int) $current->parent, 'product_cat');
			}
		}

		return null;
	}

	/**
	 * Resolve the top-level Mood term name for a product.
	 *
	 * Walks the product's product_cat assignments and returns the name of
	 * the first term that is a direct child of the `mood` wrapper term
	 * (Anytime / Daytime / Nighttime). Walks up the parent chain so deeper
	 * sub-terms still resolve to their top-level mood.
	 *
	 * @param int $productId Product post ID.
	 *
	 * @return string Mood label, or empty string if none.
	 */
	private static function resolveMood(int $productId): string
	{
		$wrapper = \get_term_by('slug', 'mood', 'product_cat');
		if (!$wrapper || \is_wp_error($wrapper)) {
			return '';
		}
		$wrapperId = (int) $wrapper->term_id;

		$termIds = \wp_get_post_terms($productId, 'product_cat', ['fields' => 'ids']);
		if (\is_wp_error($termIds) || empty($termIds)) {
			return '';
		}

		foreach ($termIds as $termId) {
			$current = \get_term((int) $termId, 'product_cat');
			while ($current && !\is_wp_error($current)) {
				if ((int) $current->parent === $wrapperId) {
					return (string) $current->name;
				}
				if (0 === (int) $current->parent) {
					break;
				}
				$current = \get_term((int) $current->parent, 'product_cat');
			}
		}

		return '';
	}

	/**
	 * Build one flavor entry. Keys mirror the fixture shape from
	 * _mockups/single-product/app.js, so the view JS bindings don't change.
	 *
	 * @param WC_Product $p Product.
	 *
	 * @return array<string, mixed>
	 */
	private static function buildFlavor(WC_Product $p): array
	{
		$id = $p->get_id();

		// Pack options drive the size picker. Same meta key + shape as the
		// dropdown-picker block (`_dropdown_pack_options`) so PackPricing
		// server-side hooks (priceHtml lookups, cart price overrides) work
		// without changes.
		$rawPack = \get_post_meta($id, '_dropdown_pack_options', true);
		$packsIn = $rawPack ? \json_decode($rawPack, true) : [];
		$packOptions = [];
		if (\is_array($packsIn)) {
			foreach ($packsIn as $opt) {
				$price = (float) ($opt['price'] ?? 0);
				$packOptions[] = [
					'label' => (string) ($opt['label'] ?? ''),
					'quantity' => (int) ($opt['quantity'] ?? 1),
					'price' => $price,
					'priceHtml' => \html_entity_decode(\wp_strip_all_tags(\wc_price($price)), \ENT_QUOTES, 'UTF-8'),
				];
			}
		}

		// Short label override for the flavor-card chip text. When set,
		// only the small card label uses this; the buy-card h2, wordmark,
		// and image alt keep using the full product name.
		$cardLabel = (string) \get_post_meta($id, '_yb_card_label', true);

		// Full WC gallery (featured + gallery images) — feeds the
		// product-image-gallery-slot block via iAPI state so external slot
		// blocks swap their image when the flavor picker changes the active
		// product.
		$galleryIds = \array_filter(\array_merge(
			[(int) $p->get_image_id()],
			\array_map('intval', (array) $p->get_gallery_image_ids())
		));
		$gallery = \array_values(\array_filter(\array_map(static function ($aid) use ($p) {
			$src = \wp_get_attachment_image_url((int) $aid, 'full');
			if (!$src) {
				return null;
			}
			$alt = (string) \get_post_meta((int) $aid, '_wp_attachment_image_alt', true);
			if ('' === $alt) {
				$alt = $p->get_name();
			}
			return [
				'id' => (int) $aid,
				'src' => (string) $src,
				'alt' => $alt,
			];
		}, $galleryIds)));

		return [
			'id' => $id,
			'name' => $p->get_name(),
			'cardLabel' => $cardLabel ?: $p->get_name(),
			'permalink' => \get_permalink($id),
			'priceHtml' => \html_entity_decode(\wp_strip_all_tags($p->get_price_html()), \ENT_QUOTES, 'UTF-8'),
			'subtitle' => (string) \get_post_meta($id, '_custom_product_servings_per_container_text_field', true),
			'image' => \wp_get_attachment_image_url($p->get_image_id(), 'large') ?: '',
			'cardImage' => \wp_get_attachment_image_url($p->get_image_id(), 'medium') ?: '',
			'cardBg' => (string) \get_post_meta($id, '_yb_card_bg', true),
			'nameColor' => (string) \get_post_meta($id, '_yb_name_color', true),
			'pageBg' => (string) \get_post_meta($id, '_yb_page_bg', true),
			'pageContrast' => (string) \get_post_meta($id, '_yb_name_color', true),
			'description' => (string) \get_post_meta($id, '_custom_product_description_text_field', true),
			'cannafacts' => \trim(\implode("\n", \array_filter([
				\get_post_meta($id, '_custom_product_per_serving_text_field', true),
				\get_post_meta($id, '_custom_product_serving_size_text_field', true),
				\get_post_meta($id, '_custom_product_servings_per_container_text_field', true),
			]))),
			'ingredients' => (string) \get_post_meta($id, '_custom_product_ingredients_text_field', true),
			'starsAvg' => (float) $p->get_average_rating(),
			'reviewCount' => (int) $p->get_review_count(),
			'packOptions' => $packOptions,
			'mood' => self::resolveMood($id),
			'gallery' => $gallery,
		];
	}
}
