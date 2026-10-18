<?php

/**
 * Template for the Filter chips block.
 *
 * Server-rendered from the taxonomy's non-empty terms. Each chip carries the class that
 * post_class() gives matching posts ("category-{slug}", "tag-{slug}", "{taxonomy}-{slug}").
 * Rendered hidden: the Filter grid script shows it once it works.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$filterChipsGroup = Helpers::checkAttr('filterChipsGroup', $attributes, $manifest);
$filterChipsTaxonomy = Helpers::checkAttr('filterChipsTaxonomy', $attributes, $manifest);
$filterChipsOrderBy = Helpers::checkAttr('filterChipsOrderBy', $attributes, $manifest);
$filterChipsAllLabel = Helpers::checkAttr('filterChipsAllLabel', $attributes, $manifest);
$filterChipsLabel = Helpers::checkAttr('filterChipsLabel', $attributes, $manifest);
$filterChipsUrlParam = Helpers::checkAttr('filterChipsUrlParam', $attributes, $manifest);

if (!taxonomy_exists($filterChipsTaxonomy)) {
	return;
}

$terms = get_terms([
	'taxonomy' => $filterChipsTaxonomy,
	'hide_empty' => true,
	'orderby' => in_array($filterChipsOrderBy, ['name', 'term_id', 'count', 'slug'], true) ? $filterChipsOrderBy : 'name',
	'order' => $filterChipsOrderBy === 'count' ? 'DESC' : 'ASC',
]);

if (is_wp_error($terms) || !$terms) {
	return;
}

// Same prefixes as get_post_class().
$classPrefix = match ($filterChipsTaxonomy) {
	'category' => 'category',
	'post_tag' => 'tag',
	default => sanitize_html_class($filterChipsTaxonomy),
};

// "Additional CSS class(es)" from the editor (e.g. alignwide, theme hooks).
$chipsClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	$attributes['className'] ?? '',
]);

$chipClass = Helpers::selector($blockClass, $blockClass, 'chip');
$labelId = wp_unique_id('filter-chips-label-');
?>

<div
	class="<?php echo esc_attr($chipsClass); ?>"
	role="group"
	<?php if ($filterChipsLabel) { ?>
		aria-labelledby="<?php echo esc_attr($labelId); ?>"
	<?php } else { ?>
		aria-label="<?php echo esc_attr(get_taxonomy($filterChipsTaxonomy)->labels->name ?? ''); ?>"
	<?php } ?>
	data-filter-group="<?php echo esc_attr($filterChipsGroup); ?>"
	<?php if ($filterChipsUrlParam) { ?>
		data-url-param="<?php echo esc_attr($filterChipsUrlParam); ?>"
	<?php } ?>
	hidden
>
	<?php if ($filterChipsLabel) { ?>
		<span id="<?php echo esc_attr($labelId); ?>" class="<?php echo esc_attr(Helpers::selector($blockClass, $blockClass, 'label')); ?>">
			<?php echo esc_html($filterChipsLabel); ?>
		</span>
	<?php } ?>

	<button type="button" class="<?php echo esc_attr($chipClass); ?>" data-filter-value="" aria-pressed="true">
		<?php echo esc_html($filterChipsAllLabel); ?>
	</button>

	<?php foreach ($terms as $term) { ?>
		<button
			type="button"
			class="<?php echo esc_attr($chipClass); ?>"
			data-filter-value="<?php echo esc_attr("{$classPrefix}-" . sanitize_html_class($term->slug, (string) $term->term_id)); ?>"
			data-filter-slug="<?php echo esc_attr($term->slug); ?>"
			aria-pressed="false"
		>
			<?php echo esc_html($term->name); ?>
		</button>
	<?php } ?>
</div>
