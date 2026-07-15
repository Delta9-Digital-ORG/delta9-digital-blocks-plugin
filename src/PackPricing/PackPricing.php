<?php

/**
 * Pack pricing service for WooCommerce cart price overrides.
 *
 * Handles in-memory price adjustments for pack/bundle items in the cart.
 * Prices are never written to product meta (Square-safe).
 *
 * @package Delta9DigitalBlocksPlugin\PackPricing
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\PackPricing;

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Services\ServiceInterface;
use Delta9DigitalBlocksPlugin\PackOptions\PackOptions;

/**
 * Class PackPricing
 */
class PackPricing implements ServiceInterface
{
	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Stamp pack data onto cart item during add_to_cart.
		\add_filter('woocommerce_add_cart_item_data', [$this, 'addPackCartItemData'], 10, 3);

		// Apply pack price override — priority 101 to run after BundlePricing (100).
		\add_action('woocommerce_before_calculate_totals', [$this, 'applyPackPricing'], 101);

		// Re-hydrate pack metadata from session.
		\add_filter('woocommerce_get_cart_item_from_session', [$this, 'restorePackData'], 10, 3);

		// Show pack pricing in cart.
		\add_filter('woocommerce_cart_item_price', [$this, 'displayCartItemPrice'], 11, 3);
		\add_filter('woocommerce_cart_item_subtotal', [$this, 'displayCartItemSubtotal'], 11, 3);

