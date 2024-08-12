<?php

/**
 * Template for the Button Component.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$productCountButtonUse = Helpers::checkAttr('productCountButtonUse', $attributes, $manifest);
if (!$productCountButtonUse) {
	return;
}

$unique = Helpers::getUnique();

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$productCountButtonUrl = Helpers::checkAttr('productCountButtonUrl', $attributes, $manifest);
$productCountButtonContent = Helpers::checkAttr('productCountButtonContent', $attributes, $manifest);
$productCountButtonIsAnchor = Helpers::checkAttr('productCountButtonIsAnchor', $attributes, $manifest);
$productCountButtonId = Helpers::checkAttr('productCountButtonId', $attributes, $manifest);
$productCountButtonIsNewTab = Helpers::checkAttr('productCountButtonIsNewTab', $attributes, $manifest);
$productCountButtonAriaLabel = Helpers::checkAttr('productCountButtonAriaLabel', $attributes, $manifest);
$productCountButtonAttrs = (array)Helpers::checkAttr('productCountButtonAttrs', $attributes, $manifest);

$productCountButtonAttrs['title'] = $productCountButtonContent;
$productCountButtonAttrs['data-id'] = $unique;

if (!empty($productCountButtonUrl)) {
	$productCountButtonAttrs['href'] = $productCountButtonUrl;
}

if ($productCountButtonIsNewTab) {
	$productCountButtonAttrs['target'] = '_blank';
	$productCountButtonAttrs['rel'] = '"noopener noreferrer"';
}

if (!empty($productCountButtonId)) {
	$productCountButtonAttrs['id'] = $productCountButtonId;
}

if (!empty($productCountButtonAriaLabel)) {
	$productCountButtonAttrs['aria-label'] = $productCountButtonAriaLabel;
}

$productCountButtonClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($blockClass, $blockClass, $selectorClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($productCountButtonIsAnchor, 'js-scroll-to-anchor'),
]);

$productCountButtonTag = $productCountButtonUrl ? 'a' : 'button';

if(have_posts()) {
	$productPost = get_post(get_the_ID());
	
	?>
	
	<?php
		echo Helpers::outputCssVariables($attributes, $manifest, $unique);
	?>
	
	<?php // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
	<<?php echo $productCountButtonTag; ?>
		class="<?php echo esc_attr($productCountButtonClass); ?> wp-block-button__link wp-element-button wc-block-components-product-button__button"
		<?php
		foreach ($productCountButtonAttrs as $key => $value) {
			if (!empty($key) && !empty($value)) {
				echo wp_kses_post("{$key}=\"$value\"");
			}
		}
		?>
		data-product_id="<?php echo $productPost->ID; ?>"
		data-product_quantity="1"
	>
		<?php
		echo Helpers::render('icon', Helpers::props('icon', $attributes, [
			'blockClass' => $componentClass,
		]));
		?>
	
		<?php if (!empty($productCountButtonContent)) { ?>
			<span><?php echo esc_html($productCountButtonContent); ?></span>
		<?php } ?>
	<?php // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
	</<?php echo $productCountButtonTag; ?>>
<?php
}
