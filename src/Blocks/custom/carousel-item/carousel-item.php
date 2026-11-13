<?php

/**
 * Template for the Carousel item Block view.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$unique = Helpers::getUnique();

$carouselItemUseContent = Helpers::checkAttr('carouselItemUseContent', $attributes, $manifest);

$carouselItemClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	$carouselItemUseContent ? Helpers::selector($blockClass, $blockClass, '', 'content') : '',
]);
?>

<div class="<?php echo esc_attr($carouselItemClass); ?>" data-id="<?php echo esc_attr($unique); ?>">
	<?php
	echo Helpers::outputCssVariables($attributes, $manifest, $unique); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped

	if ($carouselItemUseContent) {
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		echo $renderContent;
	} else {
		echo Helpers::render('image', Helpers::props('image', $attributes)); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	}
	?>
</div>
