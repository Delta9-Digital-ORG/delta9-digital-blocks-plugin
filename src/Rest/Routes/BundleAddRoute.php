<?php

/**
 * The class register route for BundleAdd endpoint.
 *
 * @package Delta9DigitalBlocksPlugin\Rest\Routes
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\Rest\Routes;

use Delta9DigitalBlocksPlugin\Config\Config;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Rest\CallableRouteInterface;
use WP_REST_Request;

/**
 * Class BundleAddRoute
 */
class BundleAddRoute extends AbstractRoute implements CallableRouteInterface
{
	/**
	 * Method that returns project Route namespace.
	 *
	 * @return string Project namespace for REST route.
	 */
	protected function getNamespace(): string
	{
		return Config::getProjectRoutesNamespace();
	}

	/**
	 * Method that returns project route version.
	 *
	 * @return string Route version as a string.
	 */
	protected function getVersion(): string
	{
		return Config::getProjectRoutesVersion();
	}

	/**
	 * Get the base url of the route.
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/bundle/add';
	}

	/**
	 * Get callback arguments array.
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => static::CREATABLE,
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => function () {
				return wp_verify_nonce(
					sanitize_text_field($_SERVER['HTTP_X_WP_NONCE'] ?? ''),
					'wp_rest'
				) !== false;
			},
		];
	}

	/**
	 * Method that returns rest response.
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return \WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                 is already an instance, WP_HTTP_Response, otherwise
	 *                                 returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		$body = \json_decode($request->get_body(), true);

		$items = $body['items'] ?? [];
		$tiers = $body['tiers'] ?? [];

		if (empty($items)) {
			return \rest_ensure_response([
				'success' => false,
				'message' => 'No items provided.',
			]);
		}

		// Ensure WooCommerce cart is loaded.
		if (!function_exists('WC') || !WC()->cart) {
			return \rest_ensure_response([
				'success' => false,
				'message' => 'WooCommerce cart is not available.',
			]);
		}

		$cart = WC()->cart;

		// Generate a unique bundle key.
		$bundleKey = wp_generate_uuid4();

		// Temporarily remove the calculate_totals hook to prevent n recalculations.
		remove_action('woocommerce_add_to_cart', [$cart, 'calculate_totals'], 20);

		$addedItems = [];
		$errors = [];

		foreach ($items as $item) {
			$productId = absint($item['product_id'] ?? 0);
			$quantity = absint($item['quantity'] ?? 0);

			if ($productId <= 0 || $quantity <= 0) {
				continue;
			}

			$product = wc_get_product($productId);

			if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
				$errors[] = sprintf('Product %d is not available.', $productId);
				continue;
			}

			$cartItemData = [
				'wc_bundle_key' => $bundleKey,
				'wc_bundle_tiers' => $tiers,
			];

			$cartItemKey = $cart->add_to_cart($productId, $quantity, 0, [], $cartItemData);

			if ($cartItemKey) {
				$addedItems[] = [
					'product_id' => $productId,
					'quantity' => $quantity,
					'cart_item_key' => $cartItemKey,
				];
			} else {
				$errors[] = sprintf('Failed to add product %d to cart.', $productId);
			}
		}

		// Re-add the calculate_totals hook and calculate once.
		add_action('woocommerce_add_to_cart', [$cart, 'calculate_totals'], 20);
		$cart->calculate_totals();

		// Get cart fragments for mini-cart update.
		$fragments = $this->getCartFragments();

		if (empty($addedItems)) {
			return \rest_ensure_response([
				'success' => false,
				'message' => !empty($errors) ? implode(' ', $errors) : 'No items were added.',
			]);
		}

		return \rest_ensure_response([
			'success' => true,
			'message' => sprintf('%d item(s) added to cart.', count($addedItems)),
			'bundle_key' => $bundleKey,
			'items' => $addedItems,
			'fragments' => $fragments,
			'cart_total' => $cart->get_total('edit'),
		]);
	}

	/**
	 * Get WooCommerce cart fragments for mini-cart refresh.
	 *
	 * @return array<string, string> Cart fragment HTML keyed by selector.
	 */
	private function getCartFragments(): array
	{
		$fragments = [];

		// Trigger the WooCommerce fragments filter.
		ob_start();
		\woocommerce_mini_cart();
		$miniCart = ob_get_clean();

		$fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . $miniCart . '</div>';

		return \apply_filters('woocommerce_add_to_cart_fragments', $fragments);
	}
}
