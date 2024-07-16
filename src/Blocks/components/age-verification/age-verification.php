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

$ageVerificationExitButton = Helpers::checkAttr('ageVerificationExitButton', $attributes, $manifest);
$ageVerificationContent = Helpers::checkAttr('ageVerificationContent', $attributes, $manifest);
$ageVerificationConfirmText = Helpers::checkAttr('ageVerificationConfirmText', $attributes, $manifest);
$ageVerificationDeclineText = Helpers::checkAttr('ageVerificationDeclineText', $attributes, $manifest);
$ageVerificationTime = Helpers::checkAttr('ageVerificationTime', $attributes, $manifest);
$ageVerificationDeclineLink = Helpers::checkAttr('ageVerificationDeclineLink', $attributes, $manifest);
$ageVerificationId = Helpers::checkAttr('ageVerificationId', $attributes, $manifest);

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
