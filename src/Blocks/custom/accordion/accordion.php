<?php

/**
 * Template for the Accordion Block view.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$accordionCloseAdjacent = Components::checkAttr('accordionCloseAdjacent', $attributes, $manifest);

$accordionClass = Components::classnames([
	$blockClass,
	$blockJsClass,
]);
?>

<div
	class="<?php echo esc_attr($accordionClass); ?>"
	data-close-adjacent="<?php echo esc_attr($accordionCloseAdjacent ? 'true' : 'false'); ?>"
>
		<?php
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		echo $innerBlockContent;
		?>
</div>
