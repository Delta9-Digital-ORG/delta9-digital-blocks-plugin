<?php

/**
 * Integration section: "In their words" (d9_quote). Hidden until the quote has text.
 *
 * @package Delta9DigitalBlocksPlugin
 */

if (empty($data['quote']['text'])) {
	return;
}
?>
<figure class="d9-int-panel d9-int-panel--notch-left d9-int-quote">
	<?php $notch('left', __('In their words', 'delta9-digital-blocks-plugin')); ?>
	<blockquote class="d9-int-quote__text"><p><?php echo esc_html((string) $data['quote']['text']); ?></p></blockquote>
	<?php if (!empty($data['quote']['name']) || !empty($data['quote']['role'])) { ?>
		<figcaption class="d9-int-quote__who">
			<span class="d9-int-quote__name"><?php echo esc_html((string) ($data['quote']['name'] ?? '')); ?></span>
			<span class="d9-int-quote__role"><?php echo esc_html((string) ($data['quote']['role'] ?? '')); ?></span>
		</figcaption>
	<?php } ?>
</figure>
