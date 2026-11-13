<?php

/**
 * Template for the Open Row Block (child of Open Rows).
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$question = Helpers::checkAttr('openRowQuestion', $attributes, $manifest);
$answer = Helpers::checkAttr('openRowAnswer', $attributes, $manifest);

if (!$question && !$answer) {
	return;
}

?>
<div class="d9-open-row">
	<dt class="d9-open-row__question"><?php echo wp_kses_post($question); ?></dt>
	<dd class="d9-open-row__answer"><?php echo wp_kses_post($answer); ?></dd>
</div>
