<?php

/**
 * Product Top Benefits meta box for WooCommerce products.
 *
 * Adds a repeater-style meta box that lets an editor manually curate the
 * "top benefits" shown on a product. When the Product Ingredients block's
 * "Ingredient Text" is set to "Top Product Benefits", the block renders
 * these values instead of deriving them from product category taxonomy
 * descriptions.
 *
 * @package Delta9DigitalBlocksPlugin\TopBenefits
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\TopBenefits;

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class TopBenefits
 */
class TopBenefits implements ServiceInterface
{
	/**
	 * Meta key for storing the curated top benefits (JSON-encoded array of strings).
	 */
	public const META_KEY = '_product_top_benefits';

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('add_meta_boxes', [$this, 'addMetaBox']);
		\add_action('woocommerce_process_product_meta', [$this, 'saveTopBenefits']);
	}

	/**
	 * Add the Product Top Benefits meta box to the product edit screen.
	 *
	 * @return void
	 */
	public function addMetaBox(): void
	{
		\add_meta_box(
			'product_top_benefits',
			\__('Product Top Benefits', 'delta9-digital-blocks-plugin'),
			[$this, 'renderMetaBox'],
			'product',
			'normal',
			'default'
		);
	}

	/**
	 * Render the Product Top Benefits meta box.
	 *
	 * @param \WP_Post $post Current post object.
	 *
	 * @return void
	 */
	public function renderMetaBox(\WP_Post $post): void
	{
		$benefits = \get_post_meta($post->ID, self::META_KEY, true);
		$benefits = $benefits ? \json_decode($benefits, true) : [];
		$benefits = \is_array($benefits) ? $benefits : [];

		\wp_nonce_field('product_top_benefits_save', 'product_top_benefits_nonce');
		?>
		<div id="top-benefits-wrapper">
			<p class="description">
				<?php \esc_html_e('Manually curate the "top benefits" for this product, one per row. These override the auto-generated benefits when the Product Ingredients block\'s "Ingredient Text" is set to "Top Product Benefits". Leave empty to fall back to the automatic behaviour.', 'delta9-digital-blocks-plugin'); ?>
			</p>

			<table class="widefat" id="top-benefits-table" style="margin-top: 10px;">
				<thead>
					<tr>
						<th><?php \esc_html_e('Benefit', 'delta9-digital-blocks-plugin'); ?></th>
						<th style="width: 50px;"></th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($benefits)) : ?>
						<?php foreach ($benefits as $index => $benefit) : ?>
							<tr class="top-benefit-row">
								<td>
									<input
										type="text"
										name="product_top_benefits[<?php echo \esc_attr((string) $index); ?>]"
										value="<?php echo \esc_attr((string) $benefit); ?>"
										placeholder="<?php \esc_attr_e('e.g. Body Wellness', 'delta9-digital-blocks-plugin'); ?>"
										class="widefat"
									/>
								</td>
								<td>
									<button type="button" class="button top-benefit-remove" title="<?php \esc_attr_e('Remove', 'delta9-digital-blocks-plugin'); ?>">&times;</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<p>
				<button type="button" class="button button-secondary" id="top-benefit-add">
					<?php \esc_html_e('+ Add Benefit', 'delta9-digital-blocks-plugin'); ?>
				</button>
			</p>
		</div>

		<script>
			(function () {
				const table = document.getElementById('top-benefits-table');
				const tbody = table.querySelector('tbody');
				const addBtn = document.getElementById('top-benefit-add');

				function getNextIndex() {
					return tbody.querySelectorAll('.top-benefit-row').length;
				}

				function createRow(index) {
					const tr = document.createElement('tr');
					tr.className = 'top-benefit-row';
					tr.innerHTML = `
						<td><input type="text" name="product_top_benefits[${index}]" placeholder="e.g. Body Wellness" class="widefat" /></td>
						<td><button type="button" class="button top-benefit-remove" title="Remove">&times;</button></td>
					`;
					return tr;
				}

				addBtn.addEventListener('click', function () {
					tbody.appendChild(createRow(getNextIndex()));
				});

				tbody.addEventListener('click', function (e) {
					if (e.target.classList.contains('top-benefit-remove')) {
						e.target.closest('.top-benefit-row').remove();
						// Re-index remaining rows.
						tbody.querySelectorAll('.top-benefit-row').forEach(function (row, i) {
							row.querySelectorAll('input').forEach(function (input) {
								input.name = input.name.replace(/product_top_benefits\[\d+\]/, 'product_top_benefits[' + i + ']');
							});
						});
					}
				});
			})();
		</script>
		<?php
	}

	/**
	 * Save the top benefits when the product is saved.
	 *
	 * @param int $postId Product post ID.
	 *
	 * @return void
	 */
	public function saveTopBenefits(int $postId): void
	{
		if (
			!isset($_POST['product_top_benefits_nonce']) ||
			!\wp_verify_nonce(\sanitize_text_field(\wp_unslash($_POST['product_top_benefits_nonce'])), 'product_top_benefits_save')
		) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$rawBenefits = isset($_POST['product_top_benefits']) ? \wp_unslash($_POST['product_top_benefits']) : [];

		$cleanBenefits = [];

		if (\is_array($rawBenefits)) {
			foreach ($rawBenefits as $benefit) {
				$benefit = \sanitize_text_field($benefit);

				if ($benefit === '') {
					continue;
				}

				$cleanBenefits[] = $benefit;
			}
		}

		if (!empty($cleanBenefits)) {
			\update_post_meta($postId, self::META_KEY, \wp_json_encode($cleanBenefits));
		} else {
			\delete_post_meta($postId, self::META_KEY);
		}
	}
}
