<?php

/**
 * Template for the Age Verification Block view.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $attributes['blockJsClass'] ?? '';

$ageVerificationCloseAdjacent = Helpers::checkAttr('ageVerificationCloseAdjacent', $attributes, $manifest);

$ageVerificationClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
]);
?>

<div
	class="<?php echo esc_attr($ageVerificationClass); ?>"
	data-close-adjacent="<?php echo esc_attr($ageVerificationCloseAdjacent ? 'true' : 'false'); ?>"
>
		<?php
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		echo $renderContent;
		?>
</div>
