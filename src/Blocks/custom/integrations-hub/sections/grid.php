<?php

/**
 * Integrations hub section: "Platforms we connect". Every platform (or the picked ones) in hub
 * order as linked tiles: accent bar, logo, layer label.
 *
 * @package Delta9DigitalBlocksPlugin
 */

$items = $platformSlugs ? $pick([]) : \array_values($platforms);
if (!$items) {
	return;
}
?>
<div class="d9-int-head">
	<h2 class="d9-int-head__title d9-int-head__title--small"><?php echo esc_html($customTitle ?: __('Platforms we connect', 'delta9-digital-blocks-plugin')); ?></h2>
	<p class="d9-int-head__aside"><?php echo esc_html($customLead ?: __('Running something else? We will look at the API.', 'delta9-digital-blocks-plugin')); ?></p>
</div>
<div class="d9-int-tiles d9-hub-grid">
	<?php foreach ($items as $platform) { ?>
		<a class="d9-int-tile d9-hub-grid__tile" href="<?php echo esc_url($platform['url']); ?>" style="<?php echo esc_attr("--d9-int-tile-accent:var(--wp--preset--color--{$platform['accent']});"); ?>">
			<span class="d9-int-tile__bar" aria-hidden="true"></span>
			<span class="d9-int-tile__logo"><?php $logo($platform); ?></span>
			<span class="d9-int-tile__layer"><?php echo esc_html($platform['layerLabel']); ?></span>
		</a>
	<?php } ?>
</div>
