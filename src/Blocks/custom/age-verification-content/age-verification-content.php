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
?>

<div class="<?php echo esc_attr($blockClass); ?>" data-id="<?php echo esc_attr($uniqueAgeVerificationId); ?>">
	<?php
	echo Helpers::outputCssVariables($attributes, $manifest, $unique),
	Helpers::render(
		'age-verification-button',
		Helpers::props('ageVerificationButton', $attributes, [
			'ageVerificationButtonId' => $uniqueAgeVerificationId,
			'ageVerificationButtonType' => $blockName,
		]),
		'',
		true
	);
	?>
</div>

<?php
echo Helpers::render('age-verification', Helpers::props('age-verification', $attributes, [
	'ageVerificationId' => $uniqueAgeVerificationId,
	'ageVerificationContent' => $renderContent,
]));
?>
