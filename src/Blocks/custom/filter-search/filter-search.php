<?php

/**
 * Template for the Filter search block.
 *
 * Rendered hidden: the Filter grid script shows it once it works. The label is visually
 * hidden (the placeholder is not a label).
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$filterSearchGroup = Helpers::checkAttr('filterSearchGroup', $attributes, $manifest);
$filterSearchLabel = Helpers::checkAttr('filterSearchLabel', $attributes, $manifest);
$filterSearchPlaceholder = Helpers::checkAttr('filterSearchPlaceholder', $attributes, $manifest);
$filterSearchUrlParam = Helpers::checkAttr('filterSearchUrlParam', $attributes, $manifest);

// "Additional CSS class(es)" from the editor (e.g. alignwide, theme hooks).
$searchClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	$attributes['className'] ?? '',
]);

$inputId = wp_unique_id('filter-search-');
?>

<form
	class="<?php echo esc_attr($searchClass); ?>"
	role="search"
	data-filter-group="<?php echo esc_attr($filterSearchGroup); ?>"
	<?php if ($filterSearchUrlParam) { ?>
		data-url-param="<?php echo esc_attr($filterSearchUrlParam); ?>"
	<?php } ?>
	hidden
>
	<label class="screen-reader-text" for="<?php echo esc_attr($inputId); ?>"><?php echo esc_html($filterSearchLabel); ?></label>
	<input
		id="<?php echo esc_attr($inputId); ?>"
		class="<?php echo esc_attr(Helpers::selector($blockClass, $blockClass, 'input')); ?>"
		type="search"
		autocomplete="off"
		<?php if ($filterSearchPlaceholder) { ?>
			placeholder="<?php echo esc_attr($filterSearchPlaceholder); ?>"
		<?php } ?>
	/>
	<button type="submit" class="<?php echo esc_attr(Helpers::selector($blockClass, $blockClass, 'submit')); ?>">
		<span class="screen-reader-text"><?php echo esc_html($filterSearchLabel); ?></span>
	</button>
</form>
