<?php

/**
 * Integration section: hero (eyebrow, h1, "Delta9 Digital × name", intro, CTAs) with the
 * Plus Lockup (Delta9 tile + platform tile around a mint "+").
 *
 * @package Delta9DigitalBlocksPlugin
 */

?>
<div class="d9-int-hero">
	<div class="d9-int-hero__copy">
		<?php if ($data['layerLabel']) { ?>
			<span class="d9-int-hero__eyebrow">
				<?php
				/* translators: %s: layer, e.g. "Menu". */
				echo esc_html(\sprintf(__('%s integration', 'delta9-digital-blocks-plugin'), $data['layerLabel']));
				?>
			</span>
		<?php } ?>
		<h1 class="d9-int-hero__title"><?php echo esc_html($data['h1']); ?></h1>
		<span class="d9-int-hero__pair"><?php echo esc_html('Delta9 Digital × ' . $data['name']); ?></span>
		<?php if ($data['intro']) { ?>
			<p class="d9-int-hero__intro"><?php echo esc_html($data['intro']); ?></p>
		<?php } ?>
		<div class="d9-int-hero__actions">
			<a class="d9-int-btn" href="<?php echo esc_url($contactUrl); ?>"><?php esc_html_e('Book a setup call', 'delta9-digital-blocks-plugin'); ?> <span aria-hidden="true">→</span></a>
			<a class="d9-int-btn d9-int-btn--outline" href="<?php echo esc_url($hubUrl); ?>"><?php esc_html_e('All integrations', 'delta9-digital-blocks-plugin'); ?></a>
		</div>
	</div>

	<div class="d9-int-lockup" aria-hidden="true">
		<span class="d9-int-lockup__plus">+</span>
		<span class="d9-int-lockup__tile d9-int-lockup__tile--d9">
			<?php $mark = $brand('d9-mark-mint.svg'); ?>
			<?php if ($mark) { ?>
				<img class="d9-int-lockup__mark" src="<?php echo esc_url($mark); ?>" alt="" />
			<?php } ?>
			<span class="d9-int-lockup__words">
				<?php foreach (['d9-delta9-mint.svg', 'd9-digital-mint.svg'] as $file) { ?>
					<?php $word = $brand($file); ?>
					<?php if ($word) { ?>
						<img class="d9-int-lockup__word" src="<?php echo esc_url($word); ?>" alt="" />
					<?php } ?>
				<?php } ?>
			</span>
		</span>
		<span class="d9-int-lockup__tile d9-int-lockup__tile--partner">
			<?php $logo('d9-int-lockup__logo'); ?>
		</span>
	</div>
</div>
