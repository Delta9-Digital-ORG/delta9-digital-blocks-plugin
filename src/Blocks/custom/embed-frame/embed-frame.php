<?php

/**
 * Template for the Embed frame Block.
 *
 * The iframe starts at the minimum height; assets/embed-frame.js resizes it from postMessage
 * ({ type: <resize message>, height: <px> }) sent by the embedded page, accepting messages only
 * from that frame and its origin.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$embedFrameUrl = Helpers::checkAttr('embedFrameUrl', $attributes, $manifest);
$embedFrameTitle = Helpers::checkAttr('embedFrameTitle', $attributes, $manifest);
$embedFrameMinHeight = (int) Helpers::checkAttr('embedFrameMinHeight', $attributes, $manifest);
$embedFrameResizeMessage = Helpers::checkAttr('embedFrameResizeMessage', $attributes, $manifest);
$embedFrameEventMessage = Helpers::checkAttr('embedFrameEventMessage', $attributes, $manifest);
$embedFrameEventPrefix = Helpers::checkAttr('embedFrameEventPrefix', $attributes, $manifest);
$embedFrameEventKey = Helpers::checkAttr('embedFrameEventKey', $attributes, $manifest);
$embedFrameLazy = Helpers::checkAttr('embedFrameLazy', $attributes, $manifest);

// Only http(s) URLs are embedded.
$embedFrameUrl = wp_http_validate_url($embedFrameUrl) ? $embedFrameUrl : '';

if (!$embedFrameUrl) {
	return;
}

$embedFrameClass = Helpers::classnames([
	$blockClass,
]);

$iframeClass = Helpers::classnames([
	Helpers::selector($blockClass, $blockClass, 'iframe'),
	$blockJsClass,
]);
?>

<div class="<?php echo esc_attr($embedFrameClass); ?>">
	<iframe
		class="<?php echo esc_attr($iframeClass); ?>"
		src="<?php echo esc_url($embedFrameUrl); ?>"
		title="<?php echo esc_attr($embedFrameTitle ?: __('Embedded content', 'delta9-digital-blocks-plugin')); ?>"
		style="min-height: <?php echo esc_attr((string) $embedFrameMinHeight); ?>px; height: <?php echo esc_attr((string) $embedFrameMinHeight); ?>px;"
		<?php if ($embedFrameLazy) { ?>
			loading="lazy"
		<?php } ?>
		data-resize-message="<?php echo esc_attr($embedFrameResizeMessage); ?>"
		data-event-message="<?php echo esc_attr($embedFrameEventMessage); ?>"
		data-event-prefix="<?php echo esc_attr($embedFrameEventPrefix); ?>"
		data-event-key="<?php echo esc_attr($embedFrameEventKey); ?>"
	></iframe>
</div>
