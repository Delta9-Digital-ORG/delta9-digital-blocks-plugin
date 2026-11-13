<?php

/**
 * Template for the Work Showcase Block.
 *
 * Server-renders the tiles as real <img> elements (SEO, alt text, no-JS,
 * reduced-motion, editor preview). Each tile carries per-slot CSS custom
 * properties so the fallback layout is correct before any JS runs; view.js
 * fades the tiles once the WebGL canvas is live. Media IDs resolve here via
 * wp_get_attachment_image_src so URLs never go stale.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;
use Delta9DigitalBlocksPlugin\Blocks\WorkShowcase\Geometry;

require_once __DIR__ . '/work-showcase-geometry.php';

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $manifest['blockJsClass'] ?? '';

$eyebrow = Helpers::checkAttr('workShowcaseEyebrow', $attributes, $manifest);
$heading = Helpers::checkAttr('workShowcaseHeading', $attributes, $manifest);
$lead = Helpers::checkAttr('workShowcaseLead', $attributes, $manifest);
$primaryLabel = Helpers::checkAttr('workShowcasePrimaryCtaLabel', $attributes, $manifest);
$primaryUrl = Helpers::checkAttr('workShowcasePrimaryCtaUrl', $attributes, $manifest);
$secondaryLabel = Helpers::checkAttr('workShowcaseSecondaryCtaLabel', $attributes, $manifest);
$secondaryUrl = Helpers::checkAttr('workShowcaseSecondaryCtaUrl', $attributes, $manifest);
$items = Helpers::checkAttr('workShowcaseItems', $attributes, $manifest);
$background = Helpers::checkAttr('workShowcaseBackground', $attributes, $manifest);
$tileRadius = Helpers::checkAttr('workShowcaseTileRadius', $attributes, $manifest);
$scrollExit = Helpers::checkAttr('workShowcaseScrollExit', $attributes, $manifest);
$autoReplay = Helpers::checkAttr('workShowcaseAutoReplay', $attributes, $manifest);
$minHeight = Helpers::checkAttr('workShowcaseMinHeight', $attributes, $manifest);

$slots = Geometry::slots();
$desktop = $slots['desktop'] ?? [];
$mobile = $slots['mobile'] ?? [];
$mobileCount = \count($mobile);

$sectionClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	'd9-showcase',
]);

$sectionStyle = \sprintf(
	'--d9-bg:%1$s;--d9-min-h:%2$dvh;--d9-radius:%3$s;',
	$background,
	(int) $minHeight,
	(string) $tileRadius
);

?>
<section
	class="<?php echo esc_attr($sectionClass); ?>"
	style="<?php echo esc_attr($sectionStyle); ?>"
	data-scroll-exit="<?php echo esc_attr($scrollExit ? '1' : '0'); ?>"
	data-radius="<?php echo esc_attr((string) $tileRadius); ?>"
>
	<div class="d9-showcase__stage">
		<ul class="d9-showcase__tiles">
			<?php foreach ($items as $i => $item) : ?>
				<?php
				$mediaId = (int) ($item['mediaId'] ?? 0);
				if (!$mediaId) {
					continue;
				}

				$full = wp_get_attachment_image_src($mediaId, 'full');
				$medium = wp_get_attachment_image_src($mediaId, 'medium');
				if (!$full) {
					continue;
				}

				[$fullUrl, $fullW, $fullH] = $full;
				$mediumUrl = $medium[0] ?? $fullUrl;
				$aspect = $fullH ? $fullW / $fullH : 1.0;

				$desktopSlot = $desktop[$i] ?? null;
				if ($desktopSlot === null) {
					continue; // no slot for this index on desktop; skip extras
				}

				$blurred = \array_key_exists('blur', $item)
					? (bool) $item['blur']
					: (bool) ($desktopSlot['blurred'] ?? false);

				$type = $item['type'] ?? ($desktopSlot['type'] ?? 'can');
				$caption = $item['caption'] ?? '';
				$link = $item['link'] ?? '';
				$alt = $item['alt'] ?? (string) get_post_meta($mediaId, '_wp_attachment_image_alt', true);

				$dp = Geometry::projectSlot($desktopSlot, $aspect);

				$hasMobile = $i < $mobileCount;
				$mp = $hasMobile ? Geometry::projectSlot($mobile[$i], $aspect) : null;

				$tileStyle = \sprintf(
					'--dx:%1$s;--dy:%2$s;--dw:%3$s;--dh:%4$s;',
					$dp['x'],
					$dp['y'],
					$dp['w'],
					$dp['h']
				);
				if ($mp !== null) {
					$tileStyle .= \sprintf(
						'--mx:%1$s;--my:%2$s;--mw:%3$s;--mh:%4$s;',
						$mp['x'],
						$mp['y'],
						$mp['w'],
						$mp['h']
					);
				}

				$tileClass = Helpers::classnames([
					'd9-showcase__tile',
					!$hasMobile ? 'd9-showcase__tile--no-mobile' : '',
				]);
				?>
				<li
					class="<?php echo esc_attr($tileClass); ?>"
					style="<?php echo esc_attr($tileStyle); ?>"
					data-full="<?php echo esc_url($fullUrl); ?>"
					data-medium="<?php echo esc_url($mediumUrl); ?>"
					data-aspect="<?php echo esc_attr((string) round($aspect, 4)); ?>"
					data-blurred="<?php echo esc_attr($blurred ? '1' : '0'); ?>"
					data-type="<?php echo esc_attr($type); ?>"
					data-link="<?php echo esc_url($link); ?>"
					data-caption="<?php echo esc_attr($caption); ?>"
				>
					<?php if ($link) : ?>
						<a class="d9-showcase__tile-link" href="<?php echo esc_url($link); ?>">
					<?php endif; ?>
					<?php echo wp_get_attachment_image(
						$mediaId,
						$blurred ? 'medium' : 'large',
						false,
						[
							'class' => 'd9-showcase__img',
							'alt' => $alt,
							'loading' => 'lazy',
							'decoding' => 'async',
						]
					); ?>
					<?php if ($caption) : ?>
						<span class="d9-showcase__tile-caption"><?php echo esc_html($caption); ?></span>
					<?php endif; ?>
					<?php if ($link) : ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ($autoReplay) : ?>
			<button type="button" class="d9-showcase__replay" hidden>
				<?php esc_html_e('Replay animation', 'delta9-digital-blocks-plugin'); ?>
			</button>
		<?php endif; ?>
	</div>

	<div class="d9-showcase__copy">
		<?php if ($eyebrow) : ?>
			<p class="d9-showcase__eyebrow"><?php echo wp_kses_post($eyebrow); ?></p>
		<?php endif; ?>
		<?php if ($heading) : ?>
			<h2 class="d9-showcase__heading"><?php echo wp_kses_post($heading); ?></h2>
		<?php endif; ?>
		<?php if ($lead) : ?>
			<p class="d9-showcase__lead"><?php echo wp_kses_post($lead); ?></p>
		<?php endif; ?>

		<?php if (($primaryLabel && $primaryUrl) || ($secondaryLabel && $secondaryUrl)) : ?>
			<div class="d9-showcase__actions">
				<?php if ($primaryLabel && $primaryUrl) : ?>
					<a class="d9-showcase__cta d9-showcase__cta--primary" href="<?php echo esc_url($primaryUrl); ?>">
						<?php echo esc_html($primaryLabel); ?>
					</a>
				<?php endif; ?>
				<?php if ($secondaryLabel && $secondaryUrl) : ?>
					<a class="d9-showcase__cta d9-showcase__cta--secondary" href="<?php echo esc_url($secondaryUrl); ?>">
						<?php echo esc_html($secondaryLabel); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
