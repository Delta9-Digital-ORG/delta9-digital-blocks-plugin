<?php

/**
 * Template for the Badge Cards Block.
 *
 * Spec: theme claude_design_handoff/design_handoff_wp/blocks/badge-cards.md.
 * Accents rotate by position through CSS (nth-child + --d9-bcards-accent-N); a card's own
 * accent attribute overrides it.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$columns = (int) Helpers::checkAttr('badgeCardsColumns', $attributes, $manifest);
$variant = Helpers::checkAttr('badgeCardsVariant', $attributes, $manifest) === 'steps' ? 'steps' : 'link';
$surface = Helpers::checkAttr('badgeCardsSurface', $attributes, $manifest) === 'base' ? 'base' : 'surface';
$cycle = (array) Helpers::checkAttr('badgeCardsAccentCycle', $attributes, $manifest);

$style = \sprintf('--d9-bcards-cols:%d;', \max(1, \min(4, $columns)));
foreach (\array_slice(\array_values($cycle), 0, 4) as $i => $slug) {
	$style .= \sprintf('--d9-bcards-accent-%d:var(--wp--preset--color--%s);', $i + 1, \sanitize_key((string) $slug));
}

$gridClass = Helpers::classnames([
	$blockClass,
	'd9-bcards',
	"d9-bcards--{$variant}",
	"d9-bcards--on-{$surface}",
]);

?>
<div class="<?php echo esc_attr($gridClass); ?>" style="<?php echo esc_attr($style); ?>">
	<?php
	// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	echo $renderContent;
	?>
</div>
