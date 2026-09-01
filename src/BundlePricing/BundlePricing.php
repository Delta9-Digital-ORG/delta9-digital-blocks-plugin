<?php

/**
 * Bundle pricing service class for WooCommerce tiered discounts.
 *
 * Handles in-memory price adjustments for bundle items in the cart.
 * Prices are never written to product meta (Square-safe).
 *
 * @package Delta9DigitalBlocksPlugin\BundlePricing
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\BundlePricing;

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class BundlePricing
 */
class BundlePricing implements ServiceInterface
{
	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Apply tiered discount pricing — priority 100 to run after WooCommerce Subscriptions (0, 10, 99).
		\add_action('woocommerce_before_calculate_totals', [$this, 'applyBundleDiscount'], 100);

		// Re-hydrate bundle metadata from session.
		\add_filter('woocommerce_get_cart_item_from_session', [$this, 'restoreBundleData'], 10, 3);

		// Show strikethrough pricing in cart.
		\add_filter('woocommerce_cart_item_price', [$this, 'displayCartItemPrice'], 10, 3);
		\add_filter('woocommerce_cart_item_subtotal', [$this, 'displayCartItemSubtotal'], 10, 3);

		// Stamp bundle metadata onto cart item data.
		\add_filter('woocommerce_add_cart_item_data', [$this, 'addBundleCartItemData'], 10, 3);
	}

	/**
	 * Apply tiered discount to bundle items in the cart.
	 *
	 * Groups items by wc_bundle_key, calculates total qty,
	 * determines the active tier, and sets discounted prices in-memory.
	 *
	 * @param \WC_Cart $cart WooCommerce cart instance.
	 *
	 * @return void
	 */
	public function applyBundleDiscount($cart): void
	{
		if (\is_admin() && !\wp_doing_ajax()) {
			return;
		}

		if (\did_action('woocommerce_before_calculate_totals') >= 2) {
			return;
		}

		// Group cart items by bundle key.
		$bundles = [];

		foreach ($cart->get_cart() as $cartItemKey => $cartItem) {
			if (empty($cartItem['wc_bundle_key'])) {
				continue;
			}

			$bundleKey = $cartItem['wc_bundle_key'];

			if (!isset($bundles[$bundleKey])) {
				$bundles[$bundleKey] = [
					'tiers' => $cartItem['wc_bundle_tiers'] ?? [],
					'items' => [],
					'total_qty' => 0,
				];
			}

			$bundles[$bundleKey]['items'][$cartItemKey] = $cartItem;
			$bundles[$bundleKey]['total_qty'] += $cartItem['quantity'];
		}

		// Apply tier discounts per bundle group.
		foreach ($bundles as $bundle) {
			$activeTier = $this->getActiveTier($bundle['tiers'], $bundle['total_qty']);

			if (!$activeTier) {
				continue;
			}

			$discountPct = (float) ($activeTier['pct'] ?? 0);

			if ($discountPct <= 0) {
				continue;
			}

			foreach ($bundle['items'] as $cartItem) {
				$product = $cartItem['data'];

				// Skip subscription products to avoid conflicts.
				if (\class_exists('WC_Subscriptions_Product') && \WC_Subscriptions_Product::is_subscription($product)) {
					continue;
				}

				// Get the original price (bypassing view-context filters).
				$originalPrice = (float) $product->get_price('edit');
				$discountedPrice = $originalPrice * (1 - $discountPct / 100);

				// Set discounted price in-memory only.
				$product->set_price($discountedPrice);
			}
		}
	}

	/**
	 * Get the active tier for a given quantity.
	 *
	 * @param array $tiers Array of tier rules with 'min' and 'pct' keys.
	 * @param int   $qty   Total quantity of items in the bundle.
	 *
	 * @return array|null The active tier or null if no tier qualifies.
	 */
	private function getActiveTier(array $tiers, int $qty): ?array
	{
		// Sort tiers by min descending, pick the highest qualifying tier.
		usort($tiers, function ($a, $b) {
			return ($b['min'] ?? 0) - ($a['min'] ?? 0);
		});

		foreach ($tiers as $tier) {
			if ($qty >= ($tier['min'] ?? 0)) {
				return $tier;
			}
		}

		return null;
	}

	/**
	 * Restore bundle data from session.
	 *
	 * @param array  $sessionData  Cart item data from session.
	 * @param array  $values       Raw session values.
	 * @param string $key          Cart item key.
	 *
	 * @return array Modified cart item data.
	 */
	public function restoreBundleData(array $sessionData, array $values, string $key): array
	{
		if (isset($values['wc_bundle_key'])) {
			$sessionData['wc_bundle_key'] = $values['wc_bundle_key'];
		}

		if (isset($values['wc_bundle_tiers'])) {
			$sessionData['wc_bundle_tiers'] = $values['wc_bundle_tiers'];
		}

		return $sessionData;
	}

	/**
	 * Stamp bundle metadata onto cart item data during add_to_cart.
	 *
	 * @param array $cartItemData Cart item data array.
	 * @param int   $productId    Product ID.
	 * @param int   $variationId  Variation ID.
	 *
	 * @return array Modified cart item data.
	 */
	public function addBundleCartItemData(array $cartItemData, int $productId, int $variationId): array
	{
		// Bundle data is already set by the REST route — just pass through.
		return $cartItemData;
	}

	/**
	 * Display strikethrough original price in cart item price column.
	 *
	 * @param string $price         HTML price string.
	 * @param array  $cartItem      Cart item data.
	 * @param string $cartItemKey   Cart item key.
	 *
	 * @return string Modified price HTML.
	 */
	public function displayCartItemPrice(string $price, array $cartItem, string $cartItemKey): string
	{
		if (empty($cartItem['wc_bundle_key']) || empty($cartItem['wc_bundle_tiers'])) {
			return $price;
		}

		$product = $cartItem['data'];
		$originalPrice = (float) $product->get_price('edit');
		$currentPrice = (float) $product->get_price();

		if ($currentPrice < $originalPrice) {
			return '<del>' . \wc_price($originalPrice) . '</del> ' . \wc_price($currentPrice);
		}

		return $price;
	}

	/**
	 * Display strikethrough original subtotal in cart item subtotal column.
	 *
	 * @param string $subtotal      HTML subtotal string.
	 * @param array  $cartItem      Cart item data.
	 * @param string $cartItemKey   Cart item key.
	 *
	 * @return string Modified subtotal HTML.
	 */
	public function displayCartItemSubtotal(string $subtotal, array $cartItem, string $cartItemKey): string
	{
		if (empty($cartItem['wc_bundle_key']) || empty($cartItem['wc_bundle_tiers'])) {
			return $subtotal;
		}

		$product = $cartItem['data'];
		$originalPrice = (float) $product->get_price('edit');
		$currentPrice = (float) $product->get_price();
		$qty = $cartItem['quantity'];

		if ($currentPrice < $originalPrice) {
			$originalSubtotal = $originalPrice * $qty;
			$discountedSubtotal = $currentPrice * $qty;

			return '<del>' . \wc_price($originalSubtotal) . '</del> ' . \wc_price($discountedSubtotal);
		}

		return $subtotal;
	}
}
         