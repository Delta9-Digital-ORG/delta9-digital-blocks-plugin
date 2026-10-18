<?php

/**
 * Template for the Tab Block (one panel of a Tabs block).
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$tabLabel = Helpers::checkAttr('tabLabel', $attributes, $manifest);
$tabIconUrl = Helpers::checkAttr('tabIconUrl', $attributes, $manifest);

$tabClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
]);
?>

<div
	class="<?php echo esc_attr($tabClass); ?>"
	data-tab-label="<?php echo esc_attr($tabLabel); ?>"
	<?php if ($tabIconUrl) { ?>
		data-tab-icon="<?php echo esc_url($tabIconUrl); ?>"
	<?php } ?>
>
	<?php if ($tabLabel) { ?>
		<p class="<?php echo esc_attr(Helpers::selector($blockClass, $blockClass, 'fallback-label')); ?>"><?php echo esc_html($tabLabel); ?></p>
	<?php } ?>

	<?php
	// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	echo $renderContent;
	?>
</div>
