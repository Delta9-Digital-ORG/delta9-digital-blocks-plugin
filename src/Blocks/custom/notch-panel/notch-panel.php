<?php

/**
 * Template for the Notch Panel Block.
 *
 * Spec: theme claude_design_handoff/design_handoff_wp/blocks/notch-panel.md.
 * Renders its own element (no Eightshift wrapper) so the core `align` class lands on the
 * section and the page's constrained layout can size it.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$pillText = Helpers::checkAttr('notchPanelPillText', $attributes, $manifest);
$pillSide = Helpers::checkAttr('notchPanelPillSide', $attributes, $manifest);
$tone = Helpers::checkAttr('notchPanelTone', $attributes, $manifest);
$glow = Helpers::checkAttr('notchPanelGlow', $attributes, $manifest);
$align = $attributes['align'] ?? '';
$anchor = $attributes['anchor'] ?? '';

$pillSide = $pillSide === 'right' ? 'right' : 'left';
$tone = $tone === 'mint' ? 'mint' : 'surface';

$panelClass = Helpers::classnames([
	$blockClass,
	'd9-notch',
	"d9-notch--{$pillSide}",
	"d9-notch--{$tone}",
	$glow ? 'd9-notch--glow' : '',
	$align ? "align{$align}" : '',
]);

?>
<section
	class="<?php echo esc_attr($panelClass); ?>"
	<?php echo $anchor ? 'id="' . esc_attr($anchor) . '"' : ''; ?>
>
	<span class="d9-notch__cut" aria-hidden="true"></span>
	<span class="d9-notch__fillet d9-notch__fillet--x" aria-hidden="true"></span>
	<span class="d9-notch__fillet d9-notch__fillet--y" aria-hidden="true"></span>
	<?php if ($pillText) { ?>
		<span class="d9-notch__pill"><?php echo wp_kses_post($pillText); ?></span>
	<?php } ?>
	<div class="d9-notch__inner">
		<?php
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		echo $renderContent;
		?>
	</div>
</section>
