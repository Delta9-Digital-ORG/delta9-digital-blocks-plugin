<?php

/**
 * Integrations hub section: logo band. Platform tiles (layer-coloured dot + logo) around the
 * mint "Your site" card, which sits after the second tile.
 *
 * @package Delta9DigitalBlocksPlugin
 */

$items = $pick(['dutchie', 'treez', 'alpineiq', 'ga4', 'jane', 'flowhub', 'klaviyo', 'surfside']);
if (!$items) {
	return;
}

$card = static function () use ($customTitle, $customLead): void {
	?>
	<div class="d9-hub-band__card">
		<span class="d9-hub-band__eyebrow"><?php echo esc_html($customLead ?: __('Your site', 'delta9-digital-blocks-plugin')); ?></span>
		<span class="d9-hub-band__claim"><?php echo esc_html($customTitle ?: __('Built and maintained by Delta9', 'delta9-digital-blocks-plugin')); ?></span>
	</div>
	<?php
};
?>
<div class="d9-hub-band">
	<?php foreach ($items as $index => $platform) { ?>
		<?php
		if ($index === \min(2, \count($items))) {
			$card();
		}
		?>
		<a class="d9-hub-band__tile" href="<?php echo esc_url($platform['url']); ?>" style="<?php echo esc_attr($accentVar($platform['hub']['accent'])); ?>">
			<span class="d9-hub-band__dot" aria-hidden="true"></span>
			<span class="d9-hub-band__logo"><?php $logo($platform); ?></span>
		</a>
	<?php } ?>
	<?php
	if (\count($items) < 2) {
		$card();
	}
	?>
</div>
