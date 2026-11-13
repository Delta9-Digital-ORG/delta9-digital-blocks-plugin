<?php

/**
 * Integration section: "What you get" (d9_benefits as badge cards).
 *
 * @package Delta9DigitalBlocksPlugin
 */

if (!$data['benefits']) {
	return;
}
?>
<div class="d9-int-head">
	<h2 class="d9-int-head__title"><?php echo esc_html($customTitle ?: __('What you get', 'delta9-digital-blocks-plugin')); ?></h2>
	<p class="d9-int-head__aside">
		<?php
		/* translators: %s: platform name. */
		echo esc_html($customLead ?: \sprintf(__('%s plus a site that uses it properly', 'delta9-digital-blocks-plugin'), $data['name']));
		?>
	</p>
</div>
<div class="d9-int-cards">
	<?php foreach ($data['benefits'] as $i => $item) { ?>
		<article class="d9-int-card" style="<?php echo esc_attr('--d9-int-card-accent:var(--wp--preset--color--' . $accentCycle[$i % \count($accentCycle)] . ');'); ?>">
			<span class="d9-int-badge" aria-hidden="true"><?php echo esc_html(\sprintf('%02d', $i + 1)); ?></span>
			<div class="d9-int-card__body">
				<span class="d9-int-cardnotch" aria-hidden="true"><span></span><span></span><span></span></span>
				<h3 class="d9-int-card__title"><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
				<p class="d9-int-card__text"><?php echo esc_html((string) ($item['body'] ?? '')); ?></p>
			</div>
		</article>
	<?php } ?>
</div>
