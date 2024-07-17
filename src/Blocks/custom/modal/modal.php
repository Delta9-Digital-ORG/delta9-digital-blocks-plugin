<?php

/**
 * Template for the Modal Block view.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$modalCloseAdjacent = Helpers::checkAttr('modalCloseAdjacent', $attributes, $manifest);

$modalClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
]);
?>

<div
	class="<?php echo esc_attr($modalClass); ?>"
	data-close-adjacent="<?php echo esc_attr($modalCloseAdjacent ? 'true' : 'false'); ?>"
>
		<?php
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		echo $renderContent;
		?>
</div>
