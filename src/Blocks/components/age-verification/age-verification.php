<?php

/**
 * Template for the Age Verification Component.
 *
 * @package Delta9DigitalBlocksPlugin.
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$ageVerificationUse = Helpers::checkAttr('ageVerificationUse', $attributes, $manifest);

if (!$ageVerificationUse) {
	return;
}

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentJsClass = $manifest['componentJsClass'] ?? '';
$componentJsToggleClass = $manifest['componentJsToggleClass'] ?? '';

$ageVerificationContent = $attributes[Helpers::getAttrKey('ageVerificationContent', $attributes, $manifest)];
$ageVerificationConfirmText = $attributes[Helpers::getAttrKey('ageVerificationConfirmText', $attributes, $manifest)];
$ageVerificationDeclineText = $attributes[Helpers::getAttrKey('ageVerificationDeclineText', $attributes, $manifest)];
$ageVerificationTime = $attributes[Helpers::getAttrKey('ageVerificationTime', $attributes, $manifest)];
$ageVerificationDeclineLink = $attributes[Helpers::getAttrKey('ageVerificationDeclineLink', $attributes, $manifest)];
$ageVerificationId = $attributes[Helpers::getAttrKey('ageVerificationId', $attributes, $manifest)];

$ageVerificationClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($blockClass, $blockClass, $selectorClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($componentJsClass, $componentJsClass),
]);

?>

<div
	id="<?php echo esc_attr($ageVerificationId); ?>"
	class="<?php echo esc_attr($ageVerificationClass); ?>"
	aria-hidden="true"
>
	<div
		<?php echo esc_attr("data-{$componentJsToggleClass}-close"); ?>
		class="<?php echo esc_attr("{$componentClass}__overlay"); ?>"
		tabIndex="-1"
	>
		<div
			class="<?php echo esc_attr("{$componentClass}__dialog"); ?>"
			role="dialog"
			aria-age-verification="true"
		>

			<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
				<?php
					echo $ageVerificationContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
				?>
			</div>
			<div class="<?php echo esc_attr("{$componentClass}__close"); ?>">
				<button
					<?php echo esc_attr("data-{$componentJsToggleClass}-confirm"); ?>
					class="<?php echo esc_attr("{$componentClass}__confirm-button"); ?>"
					aria-label="<?php echo esc_attr__('Age Verification Confirm', 'delta9-digital-blocks-plugin'); ?>"
				>
					<?php
					echo esc_attr__($ageVerificationConfirmText, 'delta9-digital-blocks-plugin');
					?>
				</button>
				<button
					<?php echo esc_attr("data-{$componentJsToggleClass}-decline"); ?>
					<?php echo esc_attr("data-decline-link=" . $ageVerificationDeclineLink); ?>
					<?php echo esc_attr("data-age-verification-time=" . $ageVerificationTime); ?>
					class="<?php echo esc_attr("{$componentClass}__decline-button"); ?>"
					aria-label="<?php echo esc_attr__('Age Verification Decline', 'delta9-digital-blocks-plugin'); ?>"
				>
					<?php
					echo esc_attr__($ageVerificationDeclineText, 'delta9-digital-blocks-plugin');
					?>
				</button>
			</div>
		</div>
	</div>
</div>
