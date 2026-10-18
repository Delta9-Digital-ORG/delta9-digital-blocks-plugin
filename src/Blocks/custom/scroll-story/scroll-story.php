<?php

/**
 * Template for the Scroll story block.
 *
 * Inner blocks: one Scroll story panel and any number of Scroll story steps. assets/scroll-story.js
 * marks the active step (the last one whose top has passed the trigger line, a fraction of the
 * viewport height) and the progress through it (0 to 1), as:
 * - data-active-step on the root and data-state="past|active|future" on each step;
 * - --scroll-story-step and --scroll-story-progress on the root;
 * - a bubbling `scroll-story:change` event with { step, progress, count }.
 * Without JavaScript the steps read as a normal column beside the (still sticky) panel.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$panelSide = Helpers::checkAttr('scrollStoryPanelSide', $attributes, $manifest) === 'end' ? 'end' : 'start';
$trigger = (float) Helpers::checkAttr('scrollStoryTrigger', $attributes, $manifest);
$trigger = $trigger > 0 && $trigger < 1 ? $trigger : 0.65;

$scrollStoryClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	Helpers::selector($blockClass, $blockClass, '', "panel-{$panelSide}"),
	$attributes['className'] ?? '',
]);
?>

<div
	class="<?php echo esc_attr($scrollStoryClass); ?>"
	data-trigger="<?php echo esc_attr((string) $trigger); ?>"
	data-active-step="0"
	style="--scroll-story-step: 0; --scroll-story-progress: 0;"
>
	<?php
	// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	echo $renderContent;
	?>
</div>
