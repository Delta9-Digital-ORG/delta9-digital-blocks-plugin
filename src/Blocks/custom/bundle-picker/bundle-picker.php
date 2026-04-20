<?php

/**
 * Template for the Bundle Picker Block.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$unique = Helpers::getUnique();

$blockClass = $attributes['blockClass'] ?? '';

$bundlePickerCategory = Helpers::checkAttr('bundlePickerCategory', $attributes, $manifest);
$bundlePickerProductIds = Helpers::checkAttr('bundlePickerProductIds', $attributes, $manifest);
$bundlePickerTiers = Helpers::checkAttr('bundlePickerTiers', $attributes, $manifest);
$bundlePickerHeading = Helpers::checkAttr('bundlePickerHeading', $attributes, $manifest);
$bundlePickerSubheading = Helpers::checkAttr('bundlePickerSubheading', $attributes, $manifest);
$bundlePickerColumns = Helpers::checkAttr('bundlePickerColumns', $attributes, $manifest);
$bundlePickerShowProgress = Helpers::checkAttr('bundlePickerShowProgress', $attributes, $manifest);
$bundlePickerServerSideRender = Helpers::checkAttr('bundlePickerServerSideRender', $attributes, $manifest);

// Build product query args.
$queryArgs = [
	'status' => 'publish',
	'limit' => -1,
	'orderby' => 'title',
	'order' => 'ASC',
];

// Filter by specific product IDs if set.
if (!empty($bundlePickerProductIds)) {
	$queryArgs['include'] = array_map(function ($item) {
		return $item['value'] ?? $item;
	}, (array) $bundlePickerProductIds);
} elseif (!empty($bundlePickerCategory)) {
	// Filter by category.
	$categoryId = $bundlePickerCategory['value'] ?? $bundlePickerCategory;
	$queryArgs['category'] = [get_term($categoryId)->slug ?? ''];
}

$products = wc_get_products($queryArgs);

if (empty($products)) {
	if ($bundlePickerServerSideRender) {
		echo '<div class="' . esc_attr($blockClass) . ' bundle-picker-empty">';
		echo '<p>' . esc_html__('No products found. Select a category or specific products.', 'delta9-digital-blocks-plugin') . '</p>';
		echo '</div>';
	}
	return;
}

// Prepare REST URL for frontend JS.
$restUrl = rest_url('delta9-digital-blocks-plugin/v1/bundle/add');
$restNonce = wp_create_nonce('wp_rest');

// Encode tiers for data attribute.
$tiersJson = wp_json_encode($bundlePickerTiers);

// Build product data for frontend JS.
$productsData = [];
foreach ($products as $product) {
	$productsData[] = [
		'id' => $product->get_id(),
		'name' => $product->get_name(),
		'price' => (float) $product->get_price(),
		'image' => wp_get_attachment_image_url($product->get_image_id(), 'medium'),
	];
}
$productsJson = wp_json_encode($productsData);

?>

<div
	class="<?php echo esc_attr($blockClass); ?> bundle-picker"
	data-id="<?php echo esc_attr($unique); ?>"
	data-rest-url="<?php echo esc_url($restUrl); ?>"
	data-rest-nonce="<?php echo esc_attr($restNonce); ?>"
	data-tiers="<?php echo esc_attr($tiersJson); ?>"
	data-products="<?php echo esc_attr($productsJson); ?>"
	data-columns="<?php echo esc_attr($bundlePickerColumns); ?>"
>
	<?php if ($bundlePickerHeading || $bundlePickerSubheading) : ?>
		<div class="bundle-picker__header">
			<?php if ($bundlePickerHeading) : ?>
				<h2 class="bundle-picker__heading"><?php echo esc_html($bundlePickerHeading); ?></h2>
			<?php endif; ?>
			<?php if ($bundlePickerSubheading) : ?>
				<p class="bundle-picker__subheading"><?php echo esc_html($bundlePickerSubheading); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ($bundlePickerTiers && !empty($bundlePickerTiers)) : ?>
		<div class="bundle-picker__tiers">
			<?php foreach ($bundlePickerTiers as $tier) : ?>
				<div class="bundle-picker__tier" data-min="<?php echo esc_attr($tier['min']); ?>" data-pct="<?php echo esc_attr($tier['pct']); ?>">
					<span class="bundle-picker__tier-label"><?php echo esc_html($tier['label']); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ($bundlePickerShowProgress) : ?>
		<div class="bundle-picker__progress">
			<div class="bundle-picker__progress-bar">
				<div class="bundle-picker__progress-fill" style="width: 0%;"></div>
			</div>
			<p class="bundle-picker__progress-text"></p>
		</div>
	<?php endif; ?>

	<div class="bundle-picker__grid" style="--bundle-picker-columns: <?php echo esc_attr($bundlePickerColumns); ?>;">
		<?php foreach ($products as $product) :
			$productId = $product->get_id();
			$productPrice = (float) $product->get_price();
			$productImage = wp_get_attachment_image_url($product->get_image_id(), 'medium');
		?>
			<div class="bundle-picker__product" data-product-id="<?php echo esc_attr($productId); ?>" data-price="<?php echo esc_attr($productPrice); ?>">
				<?php if ($productImage) : ?>
					<div class="bundle-picker__product-image">
						<img src="<?php echo esc_url($productImage); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" loading="lazy" />
					</div>
				<?php endif; ?>

				<div class="bundle-picker__product-info">
					<h3 class="bundle-picker__product-name"><?php echo esc_html($product->get_name()); ?></h3>
					<div class="bundle-picker__product-price">
						<span class="bundle-picker__price-current"><?php echo wp_kses_post(wc_price($productPrice)); ?></span>
						<span class="bundle-picker__price-discounted" style="display: none;"></span>
					</div>
				</div>

				<div class="bundle-picker__product-qty">
					<button class="bundle-picker__qty-btn bundle-picker__qty-minus" type="button" aria-label="<?php esc_attr_e('Decrease quantity', 'delta9-digital-blocks-plugin'); ?>">-</button>
					<input
						class="bundle-picker__qty-input"
						type="number"
						value="0"
						min="0"
						max="99"
						step="1"
						aria-label="<?php echo esc_attr(sprintf(__('Quantity for %s', 'delta9-digital-blocks-plugin'), $product->get_name())); ?>"
					/>
					<button class="bundle-picker__qty-btn bundle-picker__qty-plus" type="button" aria-label="<?php esc_attr_e('Increase quantity', 'delta9-digital-blocks-plugin'); ?>">+</button>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="bundle-picker__summary">
		<div class="bundle-picker__summary-details">
			<div class="bundle-picker__summary-row">
				<span class="bundle-picker__summary-label"><?php esc_html_e('Items:', 'delta9-digital-blocks-plugin'); ?></span>
				<span class="bundle-picker__summary-count">0</span>
			</div>
			<div class="bundle-picker__summary-row">
				<span class="bundle-picker__summary-label"><?php esc_html_e('Subtotal:', 'delta9-digital-blocks-plugin'); ?></span>
				<span class="bundle-picker__summary-subtotal">$0.00</span>
			</div>
			<div class="bundle-picker__summary-row bundle-picker__summary-row--discount" style="display: none;">
				<span class="bundle-picker__summary-label"><?php esc_html_e('Discount:', 'delta9-digital-blocks-plugin'); ?></span>
				<span class="bundle-picker__summary-discount">-$0.00</span>
			</div>
			<div class="bundle-picker__summary-row bundle-picker__summary-row--total">
				<span class="bundle-picker__summary-label"><?php esc_html_e('Total:', 'delta9-digital-blocks-plugin'); ?></span>
				<span class="bundle-picker__summary-total">$0.00</span>
			</div>
		</div>

		<button class="bundle-picker__add-to-cart" type="button" disabled>
			<?php esc_html_e('Add Bundle to Cart', 'delta9-digital-blocks-plugin'); ?>
		</button>

		<div class="bundle-picker__message" style="display: none;"></div>
	</div>
</div>
