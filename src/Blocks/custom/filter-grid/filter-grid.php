<?php

/**
 * Template for the Filter grid block.
 *
 * Renders its inner blocks unchanged plus an empty-state message and a polite live region.
 * assets/filter.js hides items (filterGridItemSelector) that don't match the Filter chips /
 * Filter search blocks of the same group. Filtering is client-side: the inner list must
 * contain every item (e.g. a Query Loop with "items per page" >= the number of posts).
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$filterGridGroup = Helpers::checkAttr('filterGridGroup', $attributes, $manifest);
$filterGridItemSelector = Helpers::checkAttr('filterGridItemSelector', $attributes, $manifest);
$filterGridEmptyMessage = Helpers::checkAttr('filterGridEmptyMessage', $attributes, $manifest);
$filterGridCountTemplate = Helpers::checkAttr('filterGridCountTemplate', $attributes, $manifest);

$filterGridClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
]);
?>

<div
	class="<?php echo esc_attr($filterGridClass); ?>"
	data-filter-group="<?php echo esc_attr($filterGridGroup); ?>"
	data-item-selector="<?php echo esc_attr($filterGridItemSelector); ?>"
	data-count-template="<?php echo esc_attr($filterGridCountTemplate); ?>"
>
	<?php
	// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	echo $renderContent;
	?>

	<p class="<?php echo esc_attr(Helpers::selector($blockClass, $blockClass, 'empty')); ?>" hidden>
		<?php echo esc_html($filterGridEmptyMessage); ?>
	</p>

	<p class="<?php echo esc_attr(Helpers::selector($blockClass, $blockClass, 'status')); ?> screen-reader-text" role="status" aria-live="polite"></p>
</div>
