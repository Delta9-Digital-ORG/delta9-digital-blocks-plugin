<?php

/**
 * Template for the Group block.
 *
 * @package Delta9DigitalBlocksPlugin
 */
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';
$unique = Components::getUnique();

$carouselItemClass = Components::classnames([
	$blockClass,
	$blockJsClass,
]);

?>

<div class="<?php echo esc_attr($carouselItemClass); ?> block-carousel-item js-block-carousel-item" data-id="<?php echo esc_attr($unique); ?>">
	<?php
	// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	echo $innerBlockContent;
	?>
</div>
