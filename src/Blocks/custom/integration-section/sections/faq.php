<?php

/**
 * Integration section: FAQs (d9_faq as open rows).
 *
 * @package Delta9DigitalBlocksPlugin
 */

if (!$data['faq']) {
	return;
}
?>
<div class="d9-int-panel d9-int-panel--notch-right">
	<?php $notch('right', $customTitle ?: __('FAQs', 'delta9-digital-blocks-plugin')); ?>
	<dl class="d9-int-faq">
		<?php foreach ($data['faq'] as $item) { ?>
			<div class="d9-int-faq__row">
				<dt class="d9-int-faq__q"><?php echo esc_html((string) ($item['q'] ?? '')); ?></dt>
				<dd class="d9-int-faq__a"><?php echo esc_html((string) ($item['a'] ?? '')); ?></dd>
			</div>
		<?php } ?>
	</dl>
</div>
