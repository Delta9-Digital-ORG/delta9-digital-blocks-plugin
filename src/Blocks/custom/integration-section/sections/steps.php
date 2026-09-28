<?php

/**
 * Integration section: "How setup works" (Sticky Steps: pinned intro + d9_steps rows).
 *
 * @package Delta9DigitalBlocksPlugin
 */

if (!$data['steps']) {
	return;
}

$count = \count($data['steps']);
?>
<div class="d9-int-steps">
	<div class="d9-int-steps__intro">
		<h2 class="d9-int-steps__title"><?php echo esc_html($customTitle ?: __('How setup works', 'delta9-digital-blocks-plugin')); ?></h2>
		<p class="d9-int-steps__lead">
			<?php
			echo esc_html(
				$customLead ?: \sprintf(
					/* translators: %d: number of steps. */
					_n('%d step, run by us. You approve, we build, your team gets trained at the end.', '%d steps, run by us. You approve, we build, your team gets trained at the end.', $count, 'delta9-digital-blocks-plugin'),
					$count
				)
			);
			?>
		</p>
		<a class="d9-int-btn d9-int-btn--small" href="<?php echo esc_url($contactUrl); ?>"><?php esc_html_e('Get started', 'delta9-digital-blocks-plugin'); ?> <span aria-hidden="true">→</span></a>
	</div>
	<ol class="d9-int-steps__list">
		<?php foreach ($data['steps'] as $i => $item) { ?>
			<li class="d9-int-step">
				<span class="d9-int-step__number" aria-hidden="true"><?php echo esc_html(\sprintf('%02d', $i + 1)); ?></span>
				<div class="d9-int-step__copy">
					<h3 class="d9-int-step__title"><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
					<p class="d9-int-step__text"><?php echo esc_html((string) ($item['body'] ?? '')); ?></p>
				</div>
			</li>
		<?php } ?>
	</ol>
</div>
