<?php

/**
 * Load more button component.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);
$componentName = $attributes['componentName'] ?? $manifest['componentName'];

$ageVerificationButtonUse = Helpers::checkAttr('ageVerificationButtonUse', $attributes, $manifest, $componentName);
if (!$ageVerificationButtonUse) {
	return;
}

$componentClass = $manifest['componentClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$ageVerificationButtonId = Helpers::checkAttr('ageVerificationButtonId', $attributes, $manifest, $componentName);
$ageVerificationButtonType = Helpers::checkAttr('ageVerificationButtonType', $attributes, $manifest, $componentName);


$ageVerificationButtonClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($blockClass, $blockClass, $selectorClass),
	Helpers::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($ageVerificationButtonClass); ?>">
	<?php
	echo Helpers::render('button', Helpers::props('button', $attributes, [
		'blockClass' => $componentClass,
		'additionalClass' => $componentJsClass,
		'buttonAttrs' => [
			'data-age-verification-button-type' => $ageVerificationButtonType,
			'data-age-verification-button-id' => $ageVerificationButtonId,
			'data-js-age-verification-toggle-open' => $ageVerificationButtonId,
			'aria-hidden' => 'false',
		]
	]), '', true);
	?>
</div>
