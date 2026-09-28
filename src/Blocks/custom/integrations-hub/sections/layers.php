<?php

/**
 * Integrations hub section: layers (variant C, sticky). The intro and CTA stay pinned on the
 * left while the numbered rows scroll past; chips that match an integration's title link to
 * its page.
 *
 * @package Delta9DigitalBlocksPlugin
 */

$rows = \array_values(\array_filter($layerRows, static fn($row) => \is_array($row) && !empty($row['title'])));
if (!$rows) {
	return;
}

$byName = [];
foreach ($platforms as $platform) {
	$byName[\strtolower($platform['name'])] = $platform['url'];
}
?>
<div class="d9-hub-layers">
	<div class="d9-hub-layers__intro">
		<h2 class="d9-hub-layers__title"><?php echo esc_html($customTitle ?: __('Five layers, one build', 'delta9-digital-blocks-plugin')); ?></h2>
		<p class="d9-hub-layers__lead"><?php echo esc_html($customLead ?: __('Menu, register, loyalty, reporting, and compliance. We wire them in the order that keeps your store selling while we work.', 'delta9-digital-blocks-plugin')); ?></p>
		<?php if ($ctaLabel && $ctaUrl) { ?>
			<a class="d9-int-btn d9-hub-layers__cta" href="<?php echo esc_url($ctaUrl); ?>"><?php echo esc_html($ctaLabel); ?> <span aria-hidden="true">→</span></a>
		<?php } ?>
	</div>
	<ol class="d9-hub-layers__rows">
		<?php foreach ($rows as $index => $row) { ?>
			<?php
			$chips = \array_values(\array_filter(\array_map('trim', \explode(',', (string) ($row['chips'] ?? '')))));
			$rowAccent = \sanitize_key((string) ($row['accent'] ?? '')) ?: 'mint';
			?>
			<li class="d9-hub-layers__row" style="<?php echo esc_attr($accentVar($rowAccent)); ?>">
				<span class="d9-hub-layers__num" aria-hidden="true"><?php echo esc_html(\str_pad((string) ($index + 1), 2, '0', \STR_PAD_LEFT)); ?></span>
				<div class="d9-hub-layers__body">
					<h3 class="d9-hub-layers__name"><?php echo esc_html((string) $row['title']); ?></h3>
					<?php if (!empty($row['text'])) { ?>
						<p class="d9-hub-layers__text"><?php echo esc_html((string) $row['text']); ?></p>
					<?php } ?>
					<?php if ($chips) { ?>
						<ul class="d9-hub-chips">
							<?php foreach ($chips as $chip) { ?>
								<?php $chipUrl = $byName[\strtolower($chip)] ?? ''; ?>
								<li>
									<?php if ($chipUrl) { ?>
										<a class="d9-hub-chip" href="<?php echo esc_url($chipUrl); ?>"><?php echo esc_html($chip); ?></a>
									<?php } else { ?>
										<span class="d9-hub-chip"><?php echo esc_html($chip); ?></span>
									<?php } ?>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
				</div>
			</li>
		<?php } ?>
	</ol>
</div>
