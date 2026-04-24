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
	 * Sets the per-unit price as pack_price / quantity so WooCommerce
	 * calculates the correct total.
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

			// Look up the correct pack tier for the current cart quantity.
			$packOption = $this->getPackOptionForQuantity($productId, $quantity);

			if ($packOption) {
				$packPrice = (float) $packOption['price'];

				// Update the stored pack data to match the current tier.
				$cart->cart_contents[$cartKey]['wc_pack_price'] = $packPrice;
				$cart->cart_contents[$cartKey]['wc_pack_label'] = $packOption['label'];
				$cart->cart_contents[$cartKey]['wc_pack_quantity'] = $quantity;
			} else {
				$packPrice = (float) $cartItem['wc_pack_price'];
			}

			if ($quantity > 0) {
				// Set per-unit price so WC total = per_unit * qty = pack_price.
				$perUnitPrice = $packPrice / $quantity;
				$product->set_price($perUnitPrice);
			}
		}
	}

	/**
	 * Look up the pack option matching a given quantity.
	 *
	 * @param int $productId Product ID.
	 * @param int $quantity  Cart quantity.
	 *
	 * @return array|null Matching pack option or null.
	 */
	private function getPackOptionForQuantity(int $productId, int $quantity): ?array
	{
		$rawOptions = \get_post_meta($productId, PackOptions::META_KEY, true);
		$packOptions = $rawOptions ? \json_decode($rawOptions, true) : [];

		foreach ($packOptions as $option) {
			if ((int) ($option['quantity'] ?? 0) === $quantity) {
				return $option;
			}
		}

		return null;
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
		$packOption = $this->getPackOptionForQuantity($productId, $quantity);
		$packPrice = $packOption ? (float) $packOption['price'] : (float) $cartItem['wc_pack_price'];

		if ($quantity > 0) {
			$perUnit = $packPrice / $quantity;
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
		$packOption = $this->getPackOptionForQuantity($productId, $quantity);
		$packPrice = $packOption ? (float) $packOption['price'] : (float) $cartItem['wc_pack_price'];

		return \wc_price($packPrice);
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
		$packOption = $this->getPackOptionForQuantity($productId, $quantity);
		$label = $packOption ? \esc_html($packOption['label']) : \esc_html($cartItem['wc_pack_label'] ?? '');

		if (!empty($label)) {
			return $name . ' <span class="pack-label">(' . $label . ')</span>';
		}

		return $name;
	}
}
