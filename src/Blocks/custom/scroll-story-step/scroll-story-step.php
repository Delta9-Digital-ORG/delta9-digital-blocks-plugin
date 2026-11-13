<?php

/**
 * Template for the Scroll story step block.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$stepClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	$attributes['className'] ?? '',
]);
?>

<div class="<?php echo esc_attr($stepClass); ?>">
	<?php
	// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	echo $renderContent;
	?>
</div>
