<?php

/**
 * Template for the Age Verification Block view.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['componentClass'] ?? '';
$blockName = $attributes['blockName'] ?? '';

$ageVerificationUse = Helpers::checkAttr('ageVerificationUse', $attributes, $manifest);

if (!$ageVerificationUse) {
	return;
}

$unique = Helpers::getUnique();
$uniqueAgeVerificationId = Helpers::getUnique();

$ageVerificationContentStartOpen = Helpers::checkAttr('ageVerificationContentStartOpen', $attributes, $manifest);

echo Helpers::render(
	'age-verification',
	Helpers::props('ageVerification', $attributes, [
		'ageVerificationId' => $uniqueAgeVerificationId,
		'ageVerificationContent' => $renderContent,
	])
);
?>
