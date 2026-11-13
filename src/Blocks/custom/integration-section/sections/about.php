<?php

/**
 * Integration section: "Who you are working with" (about the platform + about Delta9).
 *
 * @package Delta9DigitalBlocksPlugin
 */

$d9About = $customLead ?: __('We build and maintain cannabis websites in Minnesota and beyond: brand, site, menu, and the wiring between your platforms. Small senior team, no junior queue, and we tell you before you pay us if something will not work.', 'delta9-digital-blocks-plugin');
?>
<div class="d9-int-panel d9-int-panel--notch-left">
	<?php $notch('left', $customTitle ?: __('Who you are working with', 'delta9-digital-blocks-plugin'), 'wide'); ?>
	<div class="d9-int-about">
		<?php if ($data['about']) { ?>
			<div class="d9-int-about__card">
				<?php $notch('left', \sprintf(/* translators: %s: platform name. */ __('About %s', 'delta9-digital-blocks-plugin'), $data['name']), 'card'); ?>
				<span class="d9-int-about__brand"><?php $logo('d9-int-about__logo'); ?></span>
				<p class="d9-int-about__text"><?php echo esc_html($data['about']); ?></p>
			</div>
		<?php } ?>
		<div class="d9-int-about__card">
			<?php $notch('left', __('About Delta9 Digital', 'delta9-digital-blocks-plugin'), 'card-wide'); ?>
			<span class="d9-int-about__brand d9-int-about__brand--d9" aria-hidden="true">
				<?php foreach (['d9-mark-mint.svg', 'd9-delta9-mint.svg', 'd9-digital-mint.svg'] as $file) { ?>
					<?php $src = $brand($file); ?>
					<?php if ($src) { ?>
						<img src="<?php echo esc_url($src); ?>" alt="" />
					<?php } ?>
				<?php } ?>
			</span>
			<p class="d9-int-about__text"><?php echo esc_html($d9About); ?></p>
			<a class="d9-int-link" href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('Who we are', 'delta9-digital-blocks-plugin'); ?> <span aria-hidden="true">→</span></a>
		</div>
	</div>
</div>
