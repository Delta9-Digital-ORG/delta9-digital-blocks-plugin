<?php

/**
 * Template for the Dropdown Picker Block.
 *
 * Renders a <select> dropdown with per-product pack options.
 * Hidden when the product has no pack options configured.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;
use Delta9DigitalBlocksPlugin\PackOptions\PackOptions;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$unique = Helpers::getUnique();

// Get the current product in the loop.
$productId = get_the_ID();

if (!$productId) {
	return;
}

$product = wc_get_product($productId);

if (!$product) {
	return;
}

// Read pack options from product meta.
$rawOptions = get_post_meta($productId, PackOptions::META_KEY, true);
$packOptions = $rawOptions ? json_decode($rawOptions, true) : [];

if (empty($packOptions)) {
	return;
}

$regularPrice = (float) $product->get_price();

?>

<div
	class="<?php echo esc_attr($blockClass); ?> dropdown-picker"
	data-id="<?php echo esc_attr($unique); ?>"
	data-product-id="<?php echo esc_attr((string) $productId); ?>"
>
	<?php echo Helpers::outputCssVariables($attributes, $manifest, $unique); ?>

	<select
		class="dropdown-picker__select"
		aria-label="<?php echo esc_attr(sprintf(__('Pack size for %s', 'delta9-digital-blocks-plugin'), $product->get_name())); ?>"
		data-product-id="<?php echo esc_attr((string) $productId); ?>"
		data-regular-price="<?php echo esc_attr((string) $regularPrice); ?>"
	>
		<?php foreach ($packOptions as $option) :
			$label = $option['label'] ?? '';
			$quantity = (int) ($option['quantity'] ?? 1);
			$price = (float) ($option['price'] ?? 0);
		?>
			<option
				value="<?php echo esc_attr((string) $price); ?>"
				data-quantity="<?php echo esc_attr((string) $quantity); ?>"
				data-price="<?php echo esc_attr((string) $price); ?>"
				data-label="<?php echo esc_attr($label); ?>"
			>
				<?php echo esc_html(sprintf('%s — %s', $label, strip_tags(wc_price($price)))); ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>