		// Display pack label in cart item name.
		\add_filter('woocommerce_cart_item_name', [$this, 'displayCartItemName'], 10, 3);
	}

	/**
	 * Capture pack price from POST data and stamp onto cart item.
	 *
	 * @param array $cartItemData Cart item data array.
	 * @param int   $productId    Product ID.
	 * @param int   $variationId  Variation ID.
	 *
	 * @return array Modified cart item data.
	 */
	public function addPackCartItemData(array $cartItemData, int $productId, int $variationId): array
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if (!empty($_POST['pack_price'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$cartItemData['wc_pack_price'] = \round(\floatval($_POST['pack_price']), 2);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if (!empty($_POST['pack_label'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$cartItemData['wc_pack_label'] = \sanitize_text_field(\wp_unslash($_POST['pack_label']));
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if (!empty($_POST['pack_quantity'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$cartItemData['wc_pack_quantity'] = \absint($_POST['pack_quantity']);
		}

		return $cartItemData;
	}

	/**
	 * Apply pack price to cart items.
	 *
	 * Computes the cumulative "step" total for the current cart quantity from
	 * the product's pack tiers, then sets the per-unit price as total / qty so
	 * WooCommerce calculates the correct line total.
	 *
	 * @param \WC_Cart $cart WooCommerce cart instance.
	 *
	 * @return void
	 */
	public function applyPackPricing($cart): void
	{
		if (\is_admin() && !\wp_doing_ajax()) {
			return;
		}

		if (\did_action('woocommerce_before_calculate_totals') >= 2) {
			return;
		}

		foreach ($cart->get_cart() as $cartKey => $cartItem) {
			if (empty($cartItem['wc_pack_price'])) {
				continue;
			}

			$product = $cartItem['data'];

			// Skip subscription products to avoid conflicts.
			if (\class_exists('WC_Subscriptions_Product') && \WC_Subscriptions_Product::is_subscription($product)) {
				continue;
			}

			$productId = (int) $cartItem['product_id'];
			$quantity = (int) $cartItem['quantity'];

			if ($quantity <= 0) {
				continue;
			}

			$options = $this->getPackOptions($productId);

			if (empty($options)) {
				continue;
			}

			// Cumulative step total for the current quantity.
			$total = $this->computePackTotal($options, $quantity);

			// Keep the stored pack metadata in sync with the active tier.
			$cart->cart_contents[$cartKey]['wc_pack_price'] = $total;
			$cart->cart_contents[$cartKey]['wc_pack_label'] = $this->getActiveTierLabel($options, $quantity);
			$cart->cart_contents[$cartKey]['wc_pack_quantity'] = $quantity;

			// Set per-unit price so WC line total = per_unit * qty = total.
			$product->set_price($total / $quantity);
		}
	}

	/**
	 * Read and normalise a product's pack tiers, sorted by quantity ascending.
	 *
	 * @param int $productId Product ID.
	 *
	 * @return array<int, array{label: string, quantity: int, price: float}> Sorted pack tiers.
	 */
	private function getPackOptions(int $productId): array
	{
		$rawOptions = \get_post_meta($productId, PackOptions::META_KEY, true);
		$packOptions = $rawOptions ? \json_decode($rawOptions, true) : [];

		if (!\is_array($packOptions)) {
			return [];
		}

		$options = [];

		foreach ($packOptions as $option) {
			$quantity = (int) ($option['quantity'] ?? 0);

			if ($quantity < 1) {
				continue;
			}

			$options[] = [
				'label' => (string) ($option['label'] ?? ''),
				'quantity' => $quantity,
				'price' => (float) ($option['price'] ?? 0),
			];
		}

		\usort($options, static function ($a, $b) {
			return $a['quantity'] <=> $b['quantity'];
		});

		return $options;
	}

	/**
	 * Resolve the base per-unit price used for units beyond the active tier.
	 *
	 * Prefers the price of the single-unit (quantity === 1) tier; otherwise
	 * falls back to the smallest tier's implied per-unit price.
	 *
	 * @param array<int, array{label: string, quantity: int, price: float}> $options Sorted pack tiers.
	 *
	 * @return float Base per-unit price.
	 */
	private function getBaseUnitPrice(array $options): float
	{
		foreach ($options as $option) {
			if ($option['quantity'] === 1) {
				return $option['price'];
			}
		}

		$first = $options[0] ?? null;

		if ($first && $first['quantity'] > 0) {
			return $first['price'] / $first['quantity'];
		}

		return 0.0;
	}

	/**
	 * Compute the cumulative "step" total for a given quantity.
	 *
	 * Applies the flat price of the largest tier whose quantity is <= $qty,
	 * then charges each remaining unit at the base per-unit price.
	 *
	 * @param array<int, array{label: string, quantity: int, price: float}> $options Sorted pack tiers.
	 * @param int                                                            $qty     Cart quantity.
	 *
	 * @return float Total price for the whole line.
	 */
	private function computePackTotal(array $options, int $qty): float
	{
		if (empty($options) || $qty < 1) {
			return 0.0;
		}

		$baseUnit = $this->getBaseUnitPrice($options);
		$total = 0.0;
		$remaining = $qty;

		// Greedily fill with the largest tier that fits the remaining quantity,
		// stacking bundles so the discount keeps applying past the top tier
		// (e.g. 15 = 48-pack + 12-pack). Any leftover below the smallest tier
		// bills at the base unit price. The smallest tier is normally quantity 1,
		// so a remainder of 1 just re-uses that tier (= base price).
		while ($remaining > 0) {
			$tier = $this->getActiveTier($options, $remaining);

			if (!$tier) {
				$total += $remaining * $baseUnit;
				break;
			}

			$total += (float) $tier['price'];
			$remaining -= (int) $tier['quantity'];
		}

		return $total;
	}

	/**
	 * Get the largest tier whose quantity is <= the given quantity.
	 *
	 * @param array<int, array{label: string, quantity: int, price: float}> $options Sorted pack tiers.
	 * @param int                                                            $qty     Cart quantity.
	 *
	 * @return array{label: string, quantity: int, price: float}|null Active tier or null.
	 */
	private function getActiveTier(array $options, int $qty): ?array
	{
		$active = null;

		foreach ($options as $option) {
			if ($option['quantity'] <= $qty) {
				$active = $option;
			}
		}

		return $active;
	}

	/**
	 * Get the label of the active tier for a given quantity.
	 *
	 * @param array<int, array{label: string, quantity: int, price: float}> $options Sorted pack tiers.
	 * @param int                                                            $qty     Cart quantity.
	 *
	 * @return string Active tier label, or empty string.
	 */
	private function getActiveTierLabel(array $options, int $qty): string
	{
		$tier = $this->getActiveTier($options, $qty);

		return $tier ? $tier['label'] : '';
	}

	/**
	 * Restore pack data from session.
	 *
	 * @param array  $sessionData Cart item data from session.
	 * @param array  $values      Raw session values.
	 * @param string $key         Cart item key.
	 *
	 * @return array Modified cart item data.
	 */
	public function restorePackData(array $sessionData, array $values, string $key): array
	{
		if (isset($values['wc_pack_price'])) {
			$sessionData['wc_pack_price'] = $values['wc_pack_price'];
		}

		if (isset($values['wc_pack_label'])) {
			$sessionData['wc_pack_label'] = $values['wc_pack_label'];
		}

		if (isset($values['wc_pack_quantity'])) {
			$sessionData['wc_pack_quantity'] = $values['wc_pack_quantity'];
		}

		return $sessionData;
	}

	/**
	 * Display pack price in cart item price column.
	 *
	 * @param string $price       HTML price string.
	 * @param array  $cartItem    Cart item data.
	 * @param string $cartItemKey Cart item key.
	 *
	 * @return string Modified price HTML.
	 */
	public function displayCartItemPrice(string $price, array $cartItem, string $cartItemKey): string
	{
		if (empty($cartItem['wc_pack_price'])) {
			return $price;
		}

		$productId = (int) $cartItem['product_id'];
		$quantity = (int) $cartItem['quantity'];
		$options = $this->getPackOptions($productId);
		$total = !empty($options)
			? $this->computePackTotal($options, $quantity)
			: (float) $cartItem['wc_pack_price'];

		if ($quantity > 0) {
			$perUnit = $total / $quantity;
			return \wc_price($perUnit);
		}

		return $price;
	}

	/**
	 * Display pack subtotal in cart item subtotal column.
	 *
	 * @param string $subtotal    HTML subtotal string.
	 * @param array  $cartItem    Cart item data.
	 * @param string $cartItemKey Cart item key.
	 *
	 * @return string Modified subtotal HTML.
	 */
	public function displayCartItemSubtotal(string $subtotal, array $cartItem, string $cartItemKey): string
	{
		if (empty($cartItem['wc_pack_price'])) {
			return $subtotal;
		}

		$productId = (int) $cartItem['product_id'];
		$quantity = (int) $cartItem['quantity'];
		$options = $this->getPackOptions($productId);
		$total = !empty($options)
			? $this->computePackTotal($options, $quantity)
			: (float) $cartItem['wc_pack_price'];

		return \wc_price($total);
	}

	/**
	 * Append pack label to cart item name.
	 *
	 * @param string $name        Product name HTML.
	 * @param array  $cartItem    Cart item data.
	 * @param string $cartItemKey Cart item key.
	 *
	 * @return string Modified name HTML.
	 */
	public function displayCartItemName(string $name, array $cartItem, string $cartItemKey): string
	{
		if (empty($cartItem['wc_pack_price'])) {
			return $name;
		}

		$productId = (int) $cartItem['product_id'];
		$quantity = (int) $cartItem['quantity'];
		$options = $this->getPackOptions($productId);
		$label = !empty($options)
			? \esc_html($this->getActiveTierLabel($options, $quantity))
			: \esc_html($cartItem['wc_pack_label'] ?? '');

		if (!empty($label)) {
			return $name . ' <span class="pack-label">(' . $label . ')</span>';
		}

		return $name;
	}
}
