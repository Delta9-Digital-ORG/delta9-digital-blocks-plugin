<?php

/**
 * Integration section: "Next step" mint call to action.
 *
 * @package Delta9DigitalBlocksPlugin
 */

?>
<div class="d9-int-panel d9-int-panel--notch-left d9-int-panel--mint">
	<?php $notch('left', __('Next step', 'delta9-digital-blocks-plugin')); ?>
	<h2 class="d9-int-panel__title">
		<?php
		/* translators: %s: platform name. */
		echo esc_html($customTitle ?: \sprintf(__('Run %s? Let us look at the wiring.', 'delta9-digital-blocks-plugin'), $data['name']));
		?>
	</h2>
	<p class="d9-int-panel__lead"><?php echo esc_html($customLead ?: __('Thirty minutes, screen shared, no pitch deck. You leave knowing what is connected, what is not, and what it would take.', 'delta9-digital-blocks-plugin')); ?></p>
	<a class="d9-int-btn d9-int-btn--dark" href="<?php echo esc_url($contactUrl); ?>"><?php esc_html_e('Book the call', 'delta9-digital-blocks-plugin'); ?> <span aria-hidden="true">→</span></a>
</div>
