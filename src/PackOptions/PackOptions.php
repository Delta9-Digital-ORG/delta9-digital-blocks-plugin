<?php

/**
 * Pack Options meta box for WooCommerce products.
 *
 * Adds a repeater-style meta box to configure per-product pack/bundle
 * options (label, quantity, price) that power the Dropdown Picker block.
 *
 * @package Delta9DigitalBlocksPlugin\PackOptions
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\PackOptions;

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class PackOptions
 */
class PackOptions implements ServiceInterface
{
	/**
	 * Meta key for storing pack options.
	 */
	public const META_KEY = '_dropdown_pack_options';

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('add_meta_boxes', [$this, 'addMetaBox']);
		\add_action('woocommerce_process_product_meta', [$this, 'savePackOptions']);
	}

	/**
	 * Add the Pack Options meta box to product edit screen.
	 *
	 * @return void
	 */
	public function addMetaBox(): void
	{
		\add_meta_box(
			'dropdown_pack_options',
			\__('Pack Options', 'delta9-digital-blocks-plugin'),
			[$this, 'renderMetaBox'],
			'product',
			'normal',
			'default'
		);
	}

	/**
	 * Render the Pack Options meta box.
	 *
	 * @param \WP_Post $post Current post object.
	 *
	 * @return void
	 */
	public function renderMetaBox(\WP_Post $post): void
	{
		$options = \get_post_meta($post->ID, self::META_KEY, true);
		$options = $options ? \json_decode($options, true) : [];

		\wp_nonce_field('dropdown_pack_options_save', 'dropdown_pack_options_nonce');
		?>
		<div id="pack-options-wrapper">
			<p class="description">
				<?php \esc_html_e('Add pack/bundle sizes for this product. Leave empty to hide the dropdown on the frontend.', 'delta9-digital-blocks-plugin'); ?>
			</p>

			<table class="widefat" id="pack-options-table" style="margin-top: 10px;">
				<thead>
					<tr>
						<th><?php \esc_html_e('Label', 'delta9-digital-blocks-plugin'); ?></th>
						<th><?php \esc_html_e('Quantity', 'delta9-digital-blocks-plugin'); ?></th>
						<th><?php \esc_html_e('Price ($)', 'delta9-digital-blocks-plugin'); ?></th>
						<th style="width: 50px;"></th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($options)) : ?>
						<?php foreach ($options as $index => $option) : ?>
							<tr class="pack-option-row">
								<td>
									<input
										type="text"
										name="pack_options[<?php echo \esc_attr((string) $index); ?>][label]"
										value="<?php echo \esc_attr($option['label'] ?? ''); ?>"
										placeholder="<?php \esc_attr_e('e.g. 4 Pack', 'delta9-digital-blocks-plugin'); ?>"
										class="widefat"
									/>
								</td>
								<td>
									<input
										type="number"
										name="pack_options[<?php echo \esc_attr((string) $index); ?>][quantity]"
										value="<?php echo \esc_attr((string) ($option['quantity'] ?? '')); ?>"
										placeholder="4"
										min="1"
										step="1"
										class="widefat"
									/>
								</td>
								<td>
									<input
										type="number"
										name="pack_options[<?php echo \esc_attr((string) $index); ?>][price]"
										value="<?php echo \esc_attr((string) ($option['price'] ?? '')); ?>"
										placeholder="0.00"
										min="0"
										step="0.01"
										class="widefat"
									/>
								</td>
								<td>
									<button type="button" class="button pack-option-remove" title="<?php \esc_attr_e('Remove', 'delta9-digital-blocks-plugin'); ?>">&times;</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<p>
				<button type="button" class="button button-secondary" id="pack-option-add">
					<?php \esc_html_e('+ Add Pack Option', 'delta9-digital-blocks-plugin'); ?>
				</button>
			</p>
		</div>

		<script>
			(function () {
				const table = document.getElementById('pack-options-table');
				const tbody = table.querySelector('tbody');
				const addBtn = document.getElementById('pack-option-add');

				function getNextIndex() {
					const rows = tbody.querySelectorAll('.pack-option-row');
					return rows.length;
				}

				function createRow(index) {
					const tr = document.createElement('tr');
					tr.className = 'pack-option-row';
					tr.innerHTML = `
						<td><input type="text" name="pack_options[${index}][label]" placeholder="e.g. 4 Pack" class="widefat" /></td>
						<td><input type="number" name="pack_options[${index}][quantity]" placeholder="4" min="1" step="1" class="widefat" /></td>
						<td><input type="number" name="pack_options[${index}][price]" placeholder="0.00" min="0" step="0.01" class="widefat" /></td>
						<td><button type="button" class="button pack-option-remove" title="Remove">&times;</button></td>
					`;
					return tr;
				}

				addBtn.addEventListener('click', function () {
					tbody.appendChild(createRow(getNextIndex()));
				});

				tbody.addEventListener('click', function (e) {
					if (e.target.classList.contains('pack-option-remove')) {
						e.target.closest('.pack-option-row').remove();
						// Re-index remaining rows.
						tbody.querySelectorAll('.pack-option-row').forEach(function (row, i) {
							row.querySelectorAll('input').forEach(function (input) {
								input.name = input.name.replace(/pack_options\[\d+\]/, 'pack_options[' + i + ']');
							});
						});
					}
				});
			})();
		</script>
		<?php
	}

	/**
	 * Save pack options when product is saved.
	 *
	 * @param int $postId Product post ID.
	 *
	 * @return void
	 */
	public function savePackOptions(int $postId): void
	{
		if (
			!isset($_POST['dropdown_pack_options_nonce']) ||
			!\wp_verify_nonce(\sanitize_text_field(\wp_unslash($_POST['dropdown_pack_options_nonce'])), 'dropdown_pack_options_save')
		) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$rawOptions = isset($_POST['pack_options']) ? \wp_unslash($_POST['pack_options']) : [];

		$cleanOptions = [];

		if (\is_array($rawOptions)) {
			foreach ($rawOptions as $option) {
				$label = \sanitize_text_field($option['label'] ?? '');
				$quantity = \absint($option['quantity'] ?? 0);
				$price = \floatval($option['price'] ?? 0);

				if (empty($label) || $quantity < 1 || $price <= 0) {
					continue;
				}

				$cleanOptions[] = [
					'label' => $label,
					'quantity' => $quantity,
					'price' => \round($price, 2),
				];
			}
		}

		if (!empty($cleanOptions)) {
			\update_post_meta($postId, self::META_KEY, \wp_json_encode($cleanOptions));
		} else {
			\delete_post_meta($postId, self::META_KEY);
		}
	}
}
