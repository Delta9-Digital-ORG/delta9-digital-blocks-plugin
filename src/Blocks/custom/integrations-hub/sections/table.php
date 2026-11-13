<?php

/**
 * Integrations hub section: "What each connection does". Platforms with d9_powers, live first
 * (then beta, then on request), in hub order within each status. The layer pills filter the
 * rows (assets/table.js); without JS every row shows and the pills are hidden.
 *
 * @package Delta9DigitalBlocksPlugin
 */

$statuses = [
	'live' => ['label' => __('Live', 'delta9-digital-blocks-plugin'), 'accent' => 'mint'],
	'beta' => ['label' => __('Beta', 'delta9-digital-blocks-plugin'), 'accent' => 'yellow'],
	'request' => ['label' => __('On request', 'delta9-digital-blocks-plugin'), 'accent' => 'coral'],
];
$statusOrder = \array_flip(\array_keys($statuses));

$rows = \array_filter($platforms, static fn($platform) => \trim($platform['powers']) !== '');
if (!$rows) {
	return;
}
// Stable: $platforms is already in hub order.
$rows = \array_values($rows);
\usort($rows, static fn($a, $b) => ($statusOrder[$a['status']] ?? 0) <=> ($statusOrder[$b['status']] ?? 0));

// Filter pills: the hub layers that have rows, in hub-layer order.
$filters = [];
foreach ($hubLayers as $hub) {
	foreach ($rows as $row) {
		if ($row['hub']['key'] === $hub['key']) {
			$filters[$hub['key']] = $hub['label'];
			break;
		}
	}
}
?>
<div class="d9-int-head d9-hub-table__head">
	<h2 class="d9-int-head__title d9-int-head__title--small"><?php echo esc_html($customTitle ?: __('What each connection does', 'delta9-digital-blocks-plugin')); ?></h2>
	<?php if (\count($filters) > 1) { ?>
		<div class="d9-hub-filters" role="group" aria-label="<?php esc_attr_e('Filter by layer', 'delta9-digital-blocks-plugin'); ?>" hidden>
			<button type="button" class="d9-hub-filter" data-filter="" aria-pressed="true"><?php esc_html_e('All', 'delta9-digital-blocks-plugin'); ?></button>
			<?php foreach ($filters as $key => $label) { ?>
				<button type="button" class="d9-hub-filter" data-filter="<?php echo esc_attr($key); ?>" aria-pressed="false"><?php echo esc_html($label); ?></button>
			<?php } ?>
		</div>
	<?php } ?>
</div>
<div class="d9-hub-table" role="table" aria-label="<?php echo esc_attr($customTitle ?: __('What each connection does', 'delta9-digital-blocks-plugin')); ?>">
	<div class="d9-hub-table__row d9-hub-table__row--head" role="row">
		<span role="columnheader"><?php esc_html_e('Platform', 'delta9-digital-blocks-plugin'); ?></span>
		<span role="columnheader"><?php esc_html_e('Layer', 'delta9-digital-blocks-plugin'); ?></span>
		<span role="columnheader"><?php esc_html_e('What it powers', 'delta9-digital-blocks-plugin'); ?></span>
		<span role="columnheader"><?php esc_html_e('Status', 'delta9-digital-blocks-plugin'); ?></span>
	</div>
	<?php foreach ($rows as $row) { ?>
		<?php $status = $statuses[$row['status']] ?? $statuses['live']; ?>
		<div class="d9-hub-table__row" role="row" data-layer="<?php echo esc_attr($row['hub']['key']); ?>">
			<span class="d9-hub-table__platform" role="cell"><a href="<?php echo esc_url($row['url']); ?>"><?php $logo($row); ?></a></span>
			<span class="d9-hub-table__layer" role="cell"><?php echo esc_html($row['hub']['label']); ?></span>
			<span class="d9-hub-table__powers" role="cell"><?php echo esc_html($row['powers']); ?></span>
			<span role="cell"><span class="d9-hub-status" style="<?php echo esc_attr($accentVar($status['accent'])); ?>"><?php echo esc_html($status['label']); ?></span></span>
		</div>
	<?php } ?>
</div>
