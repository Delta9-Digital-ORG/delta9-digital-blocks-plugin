<?php

/**
 * Integration section: "How we build it" (d9_features as numbered cards).
 *
 * @package Delta9DigitalBlocksPlugin
 */

if (!$data['features']) {
	return;
}
?>
<div class="d9-int-panel d9-int-panel--notch-left">
	<?php $notch('left', __('How we build it', 'delta9-digital-blocks-plugin')); ?>
	<?php
	$isPair = \count($data['features']) === 2;
	$defaultTitle = $isPair ? __('The two decisions that shape the work', 'delta9-digital-blocks-plugin') : __('The decisions that shape the work', 'delta9-digital-blocks-plugin');
	$defaultLead = $isPair
		? __('Where the menu lives and where the data goes. We make both calls with you before a line of code gets written.', 'delta9-digital-blocks-plugin')
		: __('Where the menu lives and where the data goes. We make these calls with you before a line of code gets written.', 'delta9-digital-blocks-plugin');
	?>
	<h2 class="d9-int-panel__title"><?php echo esc_html($customTitle ?: $defaultTitle); ?></h2>
	<p class="d9-int-panel__lead"><?php echo esc_html($customLead ?: $defaultLead); ?></p>
	<div class="d9-int-features">
		<?php foreach ($data['features'] as $i => $item) { ?>
			<?php $imageId = (int) ($item['image_id'] ?? 0); ?>
			<article class="d9-int-feature<?php echo $imageId ? ' d9-int-feature--media' : ''; ?>" style="<?php echo esc_attr('--d9-int-card-accent:var(--wp--preset--color--' . $accentCycle[$i % \count($accentCycle)] . ');'); ?>">
				<span class="d9-int-badge" aria-hidden="true"><?php echo esc_html(\sprintf('%02d', $i + 1)); ?></span>
				<div class="d9-int-feature__body">
					<span class="d9-int-cardnotch" aria-hidden="true"><span></span><span></span><span></span></span>
					<?php if ($imageId) { ?>
						<span class="d9-int-feature__media"><?php echo wp_get_attachment_image($imageId, 'large', false, ['loading' => 'lazy']); ?></span>
					<?php } ?>
					<div class="d9-int-feature__copy">
						<h3 class="d9-int-card__title"><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
						<p class="d9-int-card__text"><?php echo esc_html((string) ($item['body'] ?? '')); ?></p>
					</div>
				</div>
			</article>
		<?php } ?>
	</div>
</div>
