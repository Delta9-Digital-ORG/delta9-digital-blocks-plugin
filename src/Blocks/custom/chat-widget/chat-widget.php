<?php

/**
 * Template for the Chat widget Block.
 *
 * Prints a single <script> tag that loads a self-contained chat widget (e.g. a concierge bot).
 * The widget injects its own floating launcher and chat UI, so this block has no visible output
 * in the content flow and can be placed once anywhere on the page (the footer template is ideal).
 *
 * The widget URL is a block attribute — the plugin stays brand-neutral; each site points it at
 * its own widget. Only http(s) URLs are emitted, and the same URL is printed at most once per
 * request even if the block appears more than once.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$chatWidgetScriptUrl = Helpers::checkAttr('chatWidgetScriptUrl', $attributes, $manifest);
$chatWidgetAsync = Helpers::checkAttr('chatWidgetAsync', $attributes, $manifest);

// Only http(s) URLs are loaded.
$chatWidgetScriptUrl = wp_http_validate_url($chatWidgetScriptUrl) ? $chatWidgetScriptUrl : '';

if (!$chatWidgetScriptUrl) {
	return;
}

// Print each unique widget URL only once per request.
if (!isset($GLOBALS['d9_chat_widget_printed']) || !\is_array($GLOBALS['d9_chat_widget_printed'])) {
	$GLOBALS['d9_chat_widget_printed'] = [];
}

if (\in_array($chatWidgetScriptUrl, $GLOBALS['d9_chat_widget_printed'], true)) {
	return;
}

$GLOBALS['d9_chat_widget_printed'][] = $chatWidgetScriptUrl;
?>

<script src="<?php echo esc_url($chatWidgetScriptUrl); ?>"<?php echo $chatWidgetAsync ? ' async' : ''; ?>></script>
