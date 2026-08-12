<?php

/**
 * The class register route for SingleProductPreview endpoint.
 *
 * @package Delta9DigitalBlocksPlugin\Rest\Routes
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\Rest\Routes;

use Delta9DigitalBlocksPlugin\Config\Config;
use Delta9DigitalBlocksPlugin\SingleProduct\SingleProductData;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Rest\CallableRouteInterface;
use WP_REST_Request;

/**
 * Class SingleProductPreviewRoute
 *
 * Editor-only endpoint that returns the same flavor state the block's PHP
 * render seeds via wp_interactivity_state(), so the editor preview can show
 * the real product (and its sibling flavors) when the block is edited inside
 * a product post instead of the fixture data used in template context.
 */
class SingleProductPreviewRoute extends AbstractRoute implements CallableRouteInterface
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
		return '/single-product/preview';
	}

	/**
	 * Get callback arguments array.
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => static::READABLE,
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => static function () {
				return \current_user_can('edit_posts');
			},
			'args' => [
				'productId' => [
					'type' => 'integer',
					'required' => true,
					'sanitize_callback' => 'absint',
				],
			],
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
		if (!\function_exists('wc_get_product')) {
			return \rest_ensure_response([
				'success' => false,
				'message' => 'WooCommerce is not available.',
			]);
		}

		$product = \wc_get_product((int) $request->get_param('productId'));

		if (!$product instanceof \WC_Product) {
			return \rest_ensure_response([
				'success' => false,
				'message' => 'Product not found.',
			]);
		}

		$state = SingleProductData::getState($product);

		if (!$state) {
			return \rest_ensure_response([
				'success' => false,
				'message' => 'Product has no resolvable top-level category.',
			]);
		}

		return \rest_ensure_response([
			'success' => true,
			'activeId' => $state['active']['id'],
			'flavors' => $state['flavors'],
		]);
	}
}
