<?php

/**
 * Load more button component.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);
$componentName = $attributes['componentName'] ?? $manifest['componentName'];

$modalButtonUse = Helpers::checkAttr('modalButtonUse', $attributes, $manifest, $componentName);
if (!$modalButtonUse) {
	return;
}

$componentClass = $manifest['componentClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$modalButtonId = Helpers::checkAttr('modalButtonId', $attributes, $manifest, $componentName);
$modalButtonType = Helpers::checkAttr('modalButtonType', $attributes, $manifest, $componentName);


$modalButtonClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($blockClass, $blockClass, $selectorClass),
	Helpers::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($modalButtonClass); ?>">
	<?php
	echo Helpers::render('button', Helpers::props('button', $attributes, [
		'blockClass' => $componentClass,
		'additionalClass' => $componentJsClass,
		'buttonAttrs' => [
			'data-modal-button-type' => $modalButtonType,
			'data-modal-button-id' => $modalButtonId,
			'data-js-modal-toggle-open' => $modalButtonId,
			'aria-hidden' => 'false',
		]
	]), '', true);
	?>
</div>
