<?php

/**
 * Template for the Tabs Block.
 *
 * The tab list is built by assets/tabs.js from the panels' data-tab-label / data-tab-icon,
 * so without JavaScript every panel shows, stacked, with its fallback label.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$tabsAriaLabel = Helpers::checkAttr('tabsAriaLabel', $attributes, $manifest);

$tabsClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
]);

$tabsListClass = Helpers::classnames([
	Helpers::selector($blockClass, $blockClass, 'list'),
	"{$blockJsClass}-list",
]);

$tabsPanelsClass = Helpers::selector($blockClass, $blockClass, 'panels');
?>

<div class="<?php echo esc_attr($tabsClass); ?>" data-tabs-ready="false">
	<div
		class="<?php echo esc_attr($tabsListClass); ?>"
		role="tablist"
		<?php if ($tabsAriaLabel) { ?>
			aria-label="<?php echo esc_attr($tabsAriaLabel); ?>"
		<?php } ?>
		hidden
	></div>

	<div class="<?php echo esc_attr($tabsPanelsClass); ?>">
		<?php
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		echo $renderContent;
		?>
	</div>
</div>
