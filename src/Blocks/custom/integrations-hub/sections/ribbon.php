<?php

/**
 * Integrations hub section: logo ribbon (the hero's "cascade" tiles). Four square tiles step
 * down to the right, joined by fillets; each cycles through the platforms in turn
 * (assets/ribbon.js), staggered one tile per tick. Without JS the first four stay put.
 *
 * @package Delta9DigitalBlocksPlugin
 */

$items = $pick([]);
if (!$items) {
	$items = \array_values(\array_filter($platforms, static fn($platform) => $platform['logoId'] > 0));
}
if (!$items) {
	return;
}

$item = static function (array $platform) use ($logo, $accentVar): void {
	?>
	<span class="d9-hub-ribbon__item" style="<?php echo esc_attr($accentVar($platform['hub']['accent'])); ?>">
		<?php $logo($platform, 'd9-hub-ribbon__logo'); ?>
		<span class="d9-hub-ribbon__label"><?php echo esc_html($platform['layerLabel']); ?></span>
	</span>
	<?php
};
?>
<div class="d9-hub-ribbon" aria-hidden="true">
	<?php for ($slot = 0; $slot < 4; $slot++) { ?>
		<div class="d9-hub-ribbon__tile" data-slot="<?php echo esc_attr((string) $slot); ?>">
			<?php $item($items[$slot % \count($items)]); ?>
		</div>
	<?php } ?>
</div>
<?php if (\count($items) > 4) { ?>
	<div class="d9-hub-ribbon__pool" hidden>
		<?php foreach ($items as $platform) { ?>
			<?php $item($platform); ?>
		<?php } ?>
	</div>
<?php } ?>
