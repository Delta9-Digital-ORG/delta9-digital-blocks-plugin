<?php

/**
 * Template for the Modal Block view.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['componentClass'] ?? '';
$blockName = $attributes['blockName'] ?? '';

$modalUse = Helpers::checkAttr('modalUse', $attributes, $manifest);

if (!$modalUse) {
	return;
}

$unique = Helpers::getUnique();
$modalButtonId = "{$blockName}-{$unique}";
$uniqueModalId = Helpers::getUnique();


$modalContentStartOpen = Helpers::checkAttr('modalContentStartOpen', $attributes, $manifest);
?>

<div class="<?php echo esc_attr($blockClass); ?>" data-id="<?php echo esc_attr($uniqueModalId); ?>">
	<?php
	echo Helpers::outputCssVariables($attributes, $manifest, $unique),
	Helpers::render(
	'modal-button',
	Helpers::props('modalButton', $attributes, [
		'modalButtonId' => $uniqueModalId,
		'modalButtonType' => $blockName,
	]),
	'',
	true);
	?>
</div>

<?php echo Helpers::render('modal', Helpers::props('modal', $attributes, [
	'modalId' => $uniqueModalId,
	'modalContent' => $renderContent,
]
)); ?>
	

